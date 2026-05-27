---
wardenclyffe_touchpoint:
  version: 1
  kind: clyffy-dynamic-ui-poam
  namespace: wardenclyffe.clyffy.dynamic-ui.poam
  owner: docs/CLYFFY_DYNAMIC_UI_POAM.md
  module: module-02-clyffe
  reads:
    - docs/CLYFFY_DYNAMIC_UI_SPEC.md
    - docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md
    - docs/WARDEN_ESTABLISHMENT_POAM.md
    - docs/MASTER_CLYFFY_ROLLOUT_PLAN.md
    - docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md
    - docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md
    - docs/superpowers/plans/2026-05-22-clyffe-code-local-editor.md
---

# Clyffy Dynamic UI POA&M

This POA&M tracks the milestones and sprint sequence for making Clyffy populate
dynamically and for turning Clyffe Code into a local-app-feeling managed
devstation service.

## Status Legend

| Status | Meaning |
|---|---|
| Done | Completed and verified enough to build on |
| Ready | Prerequisites are in place; can execute next |
| In Progress | Started but not verified complete |
| Blocked | Requires missing auth, infrastructure, decision, or dependency |
| Planned | Not started; defined enough to schedule |
| Parked | Deferred intentionally |

## Current State

Verified on 2026-05-22:

| Area | State |
|---|---|
| Warden Devstation | VM `116` is running and reachable as `devstation.clyffy.ai` via SSH alias |
| Local VS Code | Remote-SSH and language extensions installed |
| Local Cursor | Cursor Remote-SSH and language extensions installed |
| Browser IDE fallback | `code-server@wardenop` active on VM-local `127.0.0.1:8080` |
| Browser IDE alias | `code.devstation.clyffy.ai` forwards to local `127.0.0.1:18080` |
| Cloudflare mutation | Done through Infisical-brokered `WARDEN_CLOUDFLARE_DNS_ADMIN` inside `warden-capsule`; token value not printed |
| Public jump DNS | `ssh.clyffy.ai` resolves to `104.176.44.101` as DNS-only |
| Master Clyffy app | LXC `120` not created |
| Dedicated edge | LXC `115` not created |
| Dynamic UI backend | Not implemented |

## Master Checklist

| ID | Workstream | Milestone | Status | Evidence | Next action |
|---|---|---|---|---|---|
| CLY-UX-001 | Local editor | Friendly SSH alias for devstation | Done | `ssh devstation.clyffy.ai` returns `warden-devstation-01` | Use as editor target |
| CLY-UX-002 | Local editor | VS Code Remote-SSH installed locally | Done | VS Code window title shows `WardenClyffe-latest [SSH]` | Use as primary local-app path |
| CLY-UX-003 | Local editor | Cursor Remote-SSH installed locally | Done | Cursor window title shows `WardenClyffe-latest [SSH]` | Use as AI coding local-app path |
| CLY-UX-004 | Local editor | One-command local launchers | Done | `scripts/local/open-warden-devstation-*.cmd` | Pin shortcuts later |
| CLY-UX-005 | Local editor | Browser IDE fallback | Done | `code-server@wardenop`, `code.devstation.clyffy.ai` | Keep fallback private |
| CLY-DNS-001 | DNS | Public jump record helper | Done | `scripts/dns/upsert-cloudflare-a-record.sh` supports Infisical-brokered token | Use canonical Cloudflare token |
| CLY-DNS-002 | DNS | `ssh.clyffy.ai` public DNS-only A record | Done | `ssh.clyffy.ai -> 104.176.44.101`, Cloudflare record `605ae29461a8db03d11bbe893e7e4974` | Keep DNS-only; do not proxy SSH |
| CLY-DNS-003 | DNS | Private `devstation.clyffy.ai` split DNS | Planned | SSH alias exists now | Add after WardenNet/OPNsense split DNS |
| CLY-INF-001 | Infra | Master Clyffy LXC `120` | Blocked | rollout plan exists; VMID absent | Provision after final infra review |
| CLY-INF-002 | Infra | Dedicated Caddy edge LXC `115` | Blocked | planned; not created | Build before public routes |
| CLY-AUTH-001 | Identity | Authentik app for `master.clyffy.ai` | Blocked | app/edge not live | Configure after LXC `120` and edge |
| CLY-API-001 | API | Dynamic home contract | Planned | spec created | Implement `/api/clyffy/home` |
| CLY-API-002 | API | Workspace contract | Planned | devstation descriptor exists | Implement workspace list/detail |
| CLY-API-003 | API | Node graph contract | Planned | Warden inventory docs exist | Implement graph read model |
| CLY-API-004 | API | Content slot registry | Planned | spec defines shape | Implement registry table/file seed |
| CLY-API-005 | API | Open-intent action | Planned | local launchers exist | Create task/audit event on open |
| CLY-UI-001 | UI | Dynamic Clyffy shell | Planned | spec created | Build layout against mock API |
| CLY-UI-002 | UI | Home cards from API | Planned | endpoint list defined | Replace hardcoded cards |
| CLY-UI-003 | UI | Workspace detail | Planned | local editor path verified | Add editor/previews/tier/actions |
| CLY-UI-004 | UI | POA&M/plan panel | Planned | docs exist | Render active milestone state |
| CLY-UI-005 | UI | Node network graph | Planned | graph contract defined | Render fleet/project/workspace/MCP drilldown |
| CLY-AI-001 | Intelligence | Touchpoint ingestion status | In Progress | `scripts/foundation/validate-touchpoints.py` inventories sync-enabled docs | Build Warden sync worker and UI status |
| CLY-AI-002 | Intelligence | Qdrant source health | Planned | Qdrant LXC `106` exists | Add collection/status probe |
| CLY-AI-003 | Intelligence | Surreal projection health | Planned | Surreal LXC `104` exists | Add graph/status probe |
| CLY-AI-004 | Intelligence | Clyffe Code workspace touchpoints | Done | `docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md`, workspace README | Keep workspace docs sync-enabled |
| CLY-AI-005 | Intelligence | Workspace preflight projection | Planned | preflight shape documented | Generate preflight and project into Qdrant/SurrealDB |
| CLY-AI-006 | Intelligence | Clyffy MCP orchestrator contract | Done | `docs/CLYFFY_MCP_ORCHESTRATOR.md` | Index and project through sync worker |
| CLY-AI-007 | Intelligence | Markdown memory de-heavy rule | Done | `docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md`, validator body-size warnings | Move future memory into generated context packs and typed projections |
| CLY-AI-008 | Intelligence | SurrealDB dynamic projection model | Done | `docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md`, projection schema proposal | Build dry-run sync worker and Warden UI read model |
| CLY-CODE-001 | Clyffe Code | Workspace tier model | Done | `docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md` | Use Premium Pilot as first internal proof |
| CLY-CODE-002 | Clyffe Code | Upgrade request flow | Planned | resource concepts documented | Add request-only action first |
| CLY-CODE-003 | Clyffe Code | Customer-safe template | Planned | operator VM `116` exists | Create clean template later |

## Sprint Sequence

### Sprint 0: Foundation Lock

Goal: make the current local-app path repeatable.

Exit criteria:

- VS Code opens `/workspace/WardenClyffe-latest` on `devstation.clyffy.ai`.
- Cursor opens `/workspace/WardenClyffe-latest` on `devstation.clyffy.ai`.
- Browser fallback opens through `code.devstation.clyffy.ai`.
- Cloudflare auth path is brokered through Infisical without printing tokens.
- `ssh.clyffy.ai` resolves publicly to the homebase IP as DNS-only.

### Sprint 1: Master Clyffy Dynamic Contracts

Goal: define and mock the dynamic API before building UI.

Deliverables:

- `/api/clyffy/home` response contract.
- `/api/clyffy/workspaces` response contract.
- `/api/clyffy/node-graph` response contract.
- content slot seed for home cards.
- fixture data for current Warden Devstation, capsule, and known LXCs/VMs.

### Sprint 2: Dynamic UI Shell

Goal: render Clyffy home from fixtures or read-only API.

Deliverables:

- page shell for `master.clyffy.ai`;
- home cards populated from content slots;
- workspace card for `warden-devstation-01`;
- POA&M status panel;
- source health panel for Postgres, Qdrant, SurrealDB, and Warden inventory.

### Sprint 3: Workspace Detail And Local Editor Actions

Goal: make the devstation feel productized.

Deliverables:

- workspace detail page;
- Open in VS Code, Open in Cursor, Browser IDE fallback actions;
- preview/port panel;
- resource/tier panel;
- snapshot/backup request buttons that create Warden tasks only.

### Sprint 4: Node Network Graph

Goal: visualize the WardenClyffe operating graph.

Deliverables:

- fleet graph;
- host drilldown;
- namespace/project drilldown;
- workspace drilldown;
- MCP touchpoint/status overlay;
- health and route status badges.

### Sprint 5: Live Master Clyffy Route

Goal: make `master.clyffy.ai` real.

Prerequisites:

- LXC `120` exists and is healthy.
- LXC `115` edge exists or temporary legacy route is explicitly approved.
- Authentik app/client is configured.
- internal PowerDNS and public Cloudflare records are applied.

### Sprint 6: Clyffe Code Customer MVP

Goal: make the managed devstation product customer-safe.

Deliverables:

- clean workspace template;
- tenant-scoped workspace ownership;
- Clyffe Connect MVP contract;
- tier/upgrade request flow;
- no operator secrets or direct Proxmox access.

## Immediate Next Actions

1. Implement mocked `/api/clyffy/home` data contract before any UI styling.
2. Implement workspace fixture data for `warden-devstation-01` and Premium
   Pilot tier status.
3. Add Warden task/audit records for editor open, snapshot request, and
   upgrade request.
4. Add private split DNS only after WardenNet/OPNsense policy is chosen.
5. Build the Clyffy dynamic shell against the mock contracts.
