---
# CLYFFY TOUCHPOINT v2 — intelligence-layer routing (ADR 0033)
clyffy_touchpoint:
  version: 2
  workspace_id: clyffy.master                    # workspace slug per ADR 0031 §2; SurrealDB ns value
  workspace_uuid: null                            # backfill when assigned in federation_workspace
  project_key: clyffy-master-orchestrator
  persona: clyffy-operator
  kind: subsystem
  owner: Master-Clyffy/AGENTS.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml

  surreal:
    plane_a:                                      # federated intelligence plane (ADR 0025 §1)
      url: http://10.0.0.104:8000
      ns: clyffy
      db: ai_memory
    plane_a_star:                                 # federated operations plane (ADR 0025 §1)
      url: http://10.0.0.104:8000
      ns: wardenclyffe
      db: ai_memory

  audit:
    event_prefix: clyffy-master
    enabled: true

  scopes:
    - clyffy:operate
    - clyffy.federation:read
    - clyffy.federation:write

  capabilities:                                   # MCP routing per ADR 0032
    mcp_gateway:
      url: https://master.clyffy.ai/mcp           # planned per docs/CLYFFY_MCP_ORCHESTRATOR.md
      protocol_version: "2025-11-25"
      auth: oauth2.1+rfc9728                      # per ADR 0030 §5
      status: planned
    federation_peers:
      - wardenclyffe.infra
      - effing.personal-site
      - clyffy.bundle.*

  observability:                                  # OTel posture per ADR 0030 §3
    semconv_version: "1.40.0"
    trace_context_via_meta: true

  intel_hook:
    capture_chats: true
---

# Master Clyffy — Agent Context (canonical)

This is the root agent context for **Master Clyffy** (a.k.a. MasterClyffy /
`clyffy-master` / planned LXC `120` at `master.clyffy.ai`). It is the source
of truth for this project's structure, boundaries, and conventions.

> **Scaffold status:** initial v0.1 authored 2026-05-29 during Sub-batch C
> of the WardenClyffe-latest absorption (HF capability anchored under
> Clyffy first per operator direction). Full 10x foresight skeleton —
> including UI conventions, federation gateway shape, tenancy model, and
> connector/plugin/specialist catalog SSOT — lands per Task #5 of the
> absorption plan.

Read this completely before doing anything else.

---

## What this is

Master Clyffy is the **Go-based orchestrator** that normalizes the
WardenClyffe platform into a turnkey managed service. It is the
federation hub, control plane, and customer-facing UI layer:

| Concern | Master Clyffy owns |
|---|---|
| Operator + tenant UI | Modern OKLCH + REM + fluid-clamp components, dynamic theming, embedded chat widget |
| Federation gateway | L1 MCP gateway routing customer-site requests → per-project Clyffy |
| Tenant provisioning | workspace_uuid, SurrealDB ns, secrets envelope, audit boundary |
| Catalog SSOT (when extended) | Connectors / Plugins / Specialists three-tier taxonomy |
| Intelligence layer tiers | Standard (SurrealDB + Qdrant) / Premium (+encoder + embedder) |
| Architectural design + ADRs | All Master Clyffy ADRs, specs, and strategic docs live here |

Master Clyffy sits **above** the WardenClyffe engine in the architecture.
WardenClyffe is the body (Go monolith + Python agents + the actual
infrastructure-management runtime). Master Clyffy is the brain + face +
control plane that lets WardenClyffe ship as a productized managed service.

---

## Sibling repos under `W:\projects\`

| Sibling | Role | Status |
|---|---|---|
| `Master-Clyffy/` (this repo) | Orchestrator + UI + federation hub + architectural home | v0.1 scaffold, building out |
| `wardenclyffe/` | WardenClyffe engine (Go monolith, Python agents, catalog) | Active, shipping (`warden v0.24-beta`) — sibling submodule |
| `WardenClyffe-latest/` | Pending methodical absorption into the two above | Absorption in flight; eventually renamed to `WardenClyffeScale/` per Decision F |
| `WardenClyffeScale/` (future) | Rust MariaDB replication shared-component | Rename target after absorption per Decision F |

Master Clyffy and WardenClyffe are **siblings**, not nested. Master Clyffy
references WardenClyffe by path (`../wardenclyffe/...`) when consuming
catalog items, MCP registries, or templates.

---

## Read next, in order, before writing code

All paths are repo-relative from `Master-Clyffy/`.

1. `docs/CLYFFY_MCP_ORCHESTRATOR.md` — orchestrator role + federation contract
2. `docs/CLYFFY_DYNAMIC_UI_SPEC.md` — UI architecture (OKLCH / REM / fluid clamp)
3. `docs/CLYFFY_DYNAMIC_UI_POAM.md` — UI delivery milestones
4. `docs/CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md` — gateway readiness state
5. `docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md` — namespace + naming decisions
6. `docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md` — Clyffe Code managed-workspace product spec
7. `docs/ai/HUGGINGFACE_WORKSPACE_TOUCHPOINT.md` — HF integration touchpoint
8. `../wardenclyffe/AGENTS.md` — sibling engine's canonical context
9. `../wardenclyffe/registry/context-mesh.yaml` — MCP registry (consumed; not authored here)

Do not relitigate locked ADRs in the sibling `wardenclyffe/docs/decisions/`
tree. Master Clyffy authors its own ADRs when a decision is Master-Clyffy-
specific (UI architecture, federation gateway shape, tenant model);
otherwise it consumes the WardenClyffe ADRs.

---

## Boundary rules

- **Master Clyffy = brain + UI + federation gateway.** WardenClyffe = body.
- **Catalog SSOT lives in WardenClyffe** (at `../wardenclyffe/wardenclyffe-catalog/`),
  not duplicated here. Master Clyffy may extend the catalog taxonomy
  (connectors / plugins / specialists) but the source-of-truth files
  stay in the engine repo.
- **MCP context-mesh registry lives in WardenClyffe** (at
  `../wardenclyffe/registry/context-mesh.yaml`). Master Clyffy reads it,
  doesn't fork it.
- **Touchpoints in this repo cascade from this AGENTS.md.** Subdirectories
  add their own AGENTS.md only when their concerns override the root.
- **No secrets, ever.** Never commit token values, passwords, cookies,
  private keys, or live API responses. Use `.env.example` files and
  variable names only.
- **Repo-relative paths in committed text.** Use `docs/...` not
  `W:\projects\Master-Clyffy\docs\...` or `/workspace/warden-storage/...`.

---

## Stack constraints (non-negotiable)

- **Go** for orchestrator, federation gateway, server-side code, and any
  binary that ships as part of Master Clyffy.
- **No PHP, no Rust** in this repo. Rust lives only in the sibling
  `WardenClyffeScale/` for the local-deployment edition per Decision F.
  Per direction: "Rust for local, Go for web."
- **Modern CSS for UI**: OKLCH color, REM typography, fluid `clamp()` sizing,
  CSS custom properties (variables) over hardcoded values. No utility-class
  framework lock-in.
- **TypeScript or modern JS** for UI when needed. No legacy build chains.

---

## Touchpoint convention

Every subdirectory that has its own agent rules ships an `AGENTS.md` with
v2 `clyffy_touchpoint:` frontmatter per ADR 0033 §1. Inheritance cascades
from this root file. See `docs/ai/HUGGINGFACE_WORKSPACE_TOUCHPOINT.md` for
a worked example of a subtree touchpoint.

v1 `wardenclyffe_touchpoint:` frontmatter is deprecated; the v1 → v2
migration window is documented in ADR 0033 §10 and runs as Task #10
(Phase 8) across the entire estate.

---

## Things you must not do

- Don't duplicate catalog or registry truth that lives in `wardenclyffe/`.
- Don't paste, commit, or invent secrets.
- Don't put hardcoded local paths (`D:\…`, `/home/<user>/…`,
  `W:\projects\…`) in committed text. Repo-relative only.
- Don't write Rust or PHP here. If you find yourself reaching for either,
  the work belongs in a sibling repo, not this one.
- Don't author parallel ADRs that contradict locked WardenClyffe decisions.
- Don't relitigate the operator's vision in code — Markdown touchpoints
  describe routing, not stored knowledge.

---

## When this file is wrong

Edit it. This is the canonical Master Clyffy context. Update the v2
frontmatter before any structural change to workspace_id, project_key,
persona, or federation peers. Run the touchpoint validator
(`../wardenclyffe/scripts/foundation/validate-touchpoints.py`) after edits.
