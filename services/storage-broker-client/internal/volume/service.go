// Package volume is the storage application service: it turns a purchase into a
// managed per-tenant disk by reconciling desired spec (from the tier catalog)
// against the Driver (the Rust disk), and records control-plane truth in the
// Store. Deterministic; AI never participates here.
package volume

import (
	"context"
	"fmt"
	"time"

	"github.com/shippin/storage-broker-client/internal/contract"
)

type Service struct {
	driver contract.Driver
	store  Store
	now    func() time.Time
}

func NewService(d contract.Driver, s Store) *Service {
	return &Service{driver: d, store: s, now: time.Now}
}

// PurchaseInput is what the control plane (shippin-api/clyffe-api) sends when a
// customer buys storage.
type PurchaseInput struct {
	TenantID string        `json:"tenant_id"`
	Tier     contract.Tier `json:"tier"`
	Region   string        `json:"region,omitempty"`
}

// Provision reconciles a purchase into an active managed volume.
func (s *Service) Provision(ctx context.Context, in PurchaseInput) (*contract.Volume, error) {
	if in.TenantID == "" {
		return nil, fmt.Errorf("tenant_id required")
	}
	spec, ok := contract.SpecForTier(in.TenantID, in.Tier, in.Region)
	if !ok {
		return nil, fmt.Errorf("unknown tier %q", in.Tier)
	}
	v, err := s.driver.Provision(ctx, spec)
	if err != nil {
		return nil, fmt.Errorf("driver provision: %w", err)
	}
	now := s.now().UTC()
	if v.CreatedAt.IsZero() {
		v.CreatedAt = now
	}
	v.UpdatedAt = now
	if v.TenantID == "" {
		v.TenantID = in.TenantID
	}
	if v.Spec.Tier == "" {
		v.Spec = spec
	}
	if err := s.store.Put(ctx, v); err != nil {
		return nil, fmt.Errorf("store: %w", err)
	}
	return v, nil
}

// Get returns the stored volume, refreshed from the driver when active.
func (s *Service) Get(ctx context.Context, id string) (*contract.Volume, error) {
	v, err := s.store.Get(ctx, id)
	if err != nil {
		return nil, err
	}
	if live, err := s.driver.Status(ctx, id); err == nil {
		live.CreatedAt = v.CreatedAt
		live.UpdatedAt = s.now().UTC()
		_ = s.store.Put(ctx, live)
		return live, nil
	}
	return v, nil
}

func (s *Service) List(ctx context.Context, tenantID string) ([]*contract.Volume, error) {
	return s.store.List(ctx, tenantID)
}

// Deprovision tears down the managed volume and removes control-plane truth.
func (s *Service) Deprovision(ctx context.Context, id string) error {
	if _, err := s.store.Get(ctx, id); err != nil {
		return err
	}
	if err := s.driver.Deprovision(ctx, id); err != nil {
		return fmt.Errorf("driver deprovision: %w", err)
	}
	return s.store.Delete(ctx, id)
}

// GrantMount issues short-lived credentials for the customer to map the volume.
func (s *Service) GrantMount(ctx context.Context, id, protocol string) (*contract.MountGrant, error) {
	if protocol == "" {
		protocol = "s3"
	}
	if _, err := s.store.Get(ctx, id); err != nil {
		return nil, err
	}
	return s.driver.GrantMount(ctx, id, protocol)
}
