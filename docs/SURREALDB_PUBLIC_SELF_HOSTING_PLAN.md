---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: surrealdb-public-self-hosting
  persona: clyffy-operator
  kind: surrealdb-public-establishment-plan
  owner: docs/SURREALDB_PUBLIC_SELF_HOSTING_PLAN.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md
    - wardenclyffe/docs/cheatsheets/surrealdb-v3.0.5-master.md
    - docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
    - docs/FOUNDATION_SERVICE_MATRIX.md
    - docs/WARDEN_ESTABLISHMENT_POAM.md
  sync:
    qdrant: true
    surreal: true
---

# SurrealDB Public Self-Hosting Plan

This captures how WardenClyffe should turn SurrealDB into a real self-hosted,
publicly reachable foundation service without making the raw database a casual
internet target.

## Decision

Self-host SurrealDB as the WardenClyffe intelligence context layer. Expose it
to the public internet only through controlled, auditable routes:

1. Warden API for normal application and agent traffic.
2. Authentik-protected Surrealist/admin route for human operators.
3. Optional SurrealMCP adapter route for agents, brokered by Warden policy.
4. Raw `/rpc` or `/sql` access only for tightly scoped service credentials,
   never anonymous browser/user access.

The service may be reachable at a friendly public hostname, but Warden owns the
policy boundary. SurrealDB remains a projection/context engine, not the
customer/product source of truth.

## Current Baseline

Read-only check on 2026-05-22:

- LXC `104` hostname is `surreal`.
- SurrealDB service is active.
- `/usr/local/bin/surreal version` reports `3.0.5 for linux on x86_64`.
- `/var/lib/surrealdb` and `/etc/surrealdb` exist.
- Public `104.176.44.101:8000` did not accept a TCP connection.
- SurrealDB now listens on `10.0.0.104:8000`, not `0.0.0.0:8000`.
- The service uses `SURREAL_PATH=surrealkv:///var/lib/surrealdb`; do not put the
  storage URL after optional capability flags.
- Daily local export timer is enabled through
  `warden-surreal-export-local.timer`.
- Cloud export is blocked until the current Surreal Cloud endpoint stops
  returning `503` HTTPS health and `403` WebSocket responses.

See `docs/SURREALDB_SELF_HOSTED_RUNBOOK.md` for live service and restore
details.

## Smart Moves From The SurrealDB Dev Portal

| SurrealDB pattern | WardenClyffe move |
|---|---|
| Context layer for AI agents | Use SurrealDB for graph, documents, temporal facts, context packs, and projection lineage. |
| Agent rules | Vendor-import SurrealDB agent rules into a reviewed reference area, then map them into WardenClyffe base skills. |
| SurrealMCP | Treat as an adapter for IDE/agent access, not as a bypass around Warden approvals. |
| SurrealKit migrations | Move `.surql` schema into sync/rollout workflow: fast dev sync, controlled production rollout. |
| Capabilities | Start with `--deny-all`, then allow only the exact functions/network targets needed. |
| JWT/JWKS access | Use Authentik-issued JWT/JWKS for service or record access where direct DB auth is justified. |
| SCHEMAFULL + events | Use typed projection tables, `DEFINE EVENT` for enqueue/status, workers for heavy embedding/Qdrant work. |
| LIVE SELECT/changefeeds | Power Warden UI refresh status, drift, context-pack age, and worker health without polling. |
| Surrealist UI | Publish only behind Authentik, MFA, operator groups, and edge logs. |
| Export/import | Make `surreal export` backups and restore drills mandatory before public exposure. |

## Public Hostname Shape

Use boring names and split the surfaces:

| Hostname | Audience | Route |
|---|---|---|
| `surreal-admin.clyffy.ai` | Warden operators | Authentik -> Caddy -> Surrealist/admin tools |
| `surreal-api.clyffy.ai` | approved services/agents | Caddy -> Warden policy proxy -> SurrealDB `/rpc` or `/sql` |
| `surreal-mcp.clyffy.ai` | approved MCP clients | Caddy -> Warden MCP gateway -> SurrealMCP/adapter |

Do not publish LXC `104:8000` directly. Public DNS points to the edge, not the
database host.

## Target Runtime Posture

- SurrealDB listens on an internal address only.
- Caddy terminates TLS and forwards only approved paths.
- Authentik protects human/admin UI.
- Warden policy proxy mints or validates short-lived credentials.
- Infisical owns root/admin/service secrets.
- OPNsense restricts inbound traffic to Caddy and internal traffic from Caddy
  to SurrealDB.
- All schema changes go through SurrealKit-style rollout or reviewed `.surql`
  files.
- Backups run before schema rollout and on schedule.
- Warden UI shows service health, version, backup age, schema version, active
  projection workers, drift count, and public route status.

## Build Order

1. Verify current LXC `104` version, storage engine, service flags, and backup
   status.
2. Rotate root/admin credentials into Infisical-backed operator flow.
3. Bind SurrealDB internal-only and confirm no public forward reaches `:8000`.
4. Add backup/export and restore drill.
5. Put schema under rollout discipline and apply the v2 projection schema only
   after dry-run review.
6. Stand up Caddy routes for admin/API/MCP surfaces with no raw DB exposure.
7. Gate admin UI through Authentik and MFA.
8. Add Warden policy proxy for service/agent traffic.
9. Add Warden UI health, drift, backup, route, and worker panels.
10. Publish DNS only after health checks and rollback are ready.

## Hard No

- No anonymous public SurrealDB query access.
- No root credentials in Caddy configs, scripts, repo files, or chat.
- No customer truth in SurrealDB.
- No direct DB-as-a-service resale without a licensing review.
- No Qdrant writes from database events; workers own heavy jobs.

## References

- SurrealDB docs: `https://surrealdb.com/docs`
- Agent rules: `https://surrealdb.com/docs/build/ai-agents/agent-rules`
- Capabilities: `https://surrealdb.com/docs/surrealdb/security/capabilities`
- Authentication: `https://surrealdb.com/docs/surrealdb/security/authentication`
- JWT access: `https://surrealdb.com/docs/surrealql/statements/define/access/jwt`
- SurrealMCP: `https://surrealdb.com/blog/introducing-surrealmcp`
- SurrealKit migrations: `https://surrealdb.com/blog/schema-migrations-in-surrealdb-a-local-dev-workflow`
