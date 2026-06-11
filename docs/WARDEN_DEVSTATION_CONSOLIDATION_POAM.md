---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: devstation-consolidation
  persona: clyffy-operator
  kind: poam
  owner: docs/WARDEN_DEVSTATION_CONSOLIDATION_POAM.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - docs/WARDEN_DEVSTATION_TURNKEY_WARDENNET_PLAN.md
    - docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md
    - docs/CLYFFY_MCP_ORCHESTRATOR.md
    - docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md
    - docs/ai/MCP_MESH_TOUCHPOINTS.md
    - docs/WARDENCLYFFE_NAMING_CONVENTIONS.md
    - docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md
  sync:
    qdrant: false
    surreal: false
---

# Warden Devstation Consolidation POA&M

A Plan of Action & Milestones for consolidating everything that touches
**Devstation** (the managed dev VM), **Clyffe Code** (its future customer
product), and the **intelligence + MCP layers** Devstation depends on. The
operator's fear is scattered notes / MCP / intelligence pieces; this doc is the
methodical, evidence-first work-list that replaces guesswork.

This is a **working audit tracker**, not a routing manifest — `sync` is `false`
on purpose. Confirmed findings get promoted into proper v2 decision touchpoints
under `docs/ai/parking-lot/decisions/` (which DO sync), never hand-written into
the DB.

## Method (operator-locked)

- Evidence first: read the file before classifying it. No vibe-coding.
- Nothing is built in Pass 4+ without operator approval of names/vars/functions.
- Report to the operator **after each pass** (cadence locked 2026-06-10).
- Authorities: naming = `docs/WARDENCLYFFE_NAMING_CONVENTIONS.md`; structure =
  `docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md` + `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md`.

## Status legend

- ☐ pending — not yet read this pass
- ◐ read — read, classification noted
- ✅ reconciled — cross-checked against code/other docs, verdict locked
- ⚠ finding — contradiction, gap, stale ref, or duplicate flagged

## Ground-truth notes (from the 2026-06-10 file-tree dump)

- Repo is git-tracked, **359 files**, no submodules. Single source repo.
- `/workspace/devpulse/` is **EMPTY** — the "real ColdLight" source is not
  present locally; it must be ingested from elsewhere before ColdLight adoption.
- `WardenClyffe-latest/WardenClyffe-module1/` and `module2/` are **EMPTY**
  placeholder dirs (naming conventions forbid durable code under module1/module2).
- Nested `wardenclyffe/` holds only **2 files**: `registry/context-mesh.yaml`
  (the live MCP registry) and `docs/cheatsheets/surrealdb-v3.0.5-master.md`.
- ⚠ **`AGENTS.md` cites authorities that do not exist in the tree:**
  `wardenclyffe/docs/runbooks/mcp-2026-alignment-checkpoint.md`,
  `wardenclyffe/docs/specs/14-mcp-federation-and-workspace.md`, and ADRs
  `0030`, `0031`, `0032`, `0033`. These define the MCP capability bar, the
  federation model, and the v2 touchpoint shape — the entire MCP/intelligence
  contract is **referenced but absent**. (Confirm + resolve in Pass 2.)
- `/workspace/warden-storage/` (the W-drive SMB mount) is **empty — 0 files**,
  bare dir skeleton (`mirrors memory archives registry artifacts projects`), and
  **not actually mounted here** (`df` shows local `/dev/sda1`, not
  `//10.0.0.117/warden-storage`). The brokered SMB auto-mount is not active in
  this environment.
- ⚠ **`clyffy-go` is absent from this machine.** Lives at `E:\dev\clyffy-go`.
  Referenced as `clyffy-go/sdk/platform/store/surreal/live/client.go`,
  `orchestrator/internal/mirror/`, `sdk/secrets/`, `docs/INTELLIGENCE-LAYER.md`.
  **The real Go intelligence-layer + secrets-sync implementation is here, not in
  this repo** — a primary source of the felt scatter.

## Scope addition — W-drive General Projects MCP (operator, 2026-06-10)

**Operator goal:** the W-drive (the primary drive) hosts **one general MCP** that
can discuss the active projects: **devpulse**, **wardenclyffe**, **clyffy-go**.

Evidence / blockers found:
- The 3 projects live in 3 homes; **none is on W yet** (W is bare; clyffy-go &
  devpulse are not on this box). The "Clyffy-dean E→W move" is unstarted.
- No project-context MCP exists. `CLYFFY_MCP_ORCHESTRATOR.md` defines an *infra*
  gateway (`mcp.workspace.clyffy-master-gateway` + leaves), not a project MCP.
- ⚠ Tension with "W as primary drive": `modules/warden/bounded-contexts/
  storage/README.md` plans to *retire SMB W-drive → per-tenant volumes*.

Decisions (locked 2026-06-10 — captured to parking-lot, projecting to Surreal/Qdrant):
- **D1 — source = Move to W first.** All 3 projects moved/mirrored onto W via
  Clyffy-dean E→W; MCP reads only from W. → dependency captured in
  `docs/ai/parking-lot/decisions/modules-warden-bounded-contexts-storage.md`.
- **D2 — name = `mcp.global.projects`** (operator-approved).
- **D3 — topology = L2 leaf** under `mcp.workspace.clyffy-master-gateway`.
- **D4 — scope = read-only context/retrieval + repo/file read on W**, gated by a
  secrets-exclusion guard (no keys/secrets surfaced).
- Capability captured in
  `docs/ai/parking-lot/decisions/modules-warden-interfaces-mcp.md`.

Newly-implied open items (not yet locked):
- The secrets-exclusion guard for the file-read scope needs its own spec/design.
- Reconcile "W as primary drive" with the storage README's "retire SMB →
  per-tenant volumes" plan before the E→W move is treated as durable.

## Scope addition — Customer Devstation connections & launch UI (operator, 2026-06-11)

**Operator requirement:** logged-in customer (Clyffe, OIDC via auth.rrflow.ai)
gets a clean Devstation surface that lets them **(a) connect accounts** — GitHub,
Anthropic Claude subscription — and **(b) launch each AI tool** — Claude Code
(latest), Codex, Cursor, Gemini, Antigravity, DevForge VSCode (browser) — **via web
and/or local Remote-SSH** into their own devstation. Captured →
`docs/ai/parking-lot/decisions/modules-clyffe-bounded-contexts-code-workspaces.md`.

- Extends `CLYFFY_DYNAMIC_UI_SPEC.md` Workspace Detail (adds account-auth + full tool set).
- ⚠ Depends on **P1-A** (DevForge vs Clyffe Code) for tile/label naming.
- ⚠ Each connected token (GitHub/Anthropic/per-tool) must ride the **secrets-
  exclusion guard** — customer creds brokered, never leaked to other tenants or to
  an AI agent's context. Ties to the L4 capsule-safety research (Blackwell brief).
- React home: `apps/console/src/domains/clyffe/...`; Go: `clyffe-api internal/<ctx>`.

---

## Pass 1 — Source docs (read 2026-06-10 → tagged)

Legend in Findings: **v1/v2** = touchpoint shape; **AUTH** = treat as authority;
**STALE** = describes a state the tree no longer matches.

| # | File | Status | Findings |
|---|------|:------:|----------|
| 1 | `docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md` | ◐ | v1. AUTH for live VM (116, 8/16/160, code-server, SSH aliases). Names: warden-devstation-01, clyffe-code-workspace-template, Clyffe Code, Clyffe Connect. ⚠ v1 shape; migrate to v2. |
| 2 | `docs/WARDEN_DEVSTATION_TURNKEY_WARDENNET_PLAN.md` | ⚠ | v1. Strategic plan; Hades=operator, WardenNet=Headscale. **⚠ NAMING DRIFT: introduces "DevForge" as the product/template name** (`service.devforge-template`, role devstation) — collides with "Clyffe Code". Has unresolved confirms C1–C4. |
| 3 | `docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md` | ◐ | v2 (sync on). AUTH customer-product spec. Tiers Starter→GPU. Calls product "Clyffe Code", template "clean template". No "DevForge" — conflicts with #2. |
| 4 | `docs/WARDEN_CLYFFE_ARCHITECTURE.md` | ⚠ | **No touchpoint frontmatter** (invisible to mesh). AUTH for surface split, but "Data Layer Options" + "Auth Options" marked *under review* while #7/#11 treat Postgres+Authentik as locked → stale relative to later decisions. |
| 5 | `docs/CLYFFY_DYNAMIC_UI_SPEC.md` | ◐ | v1. Devstation shown as a workspace card; `/api/clyffy/*` read+action contract; security rules. Solid UI authority. |
| 6 | `docs/CLYFFY_DYNAMIC_UI_POAM.md` | ◐ | v1. Detailed milestone baseline (verified 2026-05-22). Good status source; reconcile "Done" claims vs code in Pass 3. |
| 7 | `docs/CLYFFY_MCP_ORCHESTRATOR.md` | ◐ | v2 (sync on). AUTH orchestrator/MCP contract + LXC foundation map + sync-worker design. ⚠ says LXC 110 Postgres (see version conflict). |
| 8 | `docs/RUNNING_THE_STACK.md` | ⚠ | **No frontmatter. STALE vs code:** names `internal/dbadmin` (tree has `internal/data`), `clyffe-api internal/portal` + `/api/clyffe/accounts|orders` (tree has `internal/account`), React `src/ds`/`src/views`/`src/styles` (tree has `src/lib/design`/`src/domains`). Misleads on the live layout. |
| 9 | `docs/MASTER_CLYFFY_ROLLOUT_PLAN.md` | ⚠ | v1. AUTH for master.clyffy.ai rollout. ⚠ `reads:` cite missing files `wardenclyffe/docs/infra-state.md`, `wardenclyffe/docs/decisions/0027-*.md`. LXC 110 = "Postgres 17". |
| 10 | `docs/superpowers/plans/2026-05-22-clyffe-code-local-editor.md` | ⚠ | No frontmatter. Tasks 1–3 ✅ (launchers, SSH aliases, ssh.clyffy.ai). ⚠ Task 5 plans `modules/warden/bounded-contexts/devstations/` — NOT a canonical context (devstation lives under `infrastructure/`); boundary risk. |
| 11 | `docs/specs/IDENTITY_TENANCY_SPEC.md` | ◐ | v2 (sync on). **The auth.rrflow.ai → roles → impersonation core.** subjects + subject_tenant.role (founder/super_admin) + RLS + create_customer. Verified PG 18.4. ⚠ "nothing exists yet"; no explicit impersonate fn (RLS via `app.current_tenant()` supports it). |
| 12 | `docs/specs/UI_COMPONENT_SYSTEM_SPEC.md` | ⚠ | v2 (sync on). ColdLight authority. ⚠ `lib/design` is a STAND-IN; real coldlight ships in **devpulse — which is EMPTY** (ground truth). HTML→component conversion is blocked at ingest step 0. |
| 13 | `docs/specs/WORKSTATION_BRIDGE_TEMPLATE.md` | ⚠ | v2 (sync on). W-drive bridge (WireGuard overlay, hades↔service↔volume, SSHFS-Win today). ⚠ `reads:` cite missing `docs/WARDENCLYFFE_REFERENCE_STACK.md`, `docs/ai/WARDENCLYFFE_OFFICIAL_BASELINE.md`. |
| 14 | `docs/WARDENCLYFFE_NAMING_CONVENTIONS.md` | ◐ | v1. AUTH naming: products, kebab dirs, UPPER_SNAKE docs, schemas, MCP grammar, **forbidden-drift rule** (directly indicts #2's DevForge). |
| 15 | `docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md` | ◐ | v2. AUTH structure: Go-drives/React-dumb, parallel domains, flat `<context>.go/service.go/handler.go`, canonical contexts. Code follows THIS. |
| 16 | `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md` | ⚠ | v1. AUTH for root scaffold + idempotent id grammar. **⚠ CONFLICTS with #15:** prescribes per-context `domain/application/infrastructure/interface/tests`; #15 (and the code) use a flat shape. Two structure authorities disagree. |
| 17 | `docs/WARDENCLYFFE_MODULE_MAP.md` | ⚠ | v1. AUTH module split. ⚠ STALE "repo reality": cites `src/`, `wardenclyffedisk/`, `wardenclyffenet/` (absent) and calls module1/2 "mistral.rs/Burn runtimes" (they're EMPTY dirs). |

### Pass 1 verdict — the real findings (not just scatter)

1. **✅ P1-A — RESOLVED 2026-06-11 (layered naming).** `Clyffe Code` = product /
   service; `Devstation` = the VM substrate; `DevForge` = the hosted browser-VSCode
   IDE tile only. Captured → `docs/ai/parking-lot/decisions/docs-wardenclyffe-naming-conventions-md.md`.
   **Follow-up:** demote `DevForge`-as-product in `WARDEN_DEVSTATION_TURNKEY_WARDENNET_PLAN.md`
   (`service.devforge-template`) and record the layering in `WARDENCLYFFE_NAMING_CONVENTIONS.md`.
2. **⚠ P1-B — Two conflicting structure authorities.** #16 (v1, `domain/
   application/...`) vs #15 (v2, flat). Code follows #15. One must be demoted.
3. **⚠ P1-C — `RUNNING_THE_STACK.md` is stale vs code** (`dbadmin`/`portal`/old
   React tree). High-traffic doc; will mislead. Rewrite after Pass 3.
4. **⚠ P1-D — Postgres version is ambiguous.** "17" (LXC 110, #7/#9) vs "18.4
   verified" (devstation-local, #11) vs "18 local" (#8). Confirm 1 vs 2 instances.
5. **P1-E — Auth/roles/impersonation = clear spec, zero build (#11).** This is
   goal #1 (auth.rrflow.ai → founder/super_admin → impersonate). No impersonate
   fn yet; RLS context-setting is the seam.
6. **⚠ P1-F — ColdLight blocked at step 0:** real source `devpulse` is empty.
7. **⚠ P1-G — More missing referenced files** (add to Pass 2 #30):
   `wardenclyffe/docs/infra-state.md`, `…/decisions/0027-*.md`,
   `docs/WARDENCLYFFE_REFERENCE_STACK.md`, `docs/ai/WARDENCLYFFE_OFFICIAL_BASELINE.md`.
8. **⚠ P1-H — Boundary risk:** editor plan Task 5 would add a non-canonical
   `bounded-contexts/devstations/` context.
9. **P1-J — Mixed touchpoint versions** across the core Devstation docs (#1,#2
   v1; #3 v2). Part of the repo-wide 56 v1 / 34 v2 split.

## Pass 2 — Intelligence + MCP layer (read 2026-06-11)

| # | File | Status | Findings |
|---|------|:------:|----------|
| 18 | `docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md` | ◐ | v2. AUTH contract: Markdown=routing only; Postgres=truth, Qdrant=retrieval, SurrealDB=graph, Warden tasks=episodic. Coherent. |
| 19 | `docs/ai/INTELLIGENCE_TOUCHPOINTS.md` | ◐ | v2. Memory boundaries + capture rules. Consistent with #18. Cites absent `wardenclyffe/intelligence/contracts/`, `REGISTRY.md`. |
| 20 | `docs/ai/MCP_MESH_TOUCHPOINTS.md` | ◐ | v2. AUTH MCP shape: ADR 0030 baseline, federation L0/L1/L2, candidate domains, `rmcp` 0.16+. Cites absent ADRs + spec 09/14 + alignment runbook. |
| 21 | `docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md` | ◐ | v2. AUTH projection plan + sync-worker responsibilities. Cites absent `wardenclyffe/surreal/schema/001,003.surql`. |
| 22 | `docs/ai/TOUCHPOINT_SYNC_PATTERN.md` | ◐ | v2. AUTH sync pipeline + payload shapes. Consistent. Confirms sync worker is planned, validator is read-only. |
| 23 | `docs/ai/TOUCHPOINT_TEMPLATE.md` | ☐ | (not yet read — low risk, template only) |
| 24 | `schemas/intelligence/clyffy-touchpoint.v2.schema.json` | ◐ | Real JSON-Schema for v2 frontmatter; matches the capture script output. Solid. |
| 25 | `schemas/intelligence/surreal-touchpoint-projection.v2.surql` | ☐ | (referenced by #21; read in Pass 3 with code) |
| 26 | `wardenclyffe/registry/context-mesh.yaml` | ◐ | v3 registry, REAL + rich. Workspaces/estates/projects, clyffy-master orchestrator, 8 servers. See verdict P2-* below. |
| 27 | `docs/ai/parking-lot/decisions/*.md` (now 11) | ◐ | Inventoried; 2 added this session (mcp, storage). Decisions are the live v2-sync surface. |
| 28 | `docs/ai/parking-lot/filetree.manifest.json` | ☐ | (boundary-guard snapshot; read in Pass 3) |
| 29 | `scripts/foundation/*.py` | ◐ | validate + capture exercised this session (work). hooks/tree not yet read. |
| 30 | ⚠ absent cited authorities | ⚠ | Confirmed absent: ADRs 0030/0031/0032/0033/0025/0028/0027, spec 09/14, `wardenclyffe/docs/runbooks/mcp-2026-alignment-checkpoint.md`, `wardenclyffe/surreal/schema/*`, `wardenclyffe/intelligence/contracts/`, `agent/clyffy-dean`, `agent/warden-mcp`, `agent/mcp-cluster`, `specialists/clyffy-authentik-specialist`. |

### Pass 2 verdict — the real findings

1. **P2-A — Intelligence-layer *design* is coherent & v2-consistent** (#18–22 + schema #24
   agree). Not a contradiction problem. The problem is implementation absence.
2. **⚠ P2-B — The whole referenced implementation lives off-box.** The registry +
   intelligence docs point to a `wardenclyffe/` Go tree (surreal schemas, `agent/*`,
   `specialists/*`, intelligence contracts, the sync worker) that is **not in this repo**
   — it's the clyffy-go / Go-Warden material on E:. Confirms P2/Devstation intelligence =
   spec-rich, implementation-absent-here.
3. **P2-C — MCP mesh reality:** exactly **one** formal MCP today
   (`mcp.workspace.clyffy-master.authentik`); everything else planned/mcp-shaped/scaffold.
   The `clyffy-master-gateway` (our chosen L1 parent) is **planned, not deployed**.
4. **⚠ P2-D — `mcp.global.projects` is not yet in the registry**, and "projects" is
   **overloaded**: a SurrealDB `continuity_plane: projects` ns, the `mcp.project.*` scope
   tier, the `namespaces.projects` registry block, AND now our new MCP domain. Naming-
   clarity risk vs. the forbidden-drift rule — resolve the name's domain meaning before
   registering it.
5. **P2-E — `clyffy-dean` = the Proxmox MCP agent** (`slug: clyffy-dean` for
   `mcp.global.proxmox`, impl `agent/clyffy-dean` → target `agent/warden-mcp`). So
   "Clyffy-dean E→W" is this ops agent doing the move, not a separate tool.
6. **P2-F — `mcp.global.rykv`** exists: an internal zero-ms hot-path memory/cache/context
   MCP (external-existing). Relevant to the intelligence/memory layer; not yet reconciled
   with Qdrant/SurrealDB roles.

## Pass 3 — Code (read 2026-06-11 → real / stub)

| # | File | Status | Findings |
|---|------|:------:|----------|
| 31 | warden-api `cmd/main.go` + `platform/{config,db,http}.go` | ◐ | Clean wiring: config→pool→stores→handlers. Follows v2 STRUCTURE_STANDARD exactly. ⚠ CORS `*` (dev). |
| 32 | `internal/identity/{identity.go,handler.go}` | ◐ | REAL bootstrap: env credential, constant-time compare, in-memory bearer tokens, 12h TTL. ⚠ Role hardcoded `"operator"` — no founder/super_admin, no OIDC, no impersonation (confirms P1-E from code). |
| 33 | `internal/fleet/{fleet.go,handler.go}` | ◐ | REAL read store + `CreateRequested` (hardcodes kind='devstation'). GET list/detail. No update/delete. |
| 34 | `internal/automation/{automation.go,handler.go}` | ◐ | REAL durable capture: action_request + requested resource + audit link. Proxmox clone = phase 2 (stub). |
| 35 | `internal/clyffy/{clyffy.go,handler.go}` | ◐ | REAL operator-facing orchestrator read surface: `/api/clyffy/{home,services,platforms,intelligence,plugins,connectors}`. Probes intel-plane reachability + credential-gating. **Good secret hygiene: `credential_ref` = Infisical path, never value.** This is the seed of the "Clyffy control layer." Read-only; no specialist/agent mgmt, no MCP control. |
| 36 | `internal/data/{data.go,handler.go}` | ◐ | REAL Supabase-style browser: 8-schema allow-list, read-only tx + 5s timeout, sanitized idents. Solid + safe. |
| 37 | `internal/audit/audit.go` | ◐ | REAL append-only events sink. Minimal. |
| 38 | clyffe-api `cmd/main.go` + `account/{account.go,handler.go}` + `platform/*` | ◐ | REAL customer plane: `/api/clyffe/{home,accounts,orders}`, reads ONLY tenants + orders; boundary enforced in code+comment. No infra authority. |
| 39 | console: `app/App.tsx`, `main.tsx`, `lib/api.ts`, `lib/design/`, identity, fleet, mesh, clyffe/storage | ◐ | Operator console (tabs: overview/foundation/workspaces/data/order), hash-routing, localStorage token. Dumb-view principle mostly honored. See verdict. |

### Pass 3 verdict — the real findings

1. **P3-A — Code follows v2 STRUCTURE_STANDARD, not v1 hypermodular.** Reinforces
   **P1-B**: the flat `<context>.go/service.go/handler.go` + `domains/<d>/<ctx>/` shape is
   the lived reality; demote the v1 `domain/application/...` doc.
2. **⚠ P3-B — React service-client pattern is inconsistent.** identity + clyffe/storage
   use per-context `<context>.svc.ts` (per the standard); but warden fleet/data/mesh/clyffy
   call a **shared monolithic `lib/api.ts`** instead. Pick one (standard says per-context svc).
3. **⚠ P3-C — Secret-leak smell in `StorageView.tsx`:** `window.alert(... key: ${g.access_key} ...)`
   surfaces an access key in the browser. Conflicts with the no-leak / secrets-exclusion
   posture (same guard the customer Connect&Launch UI needs). Fix before that UI is built.
4. **P3-D — No customer (Clyffe) app shell.** `App.tsx` is operator-only; the clyffe/storage
   domain exists but isn't mounted, and the just-specced **Connect & Launch UI is greenfield**.
5. **P3-E — Coldlight not enforced.** Views use inline styles, ad-hoc classNames, and raw
   `var(--wc-*)` — the strict token contract from `UI_COMPONENT_SYSTEM_SPEC.md` isn't in
   place (expected: `lib/design` is the stand-in, pending devpulse ingest).
6. **P3-F — confirms P1-C:** code is `internal/data` + `internal/account`; `RUNNING_THE_STACK.md`
   says `dbadmin` + `portal`. Doc is stale.
7. **P3-G — What's REAL vs STUB:** REAL = identity(bootstrap), fleet(read), automation(capture),
   clyffy(read+probe), data(browser), audit, clyffe account(read), storage(customer). STUB/
   greenfield = provision execution, OIDC/roles/impersonation, customer Clyffe shell + Connect&Launch
   UI, MCP servers, the touchpoint sync worker.

---

## Pass 4+ — NOT STARTED (requires operator direction + naming approval)

1. Reconcile Pass 1 ↔ Pass 3 into a contradiction/gap matrix.
2. Single canonical Devstation spec (one source of truth; collapse strays).
3. Intelligence/MCP wiring plan (sync worker, ADR recovery, v1→v2 migration).
4. Devstation-as-safe-VM design (API-key isolation) — brainstorm, then spec.

No names, vars, functions, files, or boundaries are to be created in Pass 4+
without explicit operator approval.

## Naming approval log

| Date | Item | Approved name | By |
|------|------|---------------|-----|
| 2026-06-10 | this tracking doc | `WARDEN_DEVSTATION_CONSOLIDATION_POAM.md` | operator |
| 2026-06-10 | W-drive general projects MCP | `mcp.global.projects` | operator |
| 2026-06-11 | turnkey VM+capsule research brief | `WARDEN_TURNKEY_VM_CAPSULE_RESEARCH_2026_06.md` | operator |
| 2026-06-11 | product naming layering (P1-A) | `Clyffe Code` (product) / `Devstation` (VM) / `DevForge` (web IDE) | operator |
| 2026-06-11 | Clyffy family separation | `Clyffy`=persona / `Clyffy-Go`=template+orchestrator / `Clyffy-Dean`=Proxmox ops agent | operator |
