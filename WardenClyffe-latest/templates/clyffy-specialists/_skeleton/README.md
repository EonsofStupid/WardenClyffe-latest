# Clyffy Specialist Skeleton — Product Layer

Copy this directory to `../<namespace>/` to start a new specialist.

This is the **product layer** template — what every Clyffy specialist
has in common regardless of namespace. The **MCP-wire layer** template
(the actual tool/resource/policy contracts a real MCP server emits)
lives separately at
`wardenclyffe/.agents/templates/mcp/l2-leaf-server/` (renamed from `mcp-mesh-server/` per alignment runbook Phase 2). Both layers are real;
this README is the seam between them.

## Files in this skeleton

| File | What it owns |
|---|---|
| `manifest.yaml` | Specialist identity, version, bucket, default capabilities, wrapper targets, dataset targets, handoff edges |
| `ROLE.md` | Layman-readable role description that surfaces in Clyffy.ai UI cards and Warden Go mesh tab tooltips |
| `touchpoint.md` | The v2 `clyffy_touchpoint` frontmatter making this specialist discoverable via the existing touchpoint system |
| `seed.template.yaml` | The schema for project-attuned values (filled by the attunement pass against project artifacts) |
| `policy.yaml` | Product-layer policy — bucket boundaries, capability gates, tenant scope, audit |
| `adapter-target.yaml` | Per-client wrapper hints feeding the MCP renderer in `docs/MCP_CLIENT_NORMALIZATION_SPEC.md` |

## What is intentionally NOT here

- `tools.yaml` — the MCP wire tool contract. Lives in the
  `mcp/l2-leaf-server/` template instance for this namespace.
- `resources.yaml`, `prompts.yaml`, `evals.yaml`, `observability.yaml`,
  `readiness.yaml`, `server-contract.yaml` — same. They are the wire
  contract; this template carries product metadata that quotes them.
- `trace-schema.json` — lives in the deeper specialist-capability-pack
  template if the specialist is being trained from traces.
- The actual specialist implementation. Code lives wherever the
  implementation language dictates (`wardenclyffe/agent/<slug>/` for
  Python services; `wardenclyffe/specialists/<slug>/` for capability
  packs; `agent/warden-mcp/` for the future Rust formal target).

## How to fill the skeleton

1. Rename the directory: `cp -r _skeleton/ ../<namespace>/`.
2. Open `manifest.yaml`. Replace every `<...>` placeholder with the
   namespace's values from
   [`docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md`](../../../docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md)
   Decision 3 table.
3. Open `touchpoint.md`. Same replacement pass, plus fill the per-section
   bodies.
4. Open `ROLE.md`. Write three or four short paragraphs. No marketing.
5. Open `seed.template.yaml`. Do **not** fill `project_context:` or
   `indexing:` — those come from the attunement pass. Fill
   `visible_capabilities:` / `hidden_capabilities:` /
   `capability_overrides:` if there is a known opinion for the default
   project.
6. Open `policy.yaml`. Set the `caller_allowlist:` for the bucket
   correctly (Clyffy.master leaves usually let other Clyffy.master leaves
   call them; WardenClyffe leaves usually do not).
7. Open `adapter-target.yaml`. Set `bridge:` to `none` if the specialist
   speaks MCP wire natively; `http-tools` if it speaks REST `/tools/{name}`.
8. Decide where the MCP-wire instance lives and link it from
   `manifest.yaml` `canonical_files.mcp_wire`.

## How the two layers compose

```text
project artifacts (AGENTS.md, clyffy_touchpoint frontmatter, package manifests)
  -> attunement pass
  -> RRD projections (per docs/ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md)
  -> templates/clyffy-specialists/<namespace>/seed.yaml (project-attuned)
                                                |
                                                v
templates/clyffy-specialists/<namespace>/manifest.yaml  <- product identity
templates/clyffy-specialists/<namespace>/touchpoint.md  <- discovery surface
                                                |
                                                v
wardenclyffe/.agents/templates/mcp/l2-leaf-server/<...> instance for this namespace
                                                |
                                                v
Actual MCP server (Python today; Rust for formal targets) speaking MCP wire
                                                |
                                                v
mcp.workspace.clyffy-master-gateway routes calls (per ADR 0032 §5)
                                                |
                                                v
Agent client (Claude Desktop / Code / Cursor / Codex / Gemini) calls tools
```

The renderer in `docs/MCP_CLIENT_NORMALIZATION_SPEC.md` traverses this
chain when emitting per-client configs. Every link is registry-resolved.
