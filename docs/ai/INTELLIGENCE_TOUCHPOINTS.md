---
clyffy_touchpoint:
  version: 2
  workspace_id: clyffy.master
  project_key: intelligence-touchpoints
  persona: clyffy-operator
  kind: intelligence-layer-touchpoints
  owner: docs/ai/INTELLIGENCE_TOUCHPOINTS.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md
    - docs/ai/TOUCHPOINT_SYNC_PATTERN.md
    - docs/CLYFFY_MCP_ORCHESTRATOR.md
    - wardenclyffe/registry/context-mesh.yaml
  sync:
    qdrant: true
    surreal: true
---

# Intelligence Layer Touchpoints

Markdown touchpoints are the routing surface, not the memory layer. They help
agents find the right WardenClyffe source quickly, then hand off to typed
stores, projections, APIs, and generated context packs for actual memory.

Use `docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md` as the controlling contract
for this split.

## Memory Boundaries

- The chosen product database layer owns product truth: customers, tickets,
  CRM, service ownership, billing references, audit, and infrastructure
  inventory.
- Qdrant owns vector retrieval and semantic search.
- SurrealDB owns AI graph/reasoning projections where graph shape helps.
- Warden task/audit/event records own episodic execution history.
- ADRs, runbooks, policies, and MCP prompts/resources own procedural memory.
- Markdown touchpoints route agents to those systems and document the contract.

## Agent Capture Rules

When an agent creates durable context:

- capture decisions in docs or ADRs when they are human governance decisions,
- capture tool/domain contracts in the mesh registry,
- capture customer/product truth in the product database layer,
- capture AI reasoning/memory only through the approved intelligence contract,
- capture task/event history in Warden task, audit, or trace records,
- capture generated summaries through the sync worker or generated context-pack
  path, not hand-authored memory dumps,
- do not place secrets in Markdown, logs, or examples.

## Touchpoint Responsibilities

A touchpoint should answer:

- What subsystem does this file route?
- Which registry or DB owns the truth?
- Which agents should read it?
- What is safe for an agent to change?
- What requires operator approval?

It should not carry long transcripts, live API responses, customer state,
inventory snapshots, model scratchpads, or large manually maintained summaries.

## Sync Pattern

Use `docs/ai/TOUCHPOINT_SYNC_PATTERN.md` for the working Qdrant and
SurrealDB projection pattern. Markdown touchpoints route agents and provide
stable metadata; Qdrant stores vector retrieval; SurrealDB stores graph and
reasoning projections where useful. Warden/Postgres owns tasks, audit, and
product truth.

## Current Go-Side Intelligence References

The nested Warden repo already includes intelligence scaffolding:

- `wardenclyffe/AGENTS.md`
- `wardenclyffe/REGISTRY.md`
- `wardenclyffe/registry/context-mesh.yaml`
- `wardenclyffe/intelligence/contracts/`
- `wardenclyffe/.cursor/rules/`
- `wardenclyffe/.cursor/skills/`
- `wardenclyffe/.codex/hooks/`

Use those as the reference pattern while this root repo is being organized,
but do not copy old Markdown-memory habits forward. Promote typed projections
and generated context packs instead.

## Codex Chat Capture

`docs/ai/CODEX_INTELLIGENCE_SYNC_TOUCHPOINT.md` routes the home-local
`wardenclyffe-intelligence-sync` Codex plugin into the same intake path:
redacted hook events land in `wardenclyffe/.codex/memory-spool/events.jsonl`,
then `wardenclyffe/scripts/chat-dump-build.py` produces the canonical
append-only raw dump. The plugin must remain an intake layer; reviewable
candidate extraction and promotion stay with the existing intelligence
pipeline.
