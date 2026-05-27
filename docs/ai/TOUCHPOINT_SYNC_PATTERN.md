---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: touchpoint-sync-pattern
  persona: clyffy-operator
  kind: touchpoint-sync-pattern
  owner: docs/ai/TOUCHPOINT_SYNC_PATTERN.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  vector_projection: qdrant
  graph_projection: surrealdb
  observability:
    semconv_version: "1.40.0"
    trace_context_via_meta: true
---

# Touchpoint Sync Pattern

Markdown touchpoints are the human-authored routing layer. They make AI work
cheaper by pointing agents to the right sources instead of copying the whole
system into every context window.

They are not the intelligence layer. The intelligence layer is the generated,
typed, queryable set of product records, events, retrieval points, graph
projections, traces, and context packs described in
`docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md`.

Frontmatter shape per ADR 0033 v2 uses `workspace_id`.

## Purpose

Touchpoints should:

- route agents to the right module, registry, doc, or code surface;
- declare ownership and safety boundaries;
- provide stable identifiers for indexing;
- sync small metadata and summaries into the intelligence layer;
- avoid becoming a second database.

## Source Of Truth

| Data | Source of truth |
|---|---|
| product/module boundaries | Markdown docs and code contracts |
| MCP tools and endpoints | Context Mesh registry (`wardenclyffe/registry/context-mesh.yaml`) |
| L0 workspace identity | `federation_workspace` table, per ADR 0031 |
| customer/product truth | product database layer |
| Warden tasks, approvals, audit, run history | Postgres task/audit/event tables and trace references |
| AI graph/reasoning projection | SurrealDB generated projection |
| vector retrieval | Qdrant |
| generated context packs | sync worker/runtime cache, regenerated from sources |
| hot local context/cache | optional runtime cache, never source of truth |

The main orchestrator contract is `docs/CLYFFY_MCP_ORCHESTRATOR.md`. It is a
sync-enabled touchpoint and should be one of the first documents indexed into
Qdrant and projected into SurrealDB.

Markdown can describe a fact. It should not be the only durable store for
customer/product facts, run history, inventory, graph state, or AI memory.

## Touchpoint Shape (v2)

Minimum frontmatter per ADR 0033 v2:

```yaml
---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.example
  project_key: wardenclyffe-example
  persona: clyffy-operator
  kind: subsystem
  owner: docs/example.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  module: module-01-warden
  sync:
    qdrant: true
    surreal: true
---
```

Minimum body:

```text
# Name

What this file routes.
What owns the truth.
What agents can change.
What requires approval.
Where to go next.
```

## Sync Pipeline

```text
Markdown touchpoint
  -> parse frontmatter (require version, workspace_id, project_key, persona)
  -> normalize workspace_id and owner
  -> validate workspace_id against federation_workspace
  -> reject or warn on oversized sync touchpoints
  -> chunk short manifest summary
  -> write/refresh Qdrant vector point scoped by workspace_id
  -> write/refresh SurrealDB graph projection
  -> link Warden task/audit/trace records where this touchpoint caused work
  -> generate temporary context packs for agent runs
  -> report stale/missing/duplicate touchpoints in Warden
```

The local validator/inventory script is:

```bash
python scripts/foundation/validate-touchpoints.py --json
```

That script does not write to Qdrant or SurrealDB. It prepares the clean
inventory that a future sync worker can consume.

Use the size checks to keep Markdown honest:

```bash
python scripts/foundation/validate-touchpoints.py --max-sync-body-words 1200
```

If a sync-enabled touchpoint exceeds the threshold, split the detail into a
canonical source doc, data store, registry entry, generated projection, or
runtime context pack. Do not keep expanding the touchpoint.

## Projection Payloads

Qdrant point payload:

```text
id
path
workspace_id
project_key
namespace
kind
module
owner
title
summary
tags
updated_at
content_hash
source_commit
body_words
projection_policy
```

SurrealDB projection:

```text
touchpoint
workspace
project
module
owned_by
routes_to
depends_on
sync_job
staleness_event
generated_context_pack
source_trace
```

Generated context pack:

```text
context_pack
workspace_id
project_key
source_touchpoints
source_records
source_query_hash
summary
expires_at
created_by
trace_id
```

## Workspace Touchpoint Sets

Managed workspaces such as Clyffe Code should have a small touchpoint set
instead of one giant context file:

- workspace overview;
- repo/project overview;
- dependency and preflight summary;
- MCP leaf/tool routing;
- Clyffy assistant memory boundary;
- customer visibility boundary;
- active Warden tasks and audit links.

The UI may present these as clickable knowledge "touchpads", but the durable
schema remains `clyffy_touchpoint` frontmatter plus the registry.

## Context Minimization Rules

- Keep the touchpoint itself short.
- Use stable workspace_id + project_key instead of long explanations.
- Prefer links to canonical docs over copied sections.
- Index summaries and embeddings, not whole noisy files by default.
- Load full files only when an agent needs implementation detail.
- Track content hashes so unchanged docs do not re-index.
- Report stale routes in Warden rather than silently guessing.
- Store run transcripts, decisions waiting for approval, and tool results in
  Warden task/audit/event records, then generate summaries from there.
- Treat context packs as disposable build artifacts. Regenerate them from
  Postgres, Qdrant, SurrealDB, registry, and touchpoint sources.

## MCP Alignment

Current MCP practices to preserve:

- formal lifecycle negotiation before normal operation;
- deterministic tool/resource/prompt lists for cacheability;
- tools, resources, and prompts as separate surfaces;
- Resources as application-driven context, selected by host applications;
- Streamable HTTP for remote formal MCP servers;
- stdio for local/private agent integrations;
- Server Card published at `/.well-known/mcp/server-card.json` where supported;
- OAuth 2.1 plus RFC 9728 Protected Resource Metadata for HTTP transport;
- Origin and Host validation on Streamable HTTP;
- OpenTelemetry MCP semantic conventions;
- trace context through JSON-RPC `params._meta`;
- leaf MCP servers for focused domains;
- L1 federation gateway per workspace when many leaves exist;
- L0 workspace-publish surface for cross-workspace catalog advertising.

Primary references:

- ADR 0030 - MCP May 2026 Baseline
- ADR 0031 - Workspace Identity
- ADR 0032 - MCP Federation Three-Layer
- Spec 09 - Context Mesh and Naming
- Spec 14 - MCP Federation and Workspace
- `docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md`
- `docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md`
- <https://modelcontextprotocol.io/specification/2025-11-25/server/resources>
- <https://modelcontextprotocol.io/specification/2025-11-25/server/tools>
- <https://modelcontextprotocol.io/specification/2025-11-25/server/prompts>
- <https://modelcontextprotocol.io/specification/2025-11-25/client/roots>
- <https://modelcontextprotocol.io/specification/2025-11-25/basic/transports>
- <https://modelcontextprotocol.io/specification/2025-06-18/basic/authorization>
- <https://opentelemetry.io/docs/specs/semconv/gen-ai/mcp/>
- <https://qdrant.tech/documentation/search/hybrid-queries/>
- <https://surrealdb.com/docs/learn/data-models/vector-search/overview>
- <https://docs.langchain.com/oss/javascript/concepts/memory>

## Warden UI Requirements

Warden should show:

- touchpoint count by module and by workspace;
- stale touchpoints;
- missing owners;
- oversized sync-enabled touchpoints;
- duplicate `project_key` within a workspace;
- Qdrant sync age;
- SurrealDB projection age;
- generated context-pack age and source hash;
- MCP registry drift;
- workspace federation health;
- route from workspace_id + project_key to its internal mesh.

## Clyffe Boundary

Clyffe may read customer-safe KB touchpoints. It must not read operator-only
Proxmox, secrets, infrastructure, or internal mesh touchpoints unless Warden
explicitly exposes a sanitized resource. Cross-workspace publication is governed
by the L0 workspace publish surface.
