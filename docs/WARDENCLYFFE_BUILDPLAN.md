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
| P0-2 | Migrations runner (own, tiny) | `services/warden-api/cmd/warden-migrate` over `data/schema/sql/000N_*.sql` + `warden_core.schema_migrations` | fresh DB → `warden-migrate up` rebuilds identically, idempotent rerun |
| P0-3 | Identity + RLS | migration 0006 per IDENTITY_TENANCY_SPEC (`subjects`, `subject_tenant`, `identity.*` fns, `trg_*`, RLS); Go `internal/identity` create/verify; seed hades (jessay@gmail.com, customer + super_admin) | `identity.create_customer` round-trip; RLS blocks cross-tenant read |
| P0-4 | **Visible:** Cortex PR2+PR3 pulled forward | `internal/mesh` endpoints + `/admin` ControlLayerView + IntelligenceLayerView per CORTEX_CONTROL_LAYER_SPEC | operator sees both layers + connect snippets in the console |
| P0-5 | Merge `codex/*` → `main` behind CI (foreign WIP committed as-is first, own label) | — | `main` green, branch retired |
| P0-OP | **Operator key-turns (parallel):** Infisical machine-identity gate; stand up PG on Warden VM (Proxmox) | — | `/run/warden-secrets` populated; `10.0.0.102:5432` open |

## P1 — Product seam (visible slice: live intelligence data)

| # | Item | Boundary | Verify |
|---|---|---|---|
| P1-1 | Sync worker v1 (cron: validator JSON → Surreal + Qdrant upserts, content-hash idempotent, per SURREALDB_INTELLIGENCE_PROJECTION_V2) | new `services/<name>` — **name pending operator approval** (candidate: `intelligence-sync`); boundary-guard before creation | decisions queryable via `cortex-intelligence`; rerun = no-op |
| P1-2 | PG cutover to Warden VM (snapshot + dumps preflight per rollout plan; repoint `WARDEN_DB_URL`/`CLYFFE_DB_URL`) | `services/*/internal/platform/config.go`, ops runbook | stack runs against 102; 110 read-only drained |
| P1-3 | Backups + restore drill (Proxmox schedule, `pg_dump` timer, W sync) | `modules/warden/infrastructure/devstation/` (runbook + units) | one successful restore, documented |
| P1-4 | Declarative devstation → `clyffe-code-workspace-template` (cloud-init + host YAML + systemd; clone-#47 test). **Includes the foundation scripts + plugins auto-copied per user/service** (process rule 4) | `modules/warden/infrastructure/devstation/` | clean clone boots with zero hand-edits, guard scripts present |

## P2 — Surfaces (visible slice: customer logs in, sees their devstation)

Connect & Launch UI → `src/domains/admin/` (customer plane folds here) ·
Clyffy chat boundary (overlay + pop-out) → `src/domains/clyffy/` ·
OIDC at auth.rrflow.ai replacing bootstrap → `services/warden-api/internal/identity/` ·
`.pulse` v1 schema → `schemas/pulse/pulse-packet.v1.schema.json` (**folder name
pending operator approval**) + first signed packet.

## P3 — Scale

E→W clones (Clyffy-dean: clyffy-go, devpulse, makers) → ColdLight ingest +
MakersImpulse verification → real component conversion · v1→v2 touchpoint
sweep (CI-enforced after) + `api.ts` split to per-context `.svc.ts` ·
Cortex Streamable HTTP + OAuth 2.1 (ADR 0030 bar → formal-mcp) ·
`ConstraintBackend` (llguidance/XGrammar local, native structured outputs
hosted) when Blackwell lands.

## Deliberate non-goals (until a second customer demands them)

Kubernetes, NATS, ClickHouse, multi-region, MCP federation L0 publish.
