---
wardenclyffe_touchpoint:
  version: 1
  kind: mcp-mesh
  namespace: wardenclyffe.mesh
  current_registry: wardenclyffe/registry/context-mesh.yaml
  template_source: wardenclyffe/.agents/templates/mcp-mesh-server
---

# MCP Mesh Touchpoints

WardenClyffe should use Markdown touchpoints plus a machine-readable registry
to help agents route work through the right tools and domains.

## Current Registry

The current working registry lives in:

- `wardenclyffe/registry/context-mesh.yaml`

That registry defines MCP domains, ownership, transport posture, endpoint
environment variables, and tool names for the Go-side Warden work.

Until a root registry is promoted, do not duplicate those contracts in this
repo. Point to them.

## Touchpoint Pattern

Use this frontmatter for new agent-facing Markdown touchpoints:

```yaml
---
wardenclyffe_touchpoint:
  version: 1
  kind: subsystem
  namespace: wardenclyffe.example
  owner: docs/path-or-module
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  agents:
    - codex
    - claude
    - cursor
  reads:
    - docs/ai/WARDENCLYFFE_BASE_SKILL.md
---
```

Keep touchpoints short. They should route agents to the source of truth, not
copy the source of truth.

## Mesh Rules

- One registry owns tool names and endpoint contracts.
- Markdown touchpoints explain how an agent should navigate to that registry.
- Warden tools are operator-facing.
- Clyffe tools are customer-safe and tenant-scoped.
- Proxmox tools must be wrapped behind Warden policy and audit.
- A planned formal MCP server should use `rmcp` unless a future ADR changes it.

## Candidate Domains

Use these as planned domain names unless the registry supersedes them:

- `mcp.global.warden`
- `mcp.global.proxmox`
- `mcp.global.clyffe`
- `mcp.global.crm`
- `mcp.global.support`
- `mcp.global.kb`
- `mcp.global.qdrant`
- `mcp.global.surreal`
- `mcp.global.postgres`

