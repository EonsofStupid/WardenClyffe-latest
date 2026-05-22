---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: mcp-mesh-touchpoints
  persona: clyffy-operator
  kind: mcp-mesh
  owner: docs/ai/MCP_MESH_TOUCHPOINTS.md
  current_registry: wardenclyffe/registry/context-mesh.yaml
  template_source: wardenclyffe/.agents/templates/mcp/l2-leaf-server
  observability:
    semconv_version: "1.40.0"
    trace_context_via_meta: true
---

# MCP Mesh Touchpoints

WardenClyffe uses Markdown touchpoints plus a machine-readable registry to
help agents route work through the right tools and domains. Touchpoint
shape is defined in **ADR 0033 (Touchpoint Protocol)**, revised 2026-05-22
to incorporate the May 2026 MCP baseline.

## Current Registry

The current working registry lives in:

- `wardenclyffe/registry/context-mesh.yaml`

That registry defines MCP domains, ownership, transport posture, endpoint
environment variables, tool names, **and now (per ADR 0030) Server Card
URLs, OTel posture, auth methods, Tasks support, state posture, and
workspace ownership** for the Go-side Warden work.

Until a root registry is promoted, do not duplicate those contracts in
this repo. Point to them.

## Touchpoint Pattern

Use this frontmatter shape (per revised ADR 0033 §1, v2) for new
agent-facing Markdown touchpoints:

```yaml
---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.example       # L0 workspace slug per ADR 0031 (= SurrealDB ns value)
  workspace_uuid: null                     # backfill when assigned in federation_workspace
  project_key: wardenclyffe-example        # narrower scoping inside the workspace
  persona: clyffy-operator                 # persona_definition row pk
  kind: subsystem
  owner: docs/path-or-module
  mesh_registry: wardenclyffe/registry/context-mesh.yaml

  agents:
    - codex
    - claude
    - cursor

  reads:
    - docs/ai/WARDENCLYFFE_BASE_SKILL.md

  capabilities:                            # optional but recommended (ADR 0032 awareness)
    mcp_gateway:
      url: null                            # set when L1 gateway is deployed for this workspace
      protocol_version: "2025-11-25"
      auth: oauth2.1+rfc9728               # per ADR 0030 §5

  observability:                           # optional but recommended (ADR 0030 §3)
    semconv_version: "1.40.0"
    trace_context_via_meta: true
---
```

Keep touchpoints short. They should route agents to the source of truth,
not copy the source of truth.

### v1 → v2 migration

The v1 frontmatter used `wardenclyffe_touchpoint:` and `namespace:`. v2
uses `clyffy_touchpoint:` and `workspace_id:` per ADR 0031 (D-1 resolution).
The validator at `scripts/foundation/validate-touchpoints.py` (planned)
accepts both during a deprecation window and reports v1 files as drift
events with explicit suggested edits.

The L0 sense ("which tenant") is **workspace**. The MCP-catalog sense
("which dotted scope") is still **namespace** — those are different
concepts per spec 09 §3.

## Mesh Rules

- One registry owns tool names and endpoint contracts.
- Markdown touchpoints explain how an agent should navigate to that registry.
- Markdown touchpoint sync is described in `docs/ai/TOUCHPOINT_SYNC_PATTERN.md`.
- Warden tools are operator-facing.
- Clyffe tools are customer-safe and tenant-scoped.
- Proxmox tools must be wrapped behind Warden policy and audit.
- A planned formal MCP server should use `rmcp` (version 0.16+) unless a future ADR changes it.
- Every formal MCP server published in this mesh meets the **ADR 0030 baseline**: Server Card at `/.well-known/mcp/server-card.json`, OAuth 2.1 + RFC 9728 for HTTP, OTel MCP semconv 1.40.0, stateless Streamable HTTP, no SSE, no Sampling.

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
- `mcp.workspace.clyffy-master.authentik` (workspace-private; the Auth-K specialist)

Per ADR 0032, leaves intended for a single workspace use the
`mcp.workspace.<slug>.<domain>` scope tier instead of `mcp.global.<domain>`.

## L1 federation gateways

Per ADR 0032 §1, each workspace MAY run an L1 gateway. Naming:

```
mcp.workspace.<workspace_slug>-gateway
```

Examples: `mcp.workspace.clyffy-master-gateway`,
`mcp.workspace.wardenclyffe-infra-gateway`. See spec 14 §4 for the gateway
contract.

## References

- ADR 0033 (revised) — Touchpoint Protocol (v2 frontmatter shape)
- ADR 0030 — MCP May 2026 Baseline
- ADR 0031 — Workspace Identity
- ADR 0032 — MCP Federation Three-Layer
- Spec 09 — Context Mesh and Naming
- Spec 14 — MCP Federation and Workspace
- Runbook `wardenclyffe/docs/runbooks/mcp-2026-alignment-checkpoint.md`
