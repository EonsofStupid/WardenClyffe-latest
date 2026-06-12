// Package automation owns the plan -> approve -> execute control plane
// (warden_core.action_requests) and the provision workflow entrypoint.
package automation

import (
	"context"
	"encoding/json"
	"fmt"

	"github.com/jackc/pgx/v5/pgxpool"

	"github.com/wardenclyffe/warden-api/internal/audit"
	"github.com/wardenclyffe/warden-api/internal/fleet"
)

type Service struct {
	db    *pgxpool.Pool
	fleet *fleet.Store
	audit *audit.Sink
}

func NewService(db *pgxpool.Pool, fl *fleet.Store, au *audit.Sink) *Service {
	return &Service{db: db, fleet: fl, audit: au}
}

// ProvisionInput is the request to provision a workspace.
type ProvisionInput struct {
	Tier     string `json:"tier"`
	Repo     string `json:"repo,omitempty"`
	Region   string `json:"region,omitempty"`
	TenantID string `json:"tenant_id,omitempty"`
}

// ProvisionResult is returned to the caller (and to clyffe-api).
type ProvisionResult struct {
	ActionRequestID string          `json:"action_request_id"`
	Resource        *fleet.Resource `json:"resource"`
}

// Provision creates the action request and a requested resource, then records
// an audit event. The actual Proxmox clone is delegated to the provisioner
// service (phase 2); here we establish durable control-plane truth.
func (s *Service) Provision(ctx context.Context, in ProvisionInput) (*ProvisionResult, error) {
	if in.Tier == "" {
		in.Tier = "builder"
	}

	payload, _ := json.Marshal(in)

	var tenantID *string
	if in.TenantID != "" {
		tenantID = &in.TenantID
	}

	var actionID string
	err := s.db.QueryRow(ctx,
		`INSERT INTO warden_core.action_requests (tenant_id, kind, status, payload)
		 VALUES ($1, 'provision_workspace', 'planned', $2)
		 RETURNING id::text`, tenantID, payload).Scan(&actionID)
	if err != nil {
		return nil, fmt.Errorf("create action request: %w", err)
	}

	// Deterministic-ish stable id for the requested workspace.
	stableID := "resource.pending." + actionID[:8]
	spec := map[string]any{"repo": in.Repo, "region": in.Region, "requested_via": "warden-api"}

	res, err := s.fleet.CreateRequested(ctx, stableID, in.Tier, tenantID, spec)
	if err != nil {
		return nil, fmt.Errorf("create requested resource: %w", err)
	}

	// link resource onto the action request result
	_, _ = s.db.Exec(ctx,
		`UPDATE warden_core.action_requests SET result = jsonb_build_object('resource_id',$2::text) WHERE id=$1`,
		actionID, res.ID)

	_ = s.audit.Append(ctx, audit.Event{
		Action:     "resource.provision_requested",
		TargetKind: "resource",
		TargetID:   res.ID,
		Data:       map[string]any{"action_request_id": actionID, "tier": in.Tier},
	})

	return &ProvisionResult{ActionRequestID: actionID, Resource: res}, nil
}
