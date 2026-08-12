// Package fleet owns infrastructure resources (workspaces / VMs / LXCs) in
// shippin_infra. It is read-authority for the console's workspace views.
package fleet

import (
	"context"
	"time"

	"github.com/jackc/pgx/v5"
	"github.com/jackc/pgx/v5/pgxpool"
)

// Resource is a provisioned guest / workspace.
type Resource struct {
	ID        string         `json:"id"`
	StableID  string         `json:"stable_id"`
	TenantID  *string        `json:"tenant_id"`
	Kind      string         `json:"kind"`
	State     string         `json:"state"`
	VMID      *int           `json:"vmid"`
	Hostname  *string        `json:"hostname"`
	Tier      *string        `json:"tier"`
	Cores     *int           `json:"cores"`
	MemoryMB  *int           `json:"memory_mb"`
	DiskGB    *int           `json:"disk_gb"`
	IPCIDR    *string        `json:"ip_cidr"`
	SSHAlias  *string        `json:"ssh_alias"`
	Spec      map[string]any `json:"spec"`
	CreatedAt time.Time      `json:"created_at"`
	UpdatedAt time.Time      `json:"updated_at"`
}

type Store struct{ db *pgxpool.Pool }

func NewStore(db *pgxpool.Pool) *Store { return &Store{db: db} }

const selectCols = `
	id::text, stable_id, tenant_id::text, kind::text, state::text,
	vmid, hostname, tier, cores, memory_mb, disk_gb, ip_cidr, ssh_alias,
	spec, created_at, updated_at`

func scan(rows pgx.Rows) (Resource, error) {
	var r Resource
	err := rows.Scan(&r.ID, &r.StableID, &r.TenantID, &r.Kind, &r.State,
		&r.VMID, &r.Hostname, &r.Tier, &r.Cores, &r.MemoryMB, &r.DiskGB,
		&r.IPCIDR, &r.SSHAlias, &r.Spec, &r.CreatedAt, &r.UpdatedAt)
	return r, err
}

// List returns all fleet resources, newest first.
func (s *Store) List(ctx context.Context) ([]Resource, error) {
	rows, err := s.db.Query(ctx,
		`SELECT `+selectCols+` FROM shippin_infra.resources ORDER BY created_at DESC`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []Resource{}
	for rows.Next() {
		r, err := scan(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, r)
	}
	return out, rows.Err()
}

// Get returns one resource by id.
func (s *Store) Get(ctx context.Context, id string) (*Resource, error) {
	rows, err := s.db.Query(ctx,
		`SELECT `+selectCols+` FROM shippin_infra.resources WHERE id=$1`, id)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	if !rows.Next() {
		return nil, pgx.ErrNoRows
	}
	r, err := scan(rows)
	if err != nil {
		return nil, err
	}
	return &r, nil
}

// CreateRequested inserts a new resource in the 'requested' state and returns it.
func (s *Store) CreateRequested(ctx context.Context, stableID, tier string, tenantID *string, spec map[string]any) (*Resource, error) {
	if spec == nil {
		spec = map[string]any{}
	}
	rows, err := s.db.Query(ctx,
		`INSERT INTO shippin_infra.resources (stable_id, tenant_id, kind, state, tier, spec)
		 VALUES ($1, $2, 'devstation', 'requested', $3, $4)
		 RETURNING `+selectCols, stableID, tenantID, tier, spec)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	if !rows.Next() {
		return nil, pgx.ErrNoRows
	}
	r, err := scan(rows)
	if err != nil {
		return nil, err
	}
	return &r, nil
}
