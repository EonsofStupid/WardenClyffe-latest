---
clyffy_touchpoint:
  version: 2
  workspace_id: <workspace_slug>
  project_key: clyffy-specialist-<namespace>
  persona: <persona-name>
  kind: clyffy-specialist
  owner: templates/clyffy-specialists/<namespace>/touchpoint.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md
    - docs/CLYFFY_MCP_ORCHESTRATOR.md
    - templates/clyffy-specialists/_skeleton/manifest.yaml
    - templates/clyffy-specialists/<namespace>/manifest.yaml
    - wardenclyffe/.agents/templates/mcp-mesh-server/README.md
  sync:
    qdrant: true
    surreal: true
---

# <Persona Name> Specialist — <namespace>

> Canonical id: **`mcp.<project>.<namespace>`** — bucket: **`<bucket>`**
> — class: **`<class>`** — status: **`<status>`**.

<One paragraph plain-language description of what this specialist owns,
when to invoke it, and what is explicitly out of scope. Same content as
manifest.yaml `description:` so each surface can stand alone.>

## What This Specialist Knows On Arrival

Default capabilities (from manifest.yaml):

- `<capability-id>` — <one-line description>
- `<capability-id>` — <one-line description>

## What This Specialist Learns From The Project

The attunement pass reads the project's `AGENTS.md`, `clyffy_touchpoint`
frontmatter, package manifests (`package.json` / `Cargo.toml` /
`pyproject.toml`), and `docs/` index, indexes them into the RRD under
this project's workspace, and populates `seed.template.yaml` →
`seed.yaml`.

The specialist queries those projections at runtime via the RRD; it does
not re-read the project files on every call. This is the
passive-intelligence principle from
[`docs/ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md`](../../../docs/ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md)
applied to specialist onboarding.

## How To Invoke

Through the workspace gateway once it exists
(`mcp.clyffy-master.gateway`). Direct invocation against this leaf is
gated by the bucket's policy:

- `mcp.clyffy-master.*` — workspace-private; only Clyffy-routed callers.
- `mcp.wardenclyffe.*` — operator surface; called by Warden Go or
  operator-attached Claude/Codex.
- `mcp.global.*` — anyone in the registry can call.

## References

- Canonical contract: [`docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md`](../../../docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md)
- Orchestrator boundary: [`docs/CLYFFY_MCP_ORCHESTRATOR.md`](../../../docs/CLYFFY_MCP_ORCHESTRATOR.md)
- MCP-wire template: [`wardenclyffe/.agents/templates/mcp-mesh-server/README.md`](../../../wardenclyffe/.agents/templates/mcp-mesh-server/README.md)
- Wrapper rendering: [`docs/MCP_CLIENT_NORMALIZATION_SPEC.md`](../../../docs/MCP_CLIENT_NORMALIZATION_SPEC.md)
