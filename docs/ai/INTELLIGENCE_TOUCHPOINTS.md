---
wardenclyffe_touchpoint:
  version: 1
  kind: intelligence-layer
  namespace: wardenclyffe.intelligence
  current_contracts:
    - wardenclyffe/intelligence/contracts
    - wardenclyffe/registry/context-mesh.yaml
---

# Intelligence Layer Touchpoints

The intelligence layer is the routing and memory surface that agents use to
understand WardenClyffe without inventing context every time.

## Memory Boundaries

- PostgreSQL owns product truth: customers, tickets, CRM, service ownership,
  billing references, audit, and infrastructure inventory.
- Qdrant owns vector retrieval and semantic search.
- SurrealDB may own AI graph/reasoning projections.
- Markdown touchpoints route agents to those systems and document the contract.

## Agent Capture Rules

When an agent creates durable context:

- capture decisions in docs or ADRs,
- capture tool/domain contracts in the mesh registry,
- capture customer/product truth in the product database layer,
- capture AI reasoning/memory only through the approved intelligence contract,
- do not place secrets in Markdown, logs, or examples.

## Touchpoint Responsibilities

A touchpoint should answer:

- What subsystem does this file route?
- Which registry or DB owns the truth?
- Which agents should read it?
- What is safe for an agent to change?
- What requires operator approval?

## Current Go-Side Intelligence References

The nested Warden repo already includes intelligence scaffolding:

- `wardenclyffe/AGENTS.md`
- `wardenclyffe/REGISTRY.md`
- `wardenclyffe/registry/context-mesh.yaml`
- `wardenclyffe/intelligence/contracts/`
- `wardenclyffe/.cursor/rules/`
- `wardenclyffe/.cursor/skills/`
- `wardenclyffe/.codex/hooks/`

Use those as the reference pattern while this root repo is being organized.

