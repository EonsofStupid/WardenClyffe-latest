// Package edge owns the public-IP inventory in shippin_infra: the addresses
// WardenClyffe owns/routes, assignable to hosts, with role + lifecycle status.
// Read-authority for the console's edge views; the operator adds/updates here.
// The transition log (shippin_infra.ip_migrations) references these addresses.
package edge

import (
	"context"
	"time"

	"github.com/jackc/pgx/v5"
	"github.com/jackc/pgx/v5/pgxpool"
)

// PublicIP is one inventory row.
type PublicIP struct {
	ID        string    `json:"id"`
	Address   string    `json:"address"`
	Provider  *string   `json:"provider"`
	HostID    *string   `json:"host_id"`
	Role      string    `json:"role"`   // ingress | egress | exit | reserved
	Status    string    `json:"status"` // active | reserved | released
	Label     *string   `json:"label"`
	Note      *string   `json:"note"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

type Store struct{ db *pgxpool.Pool }

func NewStore(db *pgxpool.Pool) *Store { return &Store{db: db} }

const cols = `
	id::text, address::text, provider, host_id::text, role::text, status::text,
	label, note, created_at, updated_at`

func scan(rows pgx.Rows) (PublicIP, error) {
	var p PublicIP
	err := rows.Scan(&p.ID, &p.Address, &p.Provider, &p.HostID, &p.Role,
		&p.Status, &p.Label, &p.Note, &p.CreatedAt, &p.UpdatedAt)
	return p, err
}

// ListIPs returns the inventory, newest first.
func (s *Store) ListIPs(ctx context.Context) ([]PublicIP, error) {
	rows, err := s.db.Query(ctx,
		`SELECT `+cols+` FROM shippin_infra.public_ips ORDER BY created_at DESC`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []PublicIP{}
	for rows.Next() {
		p, err := scan(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, p)
	}
	return out, rows.Err()
}

// CreateIPInput is the add payload. Address + role are required; the rest
// default at the column level.
type CreateIPInput struct {
	Address  string  `json:"address"`
	Role     string  `json:"role"`
	Status   *string `json:"status"`
	Provider *string `json:"provider"`
	Label    *string `json:"label"`
	Note     *string `json:"note"`
}

// CreateIP inserts a public IP. Conflicting addresses surface as an error.
func (s *Store) CreateIP(ctx context.Context, in CreateIPInput) (*PublicIP, error) {
	status := "reserved"
	if in.Status != nil && *in.Status != "" {
		status = *in.Status
	}
	rows, err := s.db.Query(ctx,
		`INSERT INTO shippin_infra.public_ips (address, role, status, provider, label, note)
		 VALUES ($1::inet, $2::shippin_infra.public_ip_role, $3::shippin_infra.public_ip_status, $4, $5, $6)
		 RETURNING `+cols,
		in.Address, in.Role, status, in.Provider, in.Label, in.Note)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	if !rows.Next() {
		return nil, rows.Err()
	}
	p, err := scan(rows)
	return &p, err
}

// UpdateIPInput patches mutable fields. Nil fields are left unchanged.
type UpdateIPInput struct {
	Role     *string `json:"role"`
	Status   *string `json:"status"`
	Provider *string `json:"provider"`
	HostID   *string `json:"host_id"`
	Label    *string `json:"label"`
	Note     *string `json:"note"`
}

// UpdateIP applies a partial update by id. COALESCE keeps unspecified fields.
func (s *Store) UpdateIP(ctx context.Context, id string, in UpdateIPInput) (*PublicIP, error) {
	rows, err := s.db.Query(ctx,
		`UPDATE shippin_infra.public_ips SET
		   role     = COALESCE($2::shippin_infra.public_ip_role, role),
		   status   = COALESCE($3::shippin_infra.public_ip_status, status),
		   provider = COALESCE($4, provider),
		   host_id  = COALESCE($5::uuid, host_id),
		   label    = COALESCE($6, label),
		   note     = COALESCE($7, note)
		 WHERE id = $1::uuid
		 RETURNING `+cols,
		id, in.Role, in.Status, in.Provider, in.HostID, in.Label, in.Note)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	if !rows.Next() {
		return nil, pgx.ErrNoRows
	}
	p, err := scan(rows)
	return &p, err
}
