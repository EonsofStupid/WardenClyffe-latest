package identity

import (
	"context"

	"github.com/jackc/pgx/v5/pgxpool"
)

// Accounts is the Postgres-backed identity surface (migration 0006). It calls
// the SQL functions — the database owns the identity logic; Go is transport.
type Accounts struct{ db *pgxpool.Pool }

// NewAccounts returns the accounts store.
func NewAccounts(db *pgxpool.Pool) *Accounts { return &Accounts{db: db} }

// CreateCustomerResult mirrors identity.create_customer. The verification
// token is returned ONCE (emailed in production) and never stored raw.
type CreateCustomerResult struct {
	SubjectID         string `json:"subject_id"`
	TenantID          string `json:"tenant_id"`
	VerificationToken string `json:"verification_token"`
}

// CreateCustomer provisions subject + tenant + role + verification token.
func (a *Accounts) CreateCustomer(ctx context.Context, email, displayName string) (*CreateCustomerResult, error) {
	var r CreateCustomerResult
	err := a.db.QueryRow(ctx,
		`SELECT subject_id::text, tenant_id::text, verification_token
		 FROM identity.create_customer($1, $2)`, email, displayName).
		Scan(&r.SubjectID, &r.TenantID, &r.VerificationToken)
	if err != nil {
		return nil, err
	}
	return &r, nil
}

// VerifyEmail redeems a single-use token; true activates the subject.
func (a *Accounts) VerifyEmail(ctx context.Context, token string) (bool, error) {
	var ok bool
	err := a.db.QueryRow(ctx, `SELECT identity.verify_email($1)`, token).Scan(&ok)
	return ok, err
}
