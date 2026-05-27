---
clyffy_touchpoint:
  version: 2
  workspace_id: clyffy.master
  project_key: surrealdb-intelligence-projection-v2
  persona: clyffy-operator
  kind: surrealdb-projection-contract
  owner: docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md
    - docs/ai/TOUCHPOINT_SYNC_PATTERN.md
    - wardenclyffe/docs/cheatsheets/surrealdb-v3.0.5-master.md
    - wardenclyffe/surreal/schema/001_ai_memory.surql
    - wardenclyffe/surreal/schema/003_wardenclyffe_infra.surql
    - schemas/intelligence/surreal-touchpoint-projection.v2.surql
  sync:
    qdrant: true
    surreal: true
---

# SurrealDB Intelligence Projection V2

This is the concrete plan for making WardenClyffe v2 touchpoints dynamic,
maintainable, and graph-native without turning Markdown into memory.

## Current Baseline

The nested Go Warden repo already has useful SurrealDB patterns:

- `wardenclyffe/surreal/schema/001_ai_memory.surql` has schemafull memory,
  sessions, tool events, relations, changefeeds, and projection jobs.
- `wardenclyffe/surreal/schema/003_wardenclyffe_infra.surql` has schemafull
  infrastructure and operations-plane projections.

Those are reference implementations to absorb. Root WardenClyffe should not
fork that model into prose. It should promote an additive v2 projection schema
and a sync worker that can be tested repeatedly.

## V2 Dynamic Pattern

```text
Markdown touchpoints
  -> validator inventory
  -> sync run row
  -> touchpoint projection rows
  -> graph relations between touchpoints, modules, services, MCP servers, tasks
  -> Qdrant vector points for retrieval
  -> generated context packs for the current agent/task
  -> Warden UI health, drift, and stale-route panels
```

This keeps maintenance boring:

- authored Markdown remains small;
- dynamic rows are regenerated from source hashes;
- graph edges are queryable and traversable;
- UI state comes from projection rows, not scraped Markdown;
- every projection run has a status and error surface.

## SurrealDB Capabilities To Use

| Capability | WardenClyffe use |
|---|---|
| SCHEMAFULL tables | projection contracts, sync runs, context packs, drift events |
| FLEXIBLE object fields | provider-specific metadata without schema churn |
| Record links | direct links to workspace, project, module, service, and source rows |
| Graph relations | routes-to, depends-on, supersedes, emits, mentions, uses-source |
| Changefeeds | Warden UI sync age, stale projection detection, worker resume |
| DEFINE EVENT | enqueue projection work when source or status rows change |
| Computed fields | derived staleness/health labels once SurrealDB v3 is the runtime baseline |
| Full-text search | local keyword search over compact summaries and titles |
| Vector search | optional local graph-adjacent reranking; Qdrant remains primary retrieval initially |
| LIVE SELECT | reactive Warden/Clyffy dashboards for sync runs, drift, and context packs |
| Permissions | future tenant/customer-safe read surfaces after Warden owns policy |

## Tables

Use `schemas/intelligence/surreal-touchpoint-projection.v2.surql` as the
proposed additive schema.

Core tables:

- `touchpoint_projection`: one row per v2/v1 touchpoint inventory item.
- `touchpoint_source`: source file hash, title, word counts, and sync flags.
- `context_pack`: generated task/session context bundle, disposable and
  reproducible from sources.
- `projection_run`: one validator/sync worker run.
- `projection_event`: append-only worker events and errors.
- `projection_checkpoint`: resumable cursor/checkpoint per worker.
- `projection_drift`: stale owner, v1 shape, missing field, broken route,
  oversized body, registry mismatch.

Graph relation tables:

- `routes_to`: touchpoint to registry/tool/service/module.
- `depends_on`: touchpoint to touchpoint/source/registry dependency.
- `uses_source`: context pack to touchpoint/source/task/audit record.
- `emits_pack`: projection run to context pack.
- `supersedes_projection`: replacement chain when a touchpoint changes.
- `mentions_service`: touchpoint/source to Warden service or host projection.

## Worker Responsibilities

The sync worker should:

1. Run `scripts/foundation/validate-touchpoints.py --json`.
2. Upsert `touchpoint_source` rows with path, hash, title, body word count,
   sync flags, v1/v2 state, and source commit.
3. Upsert `touchpoint_projection` rows with `workspace_id`, `project_key`,
   owner, module, kind, status, and projection policy.
4. Create graph relation rows from `reads`, registry routes, module ownership,
   and generated context-pack source lists.
5. Write `projection_drift` rows for warnings.
6. Upsert Qdrant points only for approved sync rows.
7. Generate `context_pack` rows for current task/session requests.
8. Emit `projection_event` rows for Warden UI and observability.

The worker must be idempotent. A repeated run with the same source hashes
should change only run metadata and health timestamps.

## Retrieval Strategy

Use Qdrant first for broad retrieval because it is already a dedicated
foundation service. Use SurrealDB for graph-aware refinement:

```text
Qdrant candidate snippets
  -> SurrealDB graph expansion by workspace/project/service/task
  -> optional Surreal full-text/vector rerank over compact summaries
  -> generated context pack
```

SurrealDB vector fields should start with compact summaries, not entire source
files. That keeps graph queries responsive and avoids duplicating Qdrant.

## Warden UI Shape

Warden should show:

- total touchpoints by v1/v2, workspace, module, and sync flag;
- stale or broken projections;
- oversized touchpoints;
- source hash and last sync run;
- graph route from workspace -> project -> touchpoint -> MCP/service/task;
- generated context-pack list with source hashes and expiry;
- LIVE status for sync runs and drift events.

## Promotion Order

1. Keep this plan and schema as root proposal.
2. Migrate high-value root touchpoints to v2.
3. Build a dry-run sync worker that writes JSON only.
4. Apply the additive Surreal schema to a non-production namespace/database.
5. Run one read-only projection pass against local docs.
6. Add Qdrant upsert behind a flag.
7. Add Warden UI read models.
8. Promote the schema into the Go Warden control plane after review; any
   future Rust consumer follows the Go-verified contract.

## Do Not Do

- Do not put raw chat transcripts into touchpoints.
- Do not make SurrealDB the product/customer truth store.
- Do not duplicate Qdrant with full-document vectors in SurrealDB.
- Do not expose operator-only graph rows to Clyffe customers.
- Do not apply this schema live without backup, target preflight, and an
  explicit operator action.
