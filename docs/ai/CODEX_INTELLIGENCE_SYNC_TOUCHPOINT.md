---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  workspace_uuid: null
  project_key: wardenclyffe-codex-intelligence-sync
  persona: clyffy-operator
  kind: intelligence-route
  owner: docs/ai/CODEX_INTELLIGENCE_SYNC_TOUCHPOINT.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml

  agents:
    - codex

  reads:
    - docs/ai/WARDENCLYFFE_BASE_SKILL.md
    - docs/ai/INTELLIGENCE_TOUCHPOINTS.md
    - docs/ai/MCP_MESH_TOUCHPOINTS.md
    - wardenclyffe/intelligence/contracts/chat-dump-envelope-v1.md
    - wardenclyffe/docs/decisions/0031-workspace-identity.md
    - wardenclyffe/docs/decisions/0033-touchpoint-protocol.md

  surreal:
    plane_a:
      url: null
      ns: clyffy
      db: ai_memory

  intel_hook:
    capture_chats: true
    source: codex-home-plugin
---

# Codex Intelligence Sync Touchpoint

The home-wide Codex plugin `wardenclyffe-intelligence-sync` captures Codex
hook events and routes them into the existing WardenClyffe intelligence
pipeline.

## Contract

- Plugin source: `C:\Users\jessa\.codex\plugins\wardenclyffe-intelligence-sync`.
- Plugin marketplace: `C:\Users\jessa\.agents\plugins\marketplace.json`.
- Default target root: `WARDENCLYFFE_INTEL_ROOT`, falling back to the nested
  `wardenclyffe/` repo when running on the operator workstation.
- Local truth stream:
  `wardenclyffe/intelligence/raw-dumps/wardenclyffe.chat_dump.v1.jsonl`.
- Runtime spool:
  `wardenclyffe/.codex/memory-spool/events.jsonl`.
- Downstream bridge:
  `wardenclyffe/scripts/surreal-memory-extract-bridge.py`.
- Candidate worker:
  `wardenclyffe/scripts/ai-memory-candidate-worker.py`.

## Routing Rule

The plugin resolves workspace routing from the nearest `AGENTS.md`
`clyffy_touchpoint` frontmatter, then falls back to
`wardenclyffe/registry/context-mesh.yaml`.

Keep ADR 0031's distinction intact:

- `workspace_id` is row-level workspace scope.
- `project_key` narrows the memory/audit stream inside that workspace.
- SurrealDB `ns` names a data plane such as `clyffy`; it is not automatically
  the workspace slug.
- MCP namespace is the registry scope such as `mcp.workspace.<slug>...`.

## Promotion Rule

Captured events are raw, redacted, local-first records. The plugin may classify
candidate hints such as decisions, preferences, procedures, or research notes,
but it must not directly promote anything to active long-term memory. Promotion
continues through the existing `ai_bridge.memory_candidates` review path.

## Operations

Inspect or trigger a bounded sync:

```powershell
python C:\Users\jessa\.codex\plugins\wardenclyffe-intelligence-sync\scripts\sync_worker.py --quick --force
```

Dry-run the pipeline plan without writing:

```powershell
python C:\Users\jessa\.codex\plugins\wardenclyffe-intelligence-sync\scripts\pipeline_tick.py --dry-run
```

Validate the plugin source:

```powershell
python C:\Users\jessa\.codex\skills\.system\plugin-creator\scripts\validate_plugin.py C:\Users\jessa\.codex\plugins\wardenclyffe-intelligence-sync
```
