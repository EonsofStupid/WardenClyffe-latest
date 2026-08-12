// Package clyffy is the Warden Clyffy orchestrator for WardenClyffe.
//
// Clyffy is the assistant/orchestration plane. It runs ON the Warden server and
// reads Warden's CAPTURED model of the foundation (shippin_infra.platforms /
// services) — it never pokes raw service IPs ad hoc. Live reachability is a
// probe of endpoints Warden already captured; credentials stay in Infisical and
// are surfaced only as references.
package clyffy

import (
	"context"
	"net/http"
	"time"

	"github.com/jackc/pgx/v5"
	"github.com/jackc/pgx/v5/pgxpool"
)

type Store struct {
	db   *pgxpool.Pool
	http *http.Client
}

func NewStore(db *pgxpool.Pool) *Store {
	return &Store{db: db, http: &http.Client{Timeout: 2500 * time.Millisecond}}
}

// Service is the customer/operator-safe projection of a captured service.
type Service struct {
	StableID      string   `json:"stable_id"`
	Name          string   `json:"name"`
	Role          string   `json:"role"`
	Designation   string   `json:"designation"` // connector | plugin | platform | core
	Audiences     []string `json:"audiences"`   // subset of operator/ai/customer
	Platform      *string  `json:"platform"`
	VMID          *int     `json:"vmid"`
	Endpoint      *string  `json:"endpoint"`
	AuthRequired  bool     `json:"auth_required"`
	CredentialRef *string  `json:"credential_ref"` // Infisical PATH, never a value
	KeepDecision  *string  `json:"keep_decision"`
	CustomerReady bool     `json:"customer_ready"`
	Owed          []string `json:"owed"`
	State         string   `json:"state"`
}

const svcCols = `
	s.stable_id, s.name, s.role::text, s.designation::text, s.audiences, p.stable_id, s.vmid, s.endpoint,
	s.auth_required, s.credential_ref, s.keep_decision, s.customer_ready,
	s.owed, s.state`

func scanService(rows pgx.Rows) (Service, error) {
	var s Service
	err := rows.Scan(&s.StableID, &s.Name, &s.Role, &s.Designation, &s.Audiences, &s.Platform, &s.VMID, &s.Endpoint,
		&s.AuthRequired, &s.CredentialRef, &s.KeepDecision, &s.CustomerReady, &s.Owed, &s.State)
	return s, err
}

func (st *Store) ListServices(ctx context.Context) ([]Service, error) {
	return st.queryServices(ctx, "", "")
}

// ListByDesignation returns services of a given designation (connector|plugin|...).
func (st *Store) ListByDesignation(ctx context.Context, designation string) ([]Service, error) {
	return st.queryServices(ctx, "WHERE s.designation = $1", designation)
}

func (st *Store) queryServices(ctx context.Context, where, arg string) ([]Service, error) {
	sql := `SELECT ` + svcCols + ` FROM shippin_infra.services s
	        LEFT JOIN shippin_infra.platforms p ON p.id = s.platform_id ` + where +
		` ORDER BY s.vmid NULLS LAST`
	var rows pgx.Rows
	var err error
	if where == "" {
		rows, err = st.db.Query(ctx, sql)
	} else {
		rows, err = st.db.Query(ctx, sql, arg)
	}
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []Service{}
	for rows.Next() {
		s, err := scanService(rows)
		if err != nil {
			return nil, err
		}
		out = append(out, s)
	}
	return out, rows.Err()
}

// Platform is a captured compute platform (proxmox / coolify docker).
type Platform struct {
	StableID      string  `json:"stable_id"`
	Kind          string  `json:"kind"`
	APIEndpoint   *string `json:"api_endpoint"`
	NetworkName   *string `json:"network_name"`
	Managed       bool    `json:"managed"`
	CredentialRef *string `json:"credential_ref"`
	State         string  `json:"state"`
	Note          *string `json:"note"`
}

func (st *Store) ListPlatforms(ctx context.Context) ([]Platform, error) {
	rows, err := st.db.Query(ctx,
		`SELECT stable_id, kind::text, api_endpoint, network_name, managed, credential_ref, state, note
		 FROM shippin_infra.platforms ORDER BY kind`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []Platform{}
	for rows.Next() {
		var p Platform
		if err := rows.Scan(&p.StableID, &p.Kind, &p.APIEndpoint, &p.NetworkName, &p.Managed, &p.CredentialRef, &p.State, &p.Note); err != nil {
			return nil, err
		}
		out = append(out, p)
	}
	return out, rows.Err()
}

// IntelProbe is a live reachability result for an intelligence-plane service.
type IntelProbe struct {
	StableID        string  `json:"stable_id"`
	Role            string  `json:"role"`
	Endpoint        *string `json:"endpoint"`
	Reachable       bool    `json:"reachable"`
	HTTPStatus      int     `json:"http_status,omitempty"`
	CredentialGated bool    `json:"credential_gated"`
	CredentialRef   *string `json:"credential_ref"`
	Detail          string  `json:"detail"`
}

// IntelligenceStatus probes the intelligence-plane services (vector/graph/
// embedder) that Warden has captured, and reports reachability + whether they
// are credential-gated (so we know exactly what Infisical access is owed).
func (st *Store) IntelligenceStatus(ctx context.Context) ([]IntelProbe, error) {
	rows, err := st.db.Query(ctx,
		`SELECT stable_id, role::text, endpoint, auth_required, credential_ref
		 FROM shippin_infra.services
		 WHERE role IN ('vector','graph','embedder') ORDER BY role`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	type row struct {
		id, role       string
		endpoint, cred *string
		auth           bool
	}
	var list []row
	for rows.Next() {
		var r row
		if err := rows.Scan(&r.id, &r.role, &r.endpoint, &r.auth, &r.cred); err != nil {
			return nil, err
		}
		list = append(list, r)
	}
	if err := rows.Err(); err != nil {
		return nil, err
	}

	out := []IntelProbe{}
	for _, r := range list {
		p := IntelProbe{StableID: r.id, Role: r.role, Endpoint: r.endpoint, CredentialRef: r.cred}
		if r.endpoint == nil {
			p.Detail = "no endpoint captured"
			out = append(out, p)
			continue
		}
		// health path per role
		path := "/healthz"
		if r.role == "graph" {
			path = "/health"
		} else if r.role == "embedder" {
			path = "/health"
		}
		code, err := st.probe(ctx, *r.endpoint+path)
		switch {
		case err != nil:
			p.Reachable = false
			p.Detail = "unreachable: " + err.Error()
		case code == 401 || code == 403:
			p.Reachable = true
			p.HTTPStatus = code
			p.CredentialGated = true
			p.Detail = "reachable; credential-gated (auth via " + refOr(r.cred) + ")"
		default:
			p.Reachable = true
			p.HTTPStatus = code
			p.Detail = "reachable"
		}
		out = append(out, p)
	}
	return out, nil
}

func (st *Store) probe(ctx context.Context, url string) (int, error) {
	req, err := http.NewRequestWithContext(ctx, http.MethodGet, url, nil)
	if err != nil {
		return 0, err
	}
	resp, err := st.http.Do(req)
	if err != nil {
		return 0, err
	}
	defer resp.Body.Close()
	return resp.StatusCode, nil
}

func refOr(s *string) string {
	if s == nil {
		return "infisical"
	}
	return *s
}
