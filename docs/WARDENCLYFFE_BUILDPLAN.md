---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: wardenclyffe-buildplan
  persona: clyffy-operator
  kind: buildplan
  owner: docs/WARDENCLYFFE_BUILDPLAN.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDEN_DEVSTATION_CONSOLIDATION_POAM.md
    - docs/specs/CORTEX_CONTROL_LAYER_SPEC.md
    - docs/specs/IDENTITY_TENANCY_SPEC.md
    - docs/WARDENCLYFFE_NAMING_CONVENTIONS.md
    - docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md
  sync:
    qdrant: true
    surreal: true
---

# WardenClyffe Buildplan

The execution record (locked 2026-06-12). The consolidation POA&M is the audit;
this is what gets built, in order, each item with a verify step. Style:
**interleaved** — every phase ships one visible slice alongside foundations.
Golden patterns = the naming conventions + structure standard; every item lands
in their shapes. Principle: configure first what is 10x costlier to retrofit.

## Process rules (locked 2026-06-12 — see parking-lot + boundary-guard skills)

1. **Second pass allowed and preferred** when it yields better-quality or
   better-structured code; capture what it changed. (This plan's own second
   pass added this section + the boundary citations below.)
2. **Scaffold-first** when it helps: folders per the structure standard before
   implementation code.
3. **Every item cites its boundary**; items without one are not plannable —
   boundary-guard `--check` runs on all filetree deltas during planning to
   remove duplicates.
4. The foundation scripts ship in the **workspace template** (auto-copied to
   every user devstation/service; `.pulse`-updated) so the same filetree
   control travels with the product.

## Verified baseline (2026-06-12)

Green: 4 Go services build+vet; frontend builds (TanStack Start SSR, domains
landing/warden/clyffy/admin); plugins live on W (`cortex-control`,
`cortex-intelligence`); intelligence endpoints answering (creds-gated);
naming/structure law conflict-free; 101 touchpoints validating.
Gaps: G1 zero tests + no CI gate · G2 no migrations runner · G3 identity
unbuilt (bootstrap only) · G4 Warden-VM PG absent (102:5432 closed) ·
G5 Infisical agent down (secrets tmpfs empty) · G6 sync worker absent (41
sync-enabled touchpoints unprojected) · G7 branch 23 ahead of main ·
G8 E→W clones pending (clyffy-go, devpulse, makers) · G9 55 v1 touchpoints +
monolithic api.ts · G10 no backups/restore drill.

## P0 — Enforcement & foundations (visible slice: /admin panels)

| # | Item | Names / boundary | Verify |
|---|---|---|---|
| P0-1 | CI gate | `.github/workflows/ci.yml`: per-module `go build/vet/test`, `npm run build`, `validate-touchpoints.py --strict` | red PR fails, green merges |
| P0-2 | Migrations runner (own, tiny). **2nd-pass find:** `warden_core.schema_migrations` ledger already exists in 0001 and every migration self-registers — runner = read dir, diff vs ledger, apply pending in tx | `services/warden-api/cmd/warden-migrate` (operator-approved 2026-06-12) | fresh DB → `warden-migrate up` rebuilds identically, idempotent rerun |
| P0-3 | Identity + RLS. **2nd-pass find:** `warden_core.subjects` ALREADY EXISTS in 0001 (kind enum, `external_id` = Authentik sub, email) — migration 0006 **extends** it (citext unique email, `email_verified_at`, status, `subject_tenant` w/ role, `identity.*` fns, `trg_*`, RLS, uuidv7 for new rows) — not green-field | migration 0006 + `internal/identity`; seed hades (jessay@gmail.com, customer + super_admin) | `identity.create_customer` round-trip; RLS blocks cross-tenant read |
| P0-4 | **Visible:** Cortex PR2+PR3 pulled forward | `internal/mesh` endpoints + `/admin` ControlLayerView + IntelligenceLayerView per CORTEX_CONTROL_LAYER_SPEC | operator sees both layers + connect snippets in the console |
| P0-5 | Merge `codex/*` → `main` behind CI (foreign WIP committed as-is first, own label) | — | `main` green, branch retired |
| P0-OP | **Operator key-turns (parallel):** Infisical machine-identity gate; stand up PG on Warden VM (Proxmox) | — | `/run/warden-secrets` populated; `10.0.0.102:5432` open |

## P1 — Product seam (visible slice: live intelligence data)

| # | Item | Boundary | Verify |
|---|---|---|---|
| P1-1 | Sync worker v1 (cron: validator JSON → Surreal + Qdrant upserts, content-hash idempotent, per SURREALDB_INTELLIGENCE_PROJECTION_V2) | `services/intelligence-sync` (operator-approved 2026-06-12) | decisions queryable via `cortex-intelligence`; rerun = no-op |
| P1-2 | PG cutover to Warden VM (snapshot + dumps preflight per rollout plan; repoint `WARDEN_DB_URL`/`CLYFFE_DB_URL`) | `services/*/internal/platform/config.go`, ops runbook | stack runs against 102; 110 read-only drained |
| P1-3 | Backups + restore drill (Proxmox schedule, `pg_dump` timer, W sync) | `modules/warden/infrastructure/devstation/` (runbook + units) | one successful restore, documented |
| P1-4 | Declarative devstation → `clyffe-code-workspace-template` (cloud-init + host YAML + systemd; clone-#47 test). **Includes the foundation scripts + plugins auto-copied per user/service** (process rule 4) | `modules/warden/infrastructure/devstation/` | clean clone boots with zero hand-edits, guard scripts present |

## P2 — Surfaces (visible slice: customer logs in, sees their devstation)

Connect & Launch UI → `src/domains/admin/` (customer plane folds here) ·
Clyffy chat boundary (overlay + pop-out) → `src/domains/clyffy/` ·
OIDC at auth.rrflow.ai replacing bootstrap → `services/warden-api/internal/identity/` ·
`.pulse` v1 schema → `schemas/pulse/pulse-packet.v1.schema.json`
(operator-approved 2026-06-12) + first signed packet.

## P3 — Scale

E→W clones (Clyffy-dean: clyffy-go, devpulse, makers) → ColdLight ingest +
MakersImpulse verification → real component conversion · v1→v2 touchpoint
sweep (CI-enforced after) + `api.ts` split to per-context `.svc.ts` ·
Cortex Streamable HTTP + OAuth 2.1 (ADR 0030 bar → formal-mcp) ·
`ConstraintBackend` (llguidance/XGrammar local, native structured outputs
hosted) when Blackwell lands.

## Locked integration pins (2nd-pass research, sourced 2026-06-12)

| Need | Pin | The gotcha that bites |
|---|---|---|
| OIDC (Authentik) | `coreos/go-oidc/v3` v3.18.0 + x/oauth2 | issuer = per-app slug URL **with trailing slash** (exact-match); `groups` claim via `profile` scope mapping |
| Postgres | pgx/v5 ≥ v5.9.2 security floor (**have v5.10.0 ✓**) | RLS context = tx-scoped `set_config('app.current_tenant',$1,true)` — never session `SET` on a pool; RLS skips table owner → dedicated non-owner app role |
| SurrealDB (intelligence-sync) | official `surrealdb.go` v1.4.0 (server v2.0→v3.1.4) | README "beta" badge is stale; pin exact server minor |
| Qdrant (intelligence-sync) | official `go-client` v1.18.2 | **gRPC-only, port 6334** (REST 6333 is the read-only plugin path); pin client to server minor |
| MCP phase-2 | official `modelcontextprotocol/go-sdk` v1.6.1 (GA) | needs Go 1.25+; migrating our hand-rolled stdio = port handlers into `ToolHandlerFor`, then HTTP is a transport swap |
| CI | `actions/setup-go@v6`, `go-version-file: go.work`, `cache-dependency-path: '**/go.sum'`; single Go job loop + parallel npm job | default cache key only hashes root go.mod — under-keys workspaces |

**2nd-pass boundary finds:** the storage context (`storage-broker-client` +
Rust `wardenclyffedisk`) is REAL and tested end-to-end (memory store) — the
per-tenant W-volume product already has its Go↔Rust seam; its POA&M items
(Postgres store, provisioner wiring, retire-SMB) interlock with P1/P2 here.
`WARDENCLYFFE_OFFICIAL_BASELINE.md` confirms the data-plane topology (Warden
VM = PG+Surreal; new Clyffy VM = Qdrant+Surreal; Surreal-Cloud export plan)
but its `apps/`-anchored frontend section is superseded (root `src/`, operator
2026-06-11) — noted in that doc.

## Alpha definition of done (locked 2026-06-12 — "truly established, move on")

1. Login → role switch works: identity context live (subjects + RLS), hades
   seeded, Admin↔Customer.
2. CI green on `main` (builds + vet + strict validator as merge law).
3. `warden-migrate` rebuilds the DB from zero on any clone.
4. **Both plugins connected in Claude Desktop** — `cortex-control` +
   `cortex-intelligence` visible, intelligence returning LIVE Surreal/Qdrant
   data (post Infisical key-turn).
5. `/admin` shows Control + Intelligence layers: active plugins, status,
   connect snippets.
6. Sync worker projecting; decisions queryable through the intelligence plugin.
7. One backup restore drill passed.
8. Every spec carries a "Minimal requirements" section; contexts ship complete
   (colocated types/svc/views) or not at all.

## Deliberate non-goals (until a second customer demands them)

Kubernetes, NATS, ClickHouse, multi-region, MCP federation L0 publish.
