---
clyffy_touchpoint:
  version: 2
  workspace_id: clyffy.master
  project_key: cortex-control-layer-spec
  persona: clyffy-operator
  kind: doc
  owner: docs/specs/CORTEX_CONTROL_LAYER_SPEC.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDENCLYFFE_NAMING_CONVENTIONS.md
    - docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md
    - docs/ai/MCP_MESH_TOUCHPOINTS.md
    - docs/ai/parking-lot/decisions/modules-warden-interfaces-mcp.md
  sync:
    qdrant: true
    surreal: true
---

# Cortex Control Layer Spec (build spec)

Gets the code developed so the operator can: open **clyffy.ai** and the
**admin boundary**, see the **control layer** and the **intelligence layer**
each listing its **active plugins** (MCP servers), and **add Cortex to Claude
Desktop / Codex / Claude Code** with a copyable snippet. Production-only
baseline; all names follow the naming-conventions law.

## Acceptance criteria (the operator can verify)

1. Claude Desktop → Settings → add the Cortex server → `cortex.*` tools,
   resources, and prompts appear. Same for Codex (`config.toml`) and Claude
   Code (plugin install).
2. `/admin` → **Control layer** panel: every registry plugin (id, class,
   status, transport, live reachability) + a **Connect** action exposing the
   Claude Desktop / Codex / Claude Code snippets (no secrets, env-ref only).
3. `/admin` → **Intelligence layer** panel: touchpoint inventory (v1/v2 counts,
   sync flags, heavy warnings), Qdrant/SurrealDB reachability, decision count.
4. `/clyffy` (→ clyffy.ai) renders the orchestrator home from `/api/clyffy/*`.

## Component 1 — `services/cortex-mcp` (Go, the plugin itself)

MCP server, **stdio transport first** (works in all three tools immediately);
Streamable HTTP + OAuth 2.1 is phase 2 (ADR 0030 bar gates `formal-mcp`).

```text
services/cortex-mcp/
  cmd/cortex-mcp/main.go        wiring: config -> catalog -> serve (stdio)
  internal/platform/config.go   repo root path, registry path, env
  internal/protocol/protocol.go MCP JSON-RPC wire (initialize, list, call)
  internal/catalog/catalog.go   registers tools/resources/prompts below
```

Tools (`<domain>.<verb>_<object>`; every input a JSON-Schema `inputSchema`):

| Tool | Does |
|---|---|
| `cortex.commit_decision` | wraps `parking-lot-capture.py` — {type, name, decision, boundary, focus} → decision touchpoint (the durable memory) |
| `cortex.validate_touchpoints` | runs validator `--json`, returns summary |
| `cortex.search_decisions` | query over `docs/ai/parking-lot/decisions/` (Qdrant later) |
| `cortex.list_plugins` | parsed `context-mesh.yaml` server/gateway summary |

Resources: `cortex://registry/context-mesh` · `cortex://schemas/clyffy-touchpoint.v2`
· `cortex://decisions/{project_key}` · `cortex://docs/naming` · `cortex://docs/structure`.
Prompts (the strict processes, portable to Codex/Desktop): `planning-workflow`,
`boundary-guard-checklist`, `build-spec`.

## Component 2 — warden-api `mesh` context endpoints

`mesh` is the canonical context owning registry view + touchpoint sync health.
Flat shape: `internal/mesh/{mesh.go,handler.go}`; store = `mesh.NewStore`.

| Method | Path | Function |
|---|---|---|
| GET | `/api/warden/mesh/plugins` | `ListPlugins` — registry entries + live probe state |
| GET | `/api/warden/mesh/plugins/{id}/connect` | `GetConnectDescriptors` — Claude Desktop JSON, Codex TOML, Claude Code command; env-refs only, never values |
| GET | `/api/warden/mesh/intelligence` | `GetIntelligenceInventory` — validator summary + Qdrant/Surreal probes (reuses `clyffy.IntelligenceStatus`) |

## Component 3 — frontend (admin + clyffy domains)

`admin` is the shell domain; its views may compose other domains' services
(explicit exception to strict mirroring — admin owns no Go context).

```text
src/domains/warden/mesh/mesh.svc.ts      typed client onto /api/warden/mesh/*
src/domains/admin/control/views/ControlLayerView.tsx       plugins table + Connect dialog (copy snippet)
src/domains/admin/intelligence/views/IntelligenceLayerView.tsx  inventory stats + probe badges
src/domains/clyffy/clyffy.svc.ts         typed client onto /api/clyffy/*
src/domains/clyffy/views/ClyffyView.tsx  orchestrator home (replace scaffold)
src/routes/admin.control.tsx · admin.intelligence.tsx      thin routes
```

All UI from `lib/design` (ColdLight) only; views are dumb; data via `.svc.ts`.

## Component 4 — `plugins/cortex` (Claude Code bundle) + registry

```text
plugins/cortex/
  .claude-plugin/plugin.json    manifest
  .mcp.json                     cortex-mcp server entry (auto-wired on install)
  skills/planning/SKILL.md      thin wrapper -> MCP prompt planning-workflow
  README.md                     Codex config.toml + Claude Desktop snippets
```

Registry: add `mcp.workspace.clyffy-master.cortex` (class `leaf`, status
`scaffold` → `formal-mcp` only after ADR 0030 bar) to `context-mesh.yaml`;
gateway upstreams gain it.

Claude Desktop / Codex connect (stdio over SSH until Streamable HTTP lands):

```json
{ "cortex": { "command": "ssh", "args": ["warden-devstation", "/usr/local/bin/cortex-mcp"] } }
```

## Build order (one PR per component)

1. **PR1** `services/cortex-mcp` stdio server + 4 tools + resources + prompts.
   Verify: tools visible in Claude Code on the devstation.
2. **PR2** warden-api `mesh` endpoints. Verify: curl returns plugins/inventory.
3. **PR3** admin views + clyffy home. Verify: acceptance 2–4.
4. **PR4** `plugins/cortex` bundle + registry entry + connect snippets.
   Verify: acceptance 1 from a clean machine.

## Non-goals (this spec)

OAuth 2.1 / Streamable HTTP / server card (phase 2); Qdrant-backed search
(file-grep first); any infra-mutating tool (read+capture only); the W-drive
`mcp.global.projects` (separate, depends on E→W clone per the W rule).
