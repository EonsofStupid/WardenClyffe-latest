---
clyffy_touchpoint:
  version: 2
  workspace_id: clyffy.master
  project_key: intelligence-layer-modernization
  persona: clyffy-operator
  kind: intelligence-layer-contract
  owner: docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/ai/INTELLIGENCE_TOUCHPOINTS.md
    - docs/ai/TOUCHPOINT_SYNC_PATTERN.md
    - docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md
    - docs/CLYFFY_MCP_ORCHESTRATOR.md
    - wardenclyffe/registry/context-mesh.yaml
  sync:
    qdrant: true
    surreal: true
---

# Intelligence Layer Modernization

Markdown is not the WardenClyffe intelligence layer.

Markdown touchpoints are small, human-authored control files. They tell agents
what a subsystem owns, where truth lives, which registry entry applies, what is
safe to change, and which generated intelligence surfaces should be queried.
They must not become the durable memory store.

## Target Split

| Concern | Authority |
|---|---|
| Product truth | Postgres: tenants, users, RBAC, CRM, tickets, inventory, lifecycle, billing references, task state, audit |
| Tool and MCP routing | `wardenclyffe/registry/context-mesh.yaml` plus small v2 Markdown touchpoints |
| Semantic retrieval | Qdrant collections scoped by `workspace_id`, with dense+sparse/hybrid retrieval where useful |
| Graph and relationship projection | SurrealDB rows/edges for workspace, project, tool, service, handoff, and provenance graph |
| Episodic execution memory | Warden tasks, approvals, audit events, trace ids, durable run records, and generated summaries |
| Procedural memory | Versioned runbooks, ADRs, policies, tool contracts, and MCP prompts/resources |
| Working memory | Current agent session plus generated context packs; never treated as source of truth |
| Observability | AI Observatory and OpenTelemetry MCP semantic conventions |

The modern pattern is authored contracts plus generated projections:

```text
registry + touchpoints + code/contracts
  -> validator
  -> sync worker
  -> Postgres task/audit records
  -> Qdrant retrieval points
  -> SurrealDB graph projection
  -> generated context packs
  -> Warden UI status and drift reports
```

## Memory Model

Use these names consistently:

- Semantic memory: approved facts and entities, owned by Postgres or generated
  from approved sources and indexed into Qdrant.
- Episodic memory: what happened, owned by Warden tasks, audits, traces, and
  generated run summaries.
- Procedural memory: how work should happen, owned by ADRs, runbooks, policies,
  MCP prompts, and tool contracts.
- Retrieval memory: Qdrant search projection, not a truth store.
- Graph memory: SurrealDB relationship projection, not a replacement for
  Postgres product truth.
- Working memory: the current context window and temporary generated context
  packs, discarded or regenerated as needed.

## Touchpoint Rules

A Markdown touchpoint should be a manifest, not a chapter.

- Keep sync-enabled touchpoints under the validator threshold unless there is a
  deliberate exception.
- Prefer stable identifiers, owner paths, and links over copied explanations.
- Put customer, inventory, ticket, billing, and task truth in Postgres.
- Put relationship-heavy AI projections in SurrealDB.
- Put search payloads, summaries, and embeddings in Qdrant.
- Put long run logs in task/audit storage, not Markdown.
- Put generated context packs outside authored docs or mark them generated.
- Do not store secrets, tokens, private keys, cookies, live API payloads, or raw
  customer data in Markdown.

## Clyffy Orchestrator Rule

Clyffy should not answer by rereading giant Markdown memory files. It should:

1. Load the small touchpoint manifest for routing.
2. Resolve the registry entry and workspace scope.
3. Query Warden APIs for product/task truth.
4. Query Qdrant for relevant retrieval snippets.
5. Query SurrealDB for graph context and provenance.
6. Build a generated context pack for the current task.
7. Cite the source touchpoint, registry entry, task, or audit record.

## Enforcement

`scripts/foundation/validate-touchpoints.py` is the local enforcement surface.
It must report:

- v1 touchpoints that need migration to `clyffy_touchpoint` v2;
- missing `workspace_id`, `project_key`, `kind`, or `owner`;
- sync-enabled touchpoints that are too large;
- Qdrant/SurrealDB sync eligibility counts.

The future sync worker should use the validator output as input, then write
fresh projections and status rows. The validator itself remains read-only.

`docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md` defines the dynamic
SurrealDB projection model for touchpoints, graph relations, generated context
packs, drift events, checkpoints, and Warden UI health.

## References

- LangGraph memory model: <https://docs.langchain.com/oss/javascript/concepts/memory>
- MCP resources: <https://modelcontextprotocol.io/specification/2025-11-25/server/resources>
- MCP tools: <https://modelcontextprotocol.io/specification/2025-11-25/server/tools>
- MCP prompts: <https://modelcontextprotocol.io/specification/2025-11-25/server/prompts>
- MCP roots: <https://modelcontextprotocol.io/specification/2025-11-25/client/roots>
- MCP transports: <https://modelcontextprotocol.io/specification/2025-11-25/basic/transports>
- MCP authorization: <https://modelcontextprotocol.io/specification/2025-06-18/basic/authorization>
- Qdrant hybrid queries: <https://qdrant.tech/documentation/search/hybrid-queries/>
- SurrealDB vector model: <https://surrealdb.com/docs/learn/data-models/vector-search/overview>
- SurrealDB agent rules: <https://surrealdb.com/docs/build/ai-agents/agent-rules>
- OpenTelemetry MCP semantic conventions: <https://opentelemetry.io/docs/specs/semconv/gen-ai/mcp/>
