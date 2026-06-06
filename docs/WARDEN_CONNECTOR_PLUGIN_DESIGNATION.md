---
wardenclyffe_touchpoint:
  version: 1
  kind: connector-plugin-designation
  namespace: wardenclyffe.warden.designation
  owner: docs/WARDEN_CONNECTOR_PLUGIN_DESIGNATION.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/CLYFFY_MCP_ORCHESTRATOR.md
    - docs/FOUNDATION_SERVICE_MATRIX.md
    - docs/WARDENCLYFFE_NAMING_CONVENTIONS.md
    - docs/CLYFFY_DYNAMIC_UI_SPEC.md
    - docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md
    - docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md
    - data/schema/sql/0001_init.sql
    - data/schema/sql/0002_platforms_services.sql
---

# Warden Connector / Plugin Designation

**Status:** establishing (v1). This is the official, enforceable designation and
privilege pattern for every managed service Warden captures. It makes the
existing "AI-only vs operator" separation (today only implied in prose) a
first-class, database-backed, UI-visible designation.

## Why this exists (the gap)

- The mesh today names **MCP servers / leaves / gateways / tools** but has **no
  formal access designation** distinguishing what is shared with the human
  operator from what is reserved for the AI plane
  (`docs/CLYFFY_MCP_ORCHESTRATOR.md`, `docs/WARDENCLYFFE_NAMING_CONVENTIONS.md`).
- The AI-only rule exists only as prose: "never expose raw vector memory"
  (`docs/FOUNDATION_APP_RESEARCH_2026_05.md`), SurrealDB/Qdrant are AI-plane not
  product truth (`docs/FOUNDATION_SERVICE_MATRIX.md`). Prose is not enforceable.
- `warden_core.subjects.subject_kind` already enumerates
  `('operator','customer','service','ai')` (`data/schema/sql/0001_init.sql`) — we
  have the *principals* but no *access class* binding services to them.

This document closes that gap with two designations: **connector** and
**plugin**, aligned with the platform-industry pattern (shared, governed
connectors vs. capability-scoped internal plugins).

## The designation taxonomy

Every captured service (`warden_infra.services`) carries a **designation** and an
explicit **audiences** set. Designation is the access class; audiences are the
principal kinds permitted (subset of `operator`, `ai`, `customer`).

| Designation | Audiences | Meaning | Examples |
|---|---|---|---|
| **connector** | `operator` + `ai` (shared) | Infrastructure/control surfaces both the human operator and the AI orchestrator (Clyffy) use, **always brokered through Warden policy + audit**. Never raw to customers. | Proxmox, Postgres (product truth), PowerDNS, Authentik, OPNsense, step-ca, Caddy edge |
| **plugin** | `ai` only | The intelligence plane. AI-only capabilities; the operator sees only *status*, never raw data. Customers never touch these. | SurrealDB (graph), Qdrant (vector), Harrier (embedder), **RRD / Reason Ready Daemon (reranker)** |
| **platform** | `operator` + `ai` | A compute substrate that *runs* the above (not a capability itself). | Proxmox `server1`, the Coolify headless-Docker service |
| **core** | `operator` (+ `customer` via projection) | Warden/Clyffe product services themselves. | warden-api, clyffe-api, clyffy orchestrator |

**Connector ⊇ operator,ai. Plugin = ai-only.** This is the senior-dev invariant
the schema enforces (see §Schema) and the UI surfaces (see §UI status).

### Why this is the right cut (industry alignment)

This mirrors how platforms separate **connectors** (governed integrations a user
and the assistant both act through — e.g. a database or identity provider) from
**plugins/tools** (capabilities the assistant uses internally). It also matches
the WardenClyffe boundary table (`docs/CLYFFY_DYNAMIC_UI_SPEC.md` §Boundaries):
Warden owns infrastructure (connectors), Clyffy owns intelligence projections
(plugins), Clyffe sees only customer-safe outcomes.

## Privilege model (enforceable)

```
principal kinds            warden_core.subjects.subject_kind = operator | customer | service | ai
service access class       warden_infra.services.designation  = connector | plugin | platform | core
service audiences          warden_infra.services.audiences[]  ⊆ { operator, ai, customer }
AI identity + method        ai_bridge.identity_grants (radius | client_cert | password_failsafe, allowed_cidrs, policy)
mesh scope / policy root    mcp.<scope>.<domain> + policy.<scope>.<domain>   (context-mesh grammar)
```

Rules:
1. A request from principal kind `K` to service `S` is allowed only if
   `K ∈ S.audiences`. `customer` is **never** in a connector/plugin audience —
   customers reach data only via Clyffe's projected, tenant-scoped API.
2. **plugin** services accept `ai` only. Operator access to a plugin is limited
   to *status/health* through Warden (`/api/clyffy/plugins`), never raw queries.
3. **connector** services are reached **through Warden policy + audit**, never
   directly by the AI (`docs/CLYFFY_MCP_ORCHESTRATOR.md`: "`mcp.global.proxmox`
   through Warden policy, never directly to customers").
4. Deny precedence across scopes (`wardenclyffe/registry/context-mesh.yaml`
   `deny_precedence: true`).
5. Credentials are referenced (Infisical path), never embedded
   (`warden_infra.services.credential_ref`); brokered to `/run/warden-secrets`.

## RRD — Reason Ready Daemon (the reranker plugin)

RRD is **net-new** (not previously documented). It formalizes the "optional
rerank" stage already named in the retrieval pipeline
(`docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md`).

- **Role:** cross-encoder **reranker / encoder** that re-scores Qdrant candidate
  snippets for relevance before the context pack is assembled.
- **Designation:** `plugin` (AI-only). Audiences: `ai`.
- **Pipeline position:**

  ```
  text -> Harrier (embed) -> Qdrant (vector recall, top-k)
       -> RRD (rerank top-k -> top-n)        <-- NEW
       -> SurrealDB (graph expansion/refine)
       -> generated context pack
  ```

- **Stable id:** `service.rrd` · role `reranker` · scope `mcp.global.rrd` (leaf).
- **Model:** a cross-encoder reranker selected by `clyffy-embedder-bakeoff`
  (`docs/ai/HUGGINGFACE_WORKSPACE_TOUCHPOINT.md`); served TEI-style alongside
  Harrier. Until deployed it is captured as `state=planned`.

## MCP naming alignment

Designation is orthogonal to, and composes with, the existing grammar
(`docs/WARDENCLYFFE_NAMING_CONVENTIONS.md`):

```
connector  -> mcp.<scope>.<domain> wrapped by Warden policy   e.g. mcp.global.proxmox  (class: compat/leaf)
plugin     -> mcp.<scope>.<domain> AI-plane leaf              e.g. mcp.global.qdrant, mcp.global.rrd
policy root-> policy.<scope>.<domain>
tools      -> <domain>.<verb>_<object>                        e.g. qdrant.search_points, rrd.rerank
```

A connector/plugin **is** a captured `warden_infra.services` row + (optionally) an
MCP server entry in `wardenclyffe/registry/context-mesh.yaml`. The service row is
the system of record for access; the registry is the routing/scope record.

## Schema (this is established in code)

`data/schema/sql/0003_designation.sql`:
- `warden_infra.designation` enum: `connector | plugin | platform | core`.
- `warden_infra.services.designation warden_infra.designation`.
- `warden_infra.services.audiences text[]` (subset of operator/ai/customer), with
  a CHECK that `plugin ⇒ audiences = {ai}` and `connector ⇒ operator,ai ⊆ audiences`.
- Seeds classify the 18 captured services and add `service.rrd`.

## UI status

The console **Foundation** surface groups services by designation and shows the
audience + live status. Clyffy exposes:
- `GET /api/clyffy/plugins`    — AI-only intelligence plane + live probe.
- `GET /api/clyffy/connectors` — operator+AI shared surfaces (status only).
The operator sees plugin *status*, never raw plugin data — enforcing the rule in
the surface itself.

## Repo-driven config (Warden VM regularly loading changes)

The Warden VM should reconcile from the repo on a schedule (GitOps-lite):
`git pull` -> apply migrations (`data/schema/sql/*`) -> rebuild/redeploy
warden-api + console -> re-probe captured services. Tracked as
`docs/WARDEN_ESTABLISHMENT_POAM.md` item WDN-DEPLOY (repo reconciler). The first
implementation is a systemd timer on the Warden VM; see
`scripts/dev/run-stack.sh` for the manual equivalent today.

## References

- `docs/CLYFFY_MCP_ORCHESTRATOR.md` — Clyffy orchestrates, Warden executes, Bifrost bridges.
- `docs/FOUNDATION_SERVICE_MATRIX.md` — the 18 captured services + AI-plane rows.
- `docs/CLYFFY_DYNAMIC_UI_SPEC.md` — Boundaries table (Warden/Clyffy/Clyffe/Clyffe Code).
- `docs/WARDENCLYFFE_NAMING_CONVENTIONS.md` — mcp.*/tools.*/policy.* grammar, db schemas.
- `docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md`, `docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md` — memory model + rerank pipeline.
- `data/schema/sql/0002_platforms_services.sql` — platforms/services capture this builds on.
