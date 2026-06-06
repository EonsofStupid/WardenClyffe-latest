---
wardenclyffe_touchpoint:
  version: 1
  kind: warden-establishment-poam
  namespace: wardenclyffe.warden.poam
  owner: docs/WARDEN_ESTABLISHMENT_POAM.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/HOST_FLEET_AND_ONBOARDING.md
    - docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
    - docs/FOUNDATION_SERVICE_MATRIX.md
    - docs/FOZZY_EXIT_AND_CADDY_HANDOFF.md
    - docs/WARDENCLYFFE_CATALOG_REPO_BOUNDARY.md
    - docs/WARDEN_OPERATOR_CAPSULE.md
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - docs/CLYFFY_MCP_ORCHESTRATOR.md
    - docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md
    - docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md
    - docs/SURREALDB_PUBLIC_SELF_HOSTING_PLAN.md
    - docs/SURREALDB_SELF_HOSTED_RUNBOOK.md
    - docs/CLYFFY_DYNAMIC_UI_SPEC.md
    - docs/CLYFFY_DYNAMIC_UI_POAM.md
    - docs/MASTER_CLYFFY_ROLLOUT_PLAN.md
    - docs/WARDEN_CLYFFE_PILOT_ROADMAP.md
    - modules/warden/infrastructure/operator-capsule/README.md
    - modules/warden/infrastructure/devstation/README.md
    - modules/warden/infrastructure/operator-access/hosts/foundation-01.yaml
---

# Warden Establishment POA&M

This is the controlling Plan of Action and Milestones for establishing Warden
as the real operator/server-control platform before Clyffe becomes the
customer-facing portal.

Use this file to answer:

- what is already done,
- what is verified,
- what is blocked,
- what is next,
- which work requires backups, explicit approval, or a staged rollout.

## Status Legend

| Status | Meaning |
|---|---|
| Done | Completed and verified enough to build on |
| Ready | Prerequisites are in place; can be executed next |
| In Progress | Started but not verified complete |
| Blocked | Cannot proceed without a missing credential, access path, decision, or prerequisite |
| Planned | Not started; defined enough to schedule |
| Parked | Intentionally deferred |

## Current Verified State

Verified on 2026-05-22:

| Area | Fact |
|---|---|
| Warden host-local access | `ssh server1` works as `root` using the dedicated Warden key |
| Server host identity | `server1` ED25519 fingerprint pinned as `SHA256:vSWJ9KW9M+1w2my9hlCsmpIRnCgk7ClMjmVCiJL39Xc` |
| Proxmox version | `pve-manager/9.1.9` |
| Proxmox API read path | Existing token/env path can read `/version` and inventory |
| Live Postgres target | LXC `110`, `clyffy-pg-master`, Postgres 17 direction |
| Internal DNS target | LXC `109`, `clyffy-pdns` |
| Current Warden app | LXC `102`, `warden` |
| Operator capsule | LXC `114`, `warden-operator-capsule`, internal-only `10.0.0.114`, `onboot=1`, alias `capsule.clyffy.ai` |
| Capsule toolchain | Node 24 LTS, Codex CLI, Claude Code, Infisical CLI, SOPS, GitHub CLI |
| Capsule secret mount | `/run/warden-secrets` tmpfs, helper commands verified |
| Warden devstation | VM `116`, `warden-devstation-01`, internal-only `10.0.0.116`, `onboot=1` |
| Devstation toolchain | Ubuntu 26.04, Node 24 LTS, Codex CLI, Claude Code, Infisical CLI, GitHub CLI, SOPS, Rust, Go, Python, uv |
| Devstation hosted editor | private `code-server`, tunnel alias `warden-devstation-code` |
| Devstation friendly aliases | `devstation.clyffy.ai` and `code.devstation.clyffy.ai` configured as local SSH aliases |
| Clyffy dynamic UI planning | spec and sprint POA&M created |
| Clyffy MCP orchestrator | boundary captured; gateway planned in registry |
| Current public homebase | `104.176.44.101` |
| Public jump DNS | `ssh.clyffy.ai` resolves to `104.176.44.101` as Cloudflare DNS-only |
| Cloudflare DNS token source | Infisical Clyffy project root `cloudflare_api_key`; value must not be printed |
| Planned clean edge | LXC `115`, not created yet |
| Fozzy Caddy export | non-secret export saved under `ops/exports/fozzy-caddy-edge-20260522/` |
| Planned master Clyffy app | LXC `120`, not created yet |
| `master.clyffy.ai` | no public A record yet |
| Legacy edge risk | VM `501` still exists but must not be treated as final |

## Ground Rules

1. Warden is Module 1 and owns operator/server control.
2. Clyffe is Module 2 and consumes only customer-safe Warden APIs.
3. Proxmox is the current substrate, not the public product surface.
4. Postgres is product truth for tenants, inventory, tickets, CRM, RBAC,
   workflow state, route intent, and audit.
5. SurrealDB and Qdrant are AI/intelligence projections, not customer truth.
6. SSH host-local commands are bootstrap/helper actions; normal inventory and
   task state should move into Warden APIs.
7. No public DNS route should go live before a backend and edge route can
   answer.
8. No stateful Postgres change should run without backup material and a
   restore path.
9. No new customer-facing route should depend on VM `501` except as a conscious
   temporary exception.
10. PowerShell is a temporary launcher/bridge for Warden live infrastructure
    work. The durable operator shell is `ssh warden-capsule`.
11. Secrets for agent-assisted work enter the operator capsule as brokered
    files under `/run/warden-secrets`, not as pasted chat text or committed
    repo files.
12. The devstation is the daily private coding VM. The capsule remains the
    secret-sensitive operator workspace.
13. Markdown touchpoints are routing manifests; generated stores and context
    packs carry intelligence memory.

## Master Checklist

| ID | Workstream | Milestone | Status | Evidence | Next action |
|---|---|---|---|---|---|
| WDN-GOV-001 | Governance | Warden/Clyffe naming and module split captured | Done | `AGENTS.md`, module docs | Keep using Warden/Clyffe terms only |
| WDN-GOV-002 | Governance | Agent touchpoints and wrappers aligned | Done | `docs/ai/*`, `.agents/skills/wardenclyffe-base` | Keep wrappers thin; update base docs first |
| WDN-GOV-003 | Governance | Controlling POA&M created | Done | this file | Update status after every live milestone |
| WDN-ACC-001 | Access | Proxmox API read-only access verified | Done | `scripts/check-proxmox-access.ps1`, live `/version` probe | Move API probes into Warden inventory service |
| WDN-ACC-002 | Access | SSH host identity verified and pinned | Done | `known_hosts`, `foundation-01.yaml` | Keep fingerprint in host profile |
| WDN-ACC-003 | Access | Dedicated Warden SSH key installed | Done | `ssh server1` returns `root` and `server1` | Move away from root later to sudo-limited operator |
| WDN-ACC-004 | Access | Infisical/keyring local workflow verified | In Progress | Infisical CLI installed in operator capsule | Authenticate/configure approved Infisical flow inside capsule |
| WDN-CAP-001 | Operator capsule | LXC `114` provisioned internal-only | Done | `ssh warden-capsule`, `pct config 114` | Keep no public route |
| WDN-CAP-002 | Operator capsule | Linux-first agent toolchain installed | Done | `warden-capsule-status` shows Codex, Claude, Infisical, SOPS, GitHub CLI | Authenticate CLIs inside capsule |
| WDN-CAP-003 | Operator capsule | Secret tmpfs and helpers verified | Done | `warden-secret-write/list/remove` test left zero files | Use helpers for future secret material |
| WDN-CAP-004 | Operator capsule | Malformed interrupted capsule key removed from `server1` | Done | no `warden-capsule-114-to-server1` authorized key entry | Design restricted operator path before re-adding |
| WDN-CAP-005 | Operator capsule | Repo cloned into capsule workspace | Done | `/workspace/WardenClyffe-latest` | Sync/push local uncommitted docs before relying on clone as complete truth |
| WDN-CAP-006 | Operator capsule | Remote-SSH/Cursor workflow documented | Done | `modules/warden/infrastructure/operator-capsule/README.md` | Open editor against `warden-capsule` |
| WDN-CAP-008 | Operator capsule | Domain-friendly headless agent launch | Done | `capsule.clyffy.ai`, `operator.clyffy.ai`, `scripts/local/open-warden-capsule-*.cmd` | Use for Claude/Codex capsule sessions |
| WDN-CAP-007 | Operator capsule | Capsule-to-server1 restricted operator account | Planned | root SSH from capsule intentionally not active | Replace direct root path with sudo-limited account |
| WDN-DEV-001 | Devstation | Private VM `116` provisioned | Done | `qm config 116`, `ssh warden-devstation` | Keep no public route |
| WDN-DEV-002 | Devstation | VS Code/Cursor Remote-SSH target ready | Done | SSH alias `warden-devstation`, `/workspace/WardenClyffe-latest` | Open editor over Remote-SSH |
| WDN-DEV-003 | Devstation | Coding toolchain installed | Done | `warden-devstation-status` | Authenticate CLIs inside VM as needed |
| WDN-DEV-004 | Devstation | Clyffe Code template direction documented | Done | `docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md` | Do not template until personal auth state is separated |
| WDN-DEV-005 | Devstation | Initial rollback snapshot captured | Done | `initial-devstation-toolchain-20260522` | Add recurring backup policy before relying on VM as sole work storage |
| WDN-DEV-006 | Devstation | Private browser IDE established | Done | `code-server`, `warden-devstation-code` | Keep SSH-tunneled only |
| WDN-DEV-007 | Devstation | Friendly local editor aliases | Done | `ssh devstation.clyffy.ai` reaches VM `116` | Use in VS Code/Cursor Remote-SSH |
| WDN-DEV-008 | Devstation | Snapshot/backup policy | Planned | initial snapshot exists; no recurring backup policy yet | Add before relying on VM as sole work storage |
| WDN-DEV-009 | Devstation | WardenNet/WireGuard access path | Planned | currently SSH via `server1` jump | Add private VPN/ZTNA path later |
| WDN-DNS-005 | DNS | Public jump record `ssh.clyffy.ai` | Done | `ssh.clyffy.ai -> 104.176.44.101`, Cloudflare record `605ae29461a8db03d11bbe893e7e4974` | Keep DNS-only and non-proxied |
| WDN-INV-001 | Inventory | Live guest inventory captured | Done | Proxmox API and `pct list` show current guests | Store inventory snapshots in Postgres |
| WDN-INV-002 | Inventory | Host descriptor for Wisconsin foundation host | Done | `modules/warden/infrastructure/operator-access/hosts/foundation-01.yaml` | Promote descriptor shape to Warden data model |
| WDN-INV-003 | Inventory | Virginia host descriptor | Planned | docs mention `host.us-va-cisco-01` | Add profile when access facts exist |
| WDN-DB-001 | Database | Product DB decision made | Done | service matrix and backend options docs | Keep Postgres for Warden/Clyffe truth |
| WDN-DB-002 | Database | Postgres LXC `110` backup/restore runbook | Planned | master rollout lists minimum material | Write and run backup preflight |
| WDN-DB-003 | Database | Patch Postgres 17 safely | Planned | LXC `110` exists | Snapshot, dump globals/dbs, then patch within major |
| WDN-DB-004 | Database | PostgreSQL 18 migration decision | Parked | major upgrade gate documented | Side-by-side restore drill first |
| WDN-EDGE-001 | Edge | Legacy VM `501` route risk documented | Done | service matrix, public IP foundation | Stop adding normal routes to VM `501` |
| WDN-EDGE-002 | Edge | Fozzy Caddy config exported | Done | `docs/FOZZY_EXIT_AND_CADDY_HANDOFF.md`, `ops/exports/fozzy-caddy-edge-20260522/` | Do not copy `.env` into git |
| WDN-EDGE-003 | Edge | Dedicated Caddy edge LXC `115` designed | Planned | service matrix names LXC `115` | Create provision/runbook before live build |
| WDN-EDGE-004 | Edge | Move public HTTP/HTTPS off VM `501` | Blocked | LXC `115` not created | Build LXC `115`, then update NAT/firewall |
| WDN-EDGE-005 | Edge | Delete Fozzy VM `501` | Blocked | public NAT still points to `10.0.0.100` | Delete only after handoff/deletion gate |
| WDN-DNS-001 | DNS | Public/internal DNS split documented | Done | public IP and master rollout docs | Keep public records on homebase IP |
| WDN-DNS-002 | DNS | Cloudflare write helper created | Done | `scripts/upsert-master-clyffy-cloudflare.ps1` | Provide token via env/keyring before apply |
| WDN-DNS-003 | DNS | Internal PowerDNS route for `master.clyffy.ai` | Blocked | LXC `120` not created | Create app target first |
| WDN-DNS-004 | DNS | Public `master.clyffy.ai` A record | Blocked | no public A record | Apply only after backend and edge route answer |
| WDN-AUTH-001 | Identity | Authentik kept as foundation IdP | Done | service matrix and app research | Finish realm/client/policy inventory |
| WDN-AUTH-002 | Identity | Warden OIDC app configured | Planned | LXC `103` exists | Create/verify Warden OIDC client |
| WDN-AUTH-003 | Identity | Clyffy/Clyffe OIDC routes configured | Blocked | app and edge not live | Configure after LXC `120` and edge |
| WDN-PROX-001 | Proxmox | Proxmox free API cheat sheet created | Done | `docs/PROXMOX_FREE_CHEATSHEET.md` | Convert to Rust/Go client coverage matrix |
| WDN-PROX-002 | Proxmox | Read-only Warden inventory API | Planned | LXC `102` exists | Implement/store host/node/guest/storage/network |
| WDN-PROX-003 | Proxmox | Safe lifecycle actions | Planned | roadmap defines allowed actions | Require approval, task polling, audit |
| WDN-PROX-004 | Proxmox | Full Proxmox API surface mapping | Planned | cheat sheet exists | Build endpoint coverage tracker |
| WDN-CLYFFY-001 | Master Clyffy | Master rollout plan created | Done | `docs/MASTER_CLYFFY_ROLLOUT_PLAN.md` | Execute phases in order |
| WDN-CLYFFY-002 | Master Clyffy | LXC `120` provisioner guardrailed | Done | script defaults to `vmbr1` | Dry-run script from `server1` SSH path |
| WDN-CLYFFY-003 | Master Clyffy | LXC `120` created | Blocked | VMID not present | Run provisioner after final review |
| WDN-CLYFFY-004 | Master Clyffy | `clyffy-master` deployed and healthy | Blocked | LXC `120` not present | Build binary/env/service after LXC |
| WDN-CLYFFY-005 | Master Clyffy | Dynamic UI spec and sprint POA&M | Done | `docs/CLYFFY_DYNAMIC_UI_SPEC.md`, `docs/CLYFFY_DYNAMIC_UI_POAM.md` | Implement mocked dynamic API contracts |
| WDN-CLYFFY-006 | Master Clyffy | Dynamic home API | Planned | endpoint contract defined | Build `/api/clyffy/home` |
| WDN-CLYFFY-007 | Master Clyffy | Dynamic workspace UI | Planned | local editor path verified | Build workspace cards/actions |
| WDN-CLYFFY-008 | Master Clyffy | Node network graph UI | Planned | graph model specified | Build after inventory API |
| WDN-CLYFFE-001 | Clyffe | Customer-safe boundary documented | Done | roadmap and architecture docs | Keep Clyffe API-only, no direct Proxmox |
| WDN-CLYFFE-002 | Clyffe | Customer portal first slice | Planned | no app slice yet | Build after Warden inventory/API exists |
| WDN-CLYFFE-003 | Clyffe Code | Local-app-first workspace plan | Done | `docs/superpowers/plans/2026-05-22-clyffe-code-local-editor.md` | Keep as internal proof |
| WDN-CLYFFE-004 | Clyffe Code | Turnkey service spec and tier model | Done | `docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md` | Use Premium Pilot as first internal target after resource check |
| WDN-CAT-001 | Catalog | Deployment catalog repo boundary defined | Done | `docs/WARDENCLYFFE_CATALOG_REPO_BOUNDARY.md` | Promote scaffold to dedicated repo/submodule |
| WDN-CAT-002 | Catalog | Catalog workspace scaffold created | Done | `wardenclyffe-catalog/` | Add validation and point Warden at it |
| WDN-CAT-003 | Catalog | Catalog templates moved to dedicated repo | Planned | scaffold exists but not separate git repo yet | Promote repo and set `WARDEN_CATALOG_DIR` |
| WDN-CAT-004 | Catalog | Warden consumes catalog from dedicated path | Planned | `warden/catalog.go` supports `WARDEN_CATALOG_DIR` | Point Warden at new repo after migration |
| WDN-MESH-001 | MCP/AI | Markdown touchpoint model documented | Done | `docs/ai/*` | Keep POA&M/touchpoints synchronized |
| WDN-MESH-002 | MCP/AI | Qdrant/Surreal projection contract | Planned | services exist | Define sync from approved touchpoints |
| WDN-MESH-003 | MCP/AI | Warden node/workspace graph UI model | Planned | master rollout defines model | Implement after inventory API |
| WDN-MESH-004 | MCP/AI | Clyffy main MCP orchestrator boundary | Done | `docs/CLYFFY_MCP_ORCHESTRATOR.md`, `wardenclyffe/registry/context-mesh.yaml` | Build Clyffy Master gateway and sync worker |
| WDN-MESH-005 | MCP/AI | Markdown-as-touchpoint intelligence split | Done | `docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md`, validator size checks | Build sync worker that emits Qdrant, SurrealDB, task/audit links, and context-pack status |
| WDN-MESH-006 | MCP/AI | SurrealDB v2 dynamic projection plan | Done | `docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md`, `schemas/intelligence/surreal-touchpoint-projection.v2.surql` | Dry-run sync worker before live schema apply |
| WDN-MESH-007 | MCP/AI | Public-safe self-hosted SurrealDB establishment plan | Done | `docs/SURREALDB_PUBLIC_SELF_HOSTING_PLAN.md` | Verify LXC `104`, add backups, Caddy/Auth/Warden proxy routes, then publish DNS |
| WDN-MESH-008 | MCP/AI | Self-hosted SurrealDB persistence and export timer | Done | `docs/SURREALDB_SELF_HOSTED_RUNBOOK.md`, LXC `104` services | Resume cloud, export to capsule, import to staging first |
| WDN-OPS-001 | Ops | Backups required before customer service | Planned | service matrix gate | Write backup matrix by service |
| WDN-OPS-002 | Ops | Observability for Warden actions | Planned | Observatory exists | Trace Warden actions and AI suggestions |
| WDN-OPS-003 | Ops | Public TCP `:5432` exposure audited | Planned | public IP foundation flags it | Confirm/remove/justify forward |

## Near-Term Execution Order

The next methodical sequence is:

1. Update this POA&M after every completed live step.
2. Authenticate Codex, Claude, GitHub CLI, and Infisical from inside
   `ssh warden-devstation` for daily coding, and inside `ssh warden-capsule`
   only when secret-sensitive operator work needs those tools.
3. Sync or push the local uncommitted WardenClyffe docs so the capsule and
   devstation clones have the same operating truth as this workstation.
4. Use VS Code or Cursor Remote-SSH against `warden-devstation` for daily work,
   or `ssh warden-devstation-code` plus
   `http://127.0.0.1:18080/?folder=/workspace/WardenClyffe-latest` for the
   private browser IDE.
5. Run future live infrastructure commands from the capsule, using PowerShell
   only to launch the SSH session if needed.
6. Run a read-only Warden host inventory snapshot from Proxmox API and SSH.
7. Write the Postgres backup/restore runbook for LXC `110`.
8. Take/schedule backup material for LXC `110`.
9. Patch Postgres 17 only after backup verification.
10. Resource-check whether `warden-devstation-01` can move to Premium Pilot
    sizing on the Wisconsin host or should wait for the Virginia host.
11. Finish the Fozzy exit gate and decide whether to accept a short public
   route outage or build the replacement edge first.
12. Create the dedicated `wardenclyffe-catalog` repo and move deployment
   templates/Caddy scaffold out of the Warden app repo.
13. Create an LXC `115` Caddy edge provision/runbook.
14. Provision LXC `115` and migrate one low-risk route.
15. Provision LXC `120` for master Clyffy on `vmbr1`.
16. Add internal PowerDNS records for `master.clyffy.ai`.
17. Add Caddy route and TLS for `master.clyffy.ai`.
18. Publish public Cloudflare `master.clyffy.ai -> 104.176.44.101`.
19. Build Warden API/UI panels for host inventory, routes, DNS, certs, and
    service health.
20. Implement the mocked Clyffy dynamic API contracts.
21. Build the Clyffy dynamic home shell against those contracts.

## Live Write Approval Classes

| Class | Examples | Required before execution |
|---|---|---|
| Read-only | `pveversion`, `pct list`, Proxmox `GET` probes, DNS lookups | no extra approval once access is established |
| Local workstation setup | SSH config, known_hosts, helper scripts | explicit user confirmation |
| Operator capsule | `ssh warden-capsule`, CLI auth, secret helper use, workspace sync | keep secrets out of chat and repo; use `/run/warden-secrets` |
| Devstation | `ssh warden-devstation`, Remote-SSH, CLI auth, workspace sync | no public route; do not turn personal VM into customer template |
| Host bootstrap | `pct create`, `qm` config, package install | checklist item, reviewed command, rollback if applicable |
| Stateful data | Postgres package updates, schema migrations, backup changes | backup material and restore path |
| Public routing | Cloudflare DNS, Caddy reload, NAT/firewall changes | backend health check and rollback |
| Destructive | delete VM/LXC, disk/storage changes, firewall removal | explicit one-off approval |

## Definition Of Warden Established

Warden is established for the two-server pilot when:

1. Wisconsin host is registered as a Warden node with API and SSH helper access.
2. Virginia host is registered as a second Warden node.
3. Warden stores host/node/guest/storage/network inventory in Postgres.
4. Warden shows inventory and health in an operator UI.
5. Warden owns route intent for public domains and edge routes.
6. Warden records every write action as an approval, task, and audit event.
7. `master.clyffy.ai` runs through Warden-managed infrastructure.
8. Clyffe can show customer-safe assigned services without direct Proxmox
   access.

## Build Status Update — 2026-06-04 (warden-api + console foundation)

What is now BUILT on the devstation (`warden-devstation-01`), superseding earlier
"not started" notes for these items. Run with `bash scripts/dev/run-stack.sh`;
see `docs/RUNNING_THE_STACK.md`.

| Item | Status | Evidence |
|---|---|---|
| Postgres control-plane schema | **built (dev)** | `data/schema/sql/0001..0003`, 8 canonical schemas on local Postgres 18; managed LXC 110 pending Infisical creds |
| warden-api (Go) | **built** | `services/warden-api` — chi+pgx; fleet, automation (order→provision→audit), audit, dbadmin |
| Supabase-style data layer (our Go) | **built** | `internal/dbadmin` — schemas/tables/columns/rows + read-only SQL; mutations blocked, non-managed schemas 403 |
| Foundation capture | **built** | `warden_infra.platforms`/`services` capture Coolify (headless Docker) + Proxmox + all 18 matrix services, Infisical credential refs only |
| Warden Clyffy orchestrator | **built** | `internal/clyffy` — `/api/clyffy/{home,services,platforms,intelligence,plugins,connectors}`; live intelligence-plane probe |
| Connector/Plugin designation | **built + enforced** | `docs/WARDEN_CONNECTOR_PLUGIN_DESIGNATION.md`, `data/schema/sql/0003_designation.sql` — DB CHECK enforces plugin⇒ai-only; RRD (Reason Ready Daemon, reranker) registered |
| React console (Go+React, RAC/OKLCH) | **built** | `apps/console` — `src/lib/design/components/ui` variant system; Foundation/Workspaces/Data/Order surfaces |
| Repo reconciler (GitOps-lite) | **built** | `scripts/dev/warden-reconcile.sh` (+`--install-timer`): pull→migrate→rebuild on a schedule |

Blocked on Infisical authentication from the devstation (managed Postgres LXC
110, Qdrant API key `/warden/qdrant/01`, SurrealDB root, Coolify API): point
warden-api at managed Postgres; create the Qdrant collection + touchpoint
ingestion; connect the SurrealDB `clyffy` intelligence plane; deploy RRD.

## References

- `docs/WARDEN_CONNECTOR_PLUGIN_DESIGNATION.md`
- `docs/RUNNING_THE_STACK.md`
- `docs/HOST_FLEET_AND_ONBOARDING.md`
- `docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md`
- `docs/FOZZY_EXIT_AND_CADDY_HANDOFF.md`
- `docs/FOUNDATION_SERVICE_MATRIX.md`
- `docs/WARDENCLYFFE_CATALOG_REPO_BOUNDARY.md`
- `docs/WARDEN_OPERATOR_CAPSULE.md`
- `docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md`
- `docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md`
- `docs/MASTER_CLYFFY_ROLLOUT_PLAN.md`
- `modules/warden/infrastructure/operator-capsule/README.md`
- `modules/warden/infrastructure/devstation/README.md`
- `modules/warden/infrastructure/operator-access/README.md`
- `modules/warden/infrastructure/proxmox-access/README.md`
