---
wardenclyffe_touchpoint:
  version: 1
  kind: touchpoint-sync-pattern
  namespace: wardenclyffe.intelligence.touchpoint-sync
  owner: docs/ai/TOUCHPOINT_SYNC_PATTERN.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  vector_projection: qdrant
  graph_projection: surrealdb
---

# Touchpoint Sync Pattern

Markdown touchpoints are the human-authored routing layer. They should make AI
work cheaper by pointing agents to the right sources instead of copying the
whole system into every context window.

## Purpose

Touchpoints should:

- route agents to the right module, registry, doc, or code surface.
- declare ownership and safety boundaries.
- provide stable identifiers for indexing.
- sync small metadata and summaries into the intelligence layer.
- avoid becoming a second database.

## Source Of Truth

| Data | Source of truth |
|---|---|
| product/module boundaries | Markdown docs and code contracts |
| MCP tools and endpoints | Context Mesh registry |
| customer/product truth | product database layer |
| AI graph/reasoning projection | SurrealDB |
| vector retrieval | Qdrant |
| hot local context/cache | optional runtime cache |

Markdown can describe a fact. It should not be the only durable store for
customer/product facts.

## Touchpoint Shape

Minimum frontmatter:

```yaml
---
wardenclyffe_touchpoint:
  version: 1
  kind: subsystem
  namespace: wardenclyffe.example
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

The boring pipeline:

```text
Markdown touchpoint
  -> parse frontmatter
  -> normalize namespace and owner
  -> chunk short body summary
  -> write/refresh Qdrant vector point
  -> write/refresh SurrealDB graph projection
  -> report stale/missing/duplicate touchpoints in Warden
```

Qdrant point payload:

```text
id
path
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
```

SurrealDB projection:

```text
touchpoint
module
namespace
owned_by
routes_to
depends_on
sync_job
staleness_event
```

## Context Minimization Rules

- Keep the touchpoint itself short.
- Use stable namespace IDs instead of long explanations.
- Prefer links to canonical docs over copied sections.
- Index summaries and embeddings, not whole noisy files by default.
- Load full files only when an agent needs implementation detail.
- Track content hashes so unchanged docs do not re-index.
- Report stale routes in Warden rather than silently guessing.

## MCP Alignment

Current MCP practices to preserve:

- formal lifecycle negotiation before normal operation.
- deterministic tool/resource/prompt lists for cacheability.
- tools, resources, and prompts as separate surfaces.
- Streamable HTTP for remote formal MCP servers.
- stdio for local/private agent integrations.
- Origin validation and authentication on Streamable HTTP.
- leaf MCP servers for focused domains.
- Warden gateway only where client limits require it.

Primary references:

- https://modelcontextprotocol.io/specification/2025-11-25/basic/lifecycle
- https://modelcontextprotocol.io/specification/2025-06-18/basic/transports
- https://qdrant.tech/documentation/
- https://surrealdb.com/docs/what-is-surrealdb

## Warden UI Requirements

Warden should show:

- touchpoint count by module.
- stale touchpoints.
- missing owners.
- duplicate namespaces.
- Qdrant sync age.
- SurrealDB projection age.
- MCP registry drift.
- route from namespace/project to its internal mesh.

## Clyffe Boundary

Clyffe may read customer-safe KB touchpoints. It must not read operator-only
Proxmox, secrets, infrastructure, or internal mesh touchpoints unless Warden
explicitly exposes a sanitized resource.

