// Package account is the customer-facing Clyffe surface for WardenClyffe.
//
// Boundary: Clyffe is the customer plane. This store reads ONLY customer-safe
// data — account identity (warden_core.tenants) and the customer's own orders
// (clyffe_core.orders). It must never read operator-only schemas
// (warden_infra, warden_audit) or surface secrets; per the Clyffe boundary
// rule, operator data is reachable only through sanitized Warden endpoints.
package account

import (
	"context"
	"time"

	"github.com/jackc/pgx/v5"
	"github.com/jackc/pgx/v5/pgxpool"
)

type Store struct{ db *pgxpool.Pool }

func NewStore(db *pgxpool.Pool) *Store { return &Store{db: db} }

// Account is the customer-safe projection of a tenant.
type Account struct {
	ID        string    `json:"id"`
	Slug      string    `json:"slug"`
	Name      string    `json:"name"`
	CreatedAt time.Time `json:"created_at"`
}

// Order is a customer-safe projection of a placed order.
type Order struct {
	ID        string    `json:"id"`
	TenantID  string    `json:"tenant_id"`
	Product   string    `json:"product"`
	Tier      string    `json:"tier"`
	Repo      *string   `json:"repo"`
	Region    *string   `json:"region"`
	Status    string    `json:"status"`
	CreatedAt time.Time `json:"created_at"`
}

// ListAccounts returns customer accounts (tenants). In production this is
// scoped to the authenticated customer's tenant; the skeleton lists all so the
// portal has something to render off the devstation.
func (s *Store) ListAccounts(ctx context.Context) ([]Account, error) {
	rows, err := s.db.Query(ctx,
		`SELECT id, slug, name, created_at FROM warden_core.tenants ORDER BY created_at`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []Account{}
	for rows.Next() {
		var a Account
		if err := rows.Scan(&a.ID, &a.Slug, &a.Name, &a.CreatedAt); err != nil {
			return nil, err
		}
		out = append(out, a)
	}
	return out, rows.Err()
}

// ListOrders returns the customer's orders. tenantID == "" lists all (skeleton
// convenience); a non-empty value scopes to one tenant, as production will.
func (s *Store) ListOrders(ctx context.Context, tenantID string) ([]Order, error) {
	const cols = `id, tenant_id, product, tier, repo, region, status::text, created_at`
	var rows pgx.Rows
	var err error
	if tenantID == "" {
		rows, err = s.db.Query(ctx,
			`SELECT `+cols+` FROM clyffe_core.orders ORDER BY created_at DESC`)
	} else {
		rows, err = s.db.Query(ctx,
			`SELECT `+cols+` FROM clyffe_core.orders WHERE tenant_id = $1 ORDER BY created_at DESC`, tenantID)
	}
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []Order{}
	for rows.Next() {
		var o Order
		if err := rows.Scan(&o.ID, &o.TenantID, &o.Product, &o.Tier, &o.Repo, &o.Region, &o.Status, &o.CreatedAt); err != nil {
			return nil, err
		}
		out = append(out, o)
	}
	return out, rows.Err()
}
