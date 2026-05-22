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

Markdown touchpoints are the human-authored routing layer. They make AI
work cheaper by pointing agents to the right sources instead of copying
the whole system into every context window.

Frontmatter shape per **ADR 0033 v2** (revised 2026-05-22 to use
`workspace_id`).

## Purpose

Touchpoints should:

- route agents to the right module, registry, doc, or code surface
- declare ownership and safety boundaries
- provide stable identifiers for indexing
- sync small metadata and summaries into the intelligence layer
- avoid becoming a second database

## Source Of Truth

| Data | Source of truth |
|---|---|
| product/module boundaries | Markdown docs and code contracts |
| MCP tools and endpoints | Context Mesh registry (`wardenclyffe/registry/context-mesh.yaml`) |
| L0 workspace identity | `federation_workspace` table (SurrealDB LXC 104), per ADR 0031 |
| customer/product truth | product database layer |
| AI graph/reasoning projection | SurrealDB |
| vector retrieval | Qdrant |
| hot local context/cache | optional runtime cache |

Markdown can describe a fact. It should not be the only durable store for
customer/product facts.

## Touchpoint Shape (v2)

Minimum frontmatter per ADR 0033 v2:

```yaml
---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.example     # L0 workspace slug per ADR 0031
  project_key: wardenclyffe-example      # row scoping
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
  -> validate workspace_id against federation_workspace (per ADR 0031)
  -> chunk short body summary
  -> write/refresh Qdrant vector point (collection scoped by workspace_id)
  -> write/refresh SurrealDB graph projection (ns = data plane per ADR 0025 §1, NOT workspace_id; see ADR 0031 §3 partial-equivalence rule. Default: ns=clyffy for intelligence-plane touchpoint metadata.)
  -> report stale/missing/duplicate touchpoints in Warden
```

The local validator/inventory script is:

```bash
python scripts/foundation/validate-touchpoints.py --json
```

That script does not write to Qdrant or SurrealDB. It prepares the clean
inventory that a future sync worker can consume.

Qdrant point payload:

```text
id
path
workspace_id                # NEW in v2 — collection scoping
project_key
namespace                   # MCP-catalog sense if applicable (NOT the tenant)
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
touchpoint                  # row in ns=clyffy (intelligence plane per ADR 0025); workspace_id is a field on the row, NOT the ns
workspace                   # the L0 entity per ADR 0031
project
module
owned_by
routes_to
depends_on
sync_job
staleness_event
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
schema remains `clyffy_touchpoint` frontmatter plus the registry. This keeps
the product friendly without making the agent contract fuzzy.

## Context Minimization Rules

- Keep the touchpoint itself short.
- Use stable workspace_id + project_key instead of long explanations.
- Prefer links to canonical docs over copied sections.
- Index summaries and embeddings, not whole noisy files by default.
- Load full files only when an agent needs implementation detail.
- Track content hashes so unchanged docs do not re-index.
- Report stale routes in Warden rather than silently guessing.

## MCP Alignment

Current MCP practices to preserve (per ADR 0030 May 2026 baseline):

- formal lifecycle negotiation before normal operation
- deterministic tool/resource/prompt lists for cacheability
- tools, resources, and prompts as separate surfaces
- Streamable HTTP for remote formal MCP servers (stateless by default)
- stdio for local/private agent integrations
- Server Card published at `/.well-known/mcp/server-card.json` (SEP-1649 / 2127)
- OAuth 2.1 + RFC 9728 Protected Resource Metadata for HTTP transport (MANDATORY)
- Origin AND Host validation on Streamable HTTP
- OpenTelemetry MCP semantic conventions 1.40.0 emission
- W3C trace context via JSON-RPC `params._meta`
- leaf MCP servers for focused domains (L2 per ADR 0032)
- L1 federation gateway per workspace when many leaves exist (per ADR 0032 §4)
- L0 workspace-publish surface for cross-workspace catalog advertising (per ADR 0032 §1)
- SSE transport DEPRECATED — do not use in new servers
- Sampling (`sampling/createMessage`) DEPRECATED — do not implement in new servers

Primary references:

- ADR 0030 — MCP May 2026 Baseline (canonical capability bar)
- ADR 0031 — Workspace Identity (workspace_id field; SurrealDB ns equivalence)
- ADR 0032 — MCP Federation Three-Layer
- Spec 09 — Context Mesh and Naming
- Spec 14 — MCP Federation and Workspace
- <https://modelcontextprotocol.io/specification/2025-11-25/basic/lifecycle>
- <https://modelcontextprotocol.io/specification/2025-11-25/basic/transports>
- <https://opentelemetry.io/docs/specs/semconv/gen-ai/mcp/>
- <https://qdrant.tech/documentation/>
- <https://surrealdb.com/docs/what-is-surrealdb>

## Warden UI Requirements

Warden should show:

- touchpoint count by module and by workspace
- stale touchpoints
- missing owners
- duplicate `project_key` within a workspace (uniqueness rule per ADR 0033 §9)
- Qdrant sync age
- SurrealDB projection age
- MCP registry drift (server present in registry vs published Server Card actually reachable)
- workspace federation health (per ADR 0017 / ADR 0031)
- route from workspace_id + project_key to its internal mesh

## Clyffe Boundary

Clyffe may read customer-safe KB touchpoints. It must not read
operator-only Proxmox, secrets, infrastructure, or internal mesh
touchpoints unless Warden explicitly exposes a sanitized resource.
Cross-workspace publication is governed by L0 (per ADR 0032 §1 and
spec 14 §5).
