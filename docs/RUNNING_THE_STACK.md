# Running the WardenClyffe stack (devstation)

This is the live foundation: **Warden server (Go) + Clyffe portal API (Go) +
self-hosted Postgres + React console** with a Supabase-inspired, Go-backed data
management layer. Warden is the operator plane; Clyffe is the customer plane.

## Components built

```
data/schema/sql/0001_init.sql      canonical Postgres schema (8 schemas)
services/warden-api/               Go server (chi + pgx), port :8081 — OPERATOR plane
  internal/platform                config, pgx pool, http helpers, CORS
  internal/fleet                   warden_infra.resources (workspaces)
  internal/automation              plan -> action_request -> provision
  internal/audit                   append-only warden_audit.events
  internal/dbadmin                 Supabase-style data API (our own, in Go)
  internal/clyffy                  Clyffy orchestrator (reads captured foundation)
services/clyffe-api/               Go server (chi + pgx), port :8082 — CUSTOMER plane
  internal/platform                config, pgx pool, http helpers, CORS
  internal/portal                  customer-safe reads: accounts + orders
apps/console/                      React + Vite + React-Aria-Components, port :5173
  src/styles/tokens.css            OKLCH color · rem space · clamp() type
  src/ds                           design-system primitives over RAC
  src/views                        Workspaces · DataBrowser · OrderDevstation
```

Boundary: `clyffe-api` holds no infrastructure authority and reads only
customer-safe data (`warden_core.tenants`, `clyffe_*`); it never touches
`warden_infra` / `warden_audit`.

## Prerequisites (already provisioned on warden-devstation-01)

- PostgreSQL 18 local, role `warden`, db `wardenclyffe`.
- Schema applied: `psql -h 127.0.0.1 -U warden -d wardenclyffe -f data/schema/sql/0001_init.sql`
- Go 1.26, Node 24.

## Run

```bash
bash scripts/dev/run-stack.sh
```

Starts warden-api on `:8081`, clyffe-api on `:8082`, and the console on `:5173`
(the console proxies `/api` to warden-api). Override ports/DB with
`WARDEN_API_ADDR`, `CLYFFE_API_ADDR`, `WARDEN_DB_URL`, `CLYFFE_DB_URL`.

Clyffe-api surface (customer plane):

| Method | Path | Purpose |
|---|---|---|
| GET | `/healthz` | liveness + db ping |
| GET | `/api/clyffe/home` | portal overview: accounts + order roll-up |
| GET | `/api/clyffe/accounts` | customer accounts (tenants) |
| GET | `/api/clyffe/orders?tenant_id=` | customer orders (all, or one tenant) |

## View it from your machine

The devstation has no public route. SSH local-forward the console port:

```bash
ssh -L 5173:127.0.0.1:5173 warden-devstation
# then open http://127.0.0.1:5173
```

## API surface (warden-api)

| Method | Path | Purpose |
|---|---|---|
| GET  | `/healthz` | liveness + db ping |
| GET  | `/api/warden/workspaces` | fleet resources |
| GET  | `/api/warden/workspaces/{id}` | one resource |
| POST | `/api/warden/provision` | order/provision a workspace (action_request + resource + audit) |
| GET  | `/api/warden/data/schemas` | managed schemas + table counts |
| GET  | `/api/warden/data/schemas/{schema}/tables` | tables + columns/rows/size |
| GET  | `/api/warden/data/tables/{schema}/{table}` | columns (type, nullable, pk) |
| GET  | `/api/warden/data/tables/{schema}/{table}/rows?limit=&offset=` | paginated rows |
| POST | `/api/warden/data/query` | **read-only** SQL (read-only tx + statement timeout) |

Safety: the data layer only exposes the 8 WardenClyffe schemas; mutating SQL
fails in the read-only transaction; non-managed schemas return 403.
