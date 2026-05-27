---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: touchpoint-template
  persona: clyffy-operator
  kind: template
  owner: docs/ai/TOUCHPOINT_TEMPLATE.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
---

# Touchpoint Template

Copy this structure when adding a new Markdown touchpoint. Shape conforms
to **ADR 0033 (Touchpoint Protocol)** v2 — revised 2026-05-22 to use
`workspace_id` (per ADR 0031) and to surface MCP capabilities and
observability hints (per ADR 0030).

```markdown
---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.example       # L0 workspace slug per ADR 0031
                                           # (= SurrealDB ns value, equivalence per ADR 0031 §3)
  workspace_uuid: null                     # backfill when assigned in federation_workspace
  project_key: wardenclyffe-example        # narrower scoping inside the workspace
  persona: clyffy-operator                 # persona_definition row pk
  kind: subsystem                          # one of: subsystem | template | runtime | doc | runbook
  owner: <path-or-team>
  mesh_registry: wardenclyffe/registry/context-mesh.yaml

  agents:                                  # which agents care about this touchpoint
    - codex
    - claude
    - cursor

  reads:                                   # files this touchpoint asks agents to load
    - AGENTS.md
    - docs/ai/WARDENCLYFFE_BASE_SKILL.md

  capabilities:                            # optional — MCP routing per ADR 0032
    mcp_gateway:                           # set when an L1 gateway exists for this workspace
      url: null
      protocol_version: "2025-11-25"
      auth: oauth2.1+rfc9728               # per ADR 0030 §5
    mcp_servers: []                        # only set when direct-to-leaf access overrides gateway

  observability:                           # optional but recommended (ADR 0030 §3)
    semconv_version: "1.40.0"
    trace_context_via_meta: true

  audit:                                   # required for surfaces that write to audit_log
    event_prefix: <prefix>
    enabled: true

  scopes:                                  # OIDC scopes used by code under this touchpoint
    - clyffy:operate
---

# <Subsystem> Touchpoint

Purpose:

- What this subsystem owns.
- Which files are source of truth.
- Which actions are safe for agents.
- Which actions need operator approval.
- Where to go next.
- Which generated store or projection carries memory, if any.
```

Keep the body short. A touchpoint is a manifest and routing surface, not a
session log, memory dump, inventory snapshot, or customer record.

## Required fields per ADR 0033 §2

- `version` (currently `2`)
- `workspace_id` (the L0 tenant slug)
- `project_key`
- `persona`
- `surreal.plane_a` (for surfaces that write intelligence)
- `audit.event_prefix` (for surfaces that write audit_log)

## Recommended additions per ADR 0033 §3 (v2)

- `workspace_uuid` — machine join key
- `capabilities.mcp_gateway` — preferred over `mcp_servers` when an L1 gateway is deployed
- `observability.semconv_version` and `observability.trace_context_via_meta` — for OTel emission

## Inheritance

Touchpoints cascade — subdirectories inherit and may override. See
ADR 0033 §5 for the inheritance rule.

## Validation

Run `scripts/foundation/validate-touchpoints.py`
to catch drift. The validator enforces the v2 shape, reports oversized
sync-enabled touchpoints, and warns on
remaining v1 (`namespace_id` / `wardenclyffe_touchpoint:`) files until the
deprecation window closes.

## References

- ADR 0033 — Touchpoint Protocol (v2)
- ADR 0031 — Workspace Identity
- ADR 0032 — MCP Federation Three-Layer
- Spec 09 — Context Mesh and Naming
- `docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md`
- Runbook `wardenclyffe/docs/runbooks/mcp-2026-alignment-checkpoint.md`
