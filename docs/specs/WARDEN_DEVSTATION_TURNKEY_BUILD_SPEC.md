---
clyffy_touchpoint:
  version: 2
  kind: devstation-turnkey-build-spec
  workspace_id: wardenclyffe.warden
  project_key: wardenclyffe
  owner: docs/specs/WARDEN_DEVSTATION_TURNKEY_BUILD_SPEC.md
  module: module-01-warden
  sync_qdrant: true
  sync_surreal: true
  reads:
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
    - modules/warden/infrastructure/devstation/turnkey/
---

# Warden Devstation — Turnkey Build Spec

How a Warden devstation is normalized into a **dynamic, purchasable turnkey
service**: a clean clone boots into a known-good state with zero hand-edits,
secrets are brokered with no leakage, and WardenClyffe assigns the correct host
and a public IP. The acceptance gate is one command: `warden-devstation-status`
returns **PASS**.

## Minimal requirements (the smallest complete outcome)

A devstation is "established" when, on a fresh clone:

1. The **secret broker** runs and materializes secrets to `/run/warden-secrets`
   (tmpfs, root-only) — nothing on disk, nothing in chat.
2. The **W drive** is present, devstation-owned, and writable at the same path
   both sides (`/workspace/warden-storage` = `W:`).
3. The **plugin binaries** (`cortex-mcp`, `intelligence-sync`) are on W.
4. The **data plane** (Postgres) is reachable and migrated to head.
5. A **public IP** is assigned with a role, recorded in the inventory.
6. `warden-devstation-status` exits `0` (PASS).

Nothing ships half-built: a context is complete (broker + verification +
inventory row) or it is not installed.

## Components (named, per the structure + naming law)

| Concern | Where | Names |
|---|---|---|
| Secret broker (turnkey) | `modules/warden/infrastructure/devstation/turnkey/` | `systemd/infisical-agent.service`, `etc/warden/agent-config.yaml`, `etc/warden/secrets.tmpl`, `etc/warden/infisical-mi.env.template`, `bin/install-devstation-turnkey.sh`, `bin/warden-secrets-preflight`, `bin/warden-secrets-refresh-hook`, `bin/warden-devstation-status` |
| Public-IP inventory | `services/warden-api/internal/edge` | table `warden_infra.public_ips` (0007); `ListIPs`/`CreateIP`/`UpdateIP`; `GET/POST/PATCH /api/warden/edge/ips` |
| IP transition log | (existing) | `warden_infra.ip_migrations` (0004) — references inventory addresses |
| Admin surface | `src/domains/admin/edge` | `EdgeView`, `edge.svc.ts`, `types.ts`; route `/admin/edge` |
| Host/fleet truth | (existing) | `warden_infra.hosts`, `warden_infra.resources` |

## Provisioning checklist (clean clone → established)

```text
[ ] 1. Clone/boot the devstation guest (Proxmox); map W both sides.
[ ] 2. Secret pipeline:
       sudo modules/warden/infrastructure/devstation/turnkey/bin/install-devstation-turnkey.sh
       - first run writes /etc/warden/infisical-mi.env (root-only) from template
       - fill MI: CLIENT_ID, CLIENT_SECRET (LIVE), PROJECT_ID, ENV
       - OPERATOR GATE: allowlist the devstation egress IP in the MI
         Universal-Auth Trusted-IPs; issue a live client secret (a 401
         "Invalid credentials" means it rotated out — see decision log)
       - re-run installer → infisical-agent.service enabled + started
[ ] 3. W drive: /workspace/warden-storage writable; plugins/bin has
       cortex-mcp + intelligence-sync (build + deploy if absent).
[ ] 4. Postgres reachable; run migrations to head:
       go run ./services/warden-api/cmd/warden-migrate up
[ ] 5. Public IP: add + assign via /admin/edge (or POST /api/warden/edge/ips),
       set role (ingress/egress/exit) and status=active.
[ ] 6. Acceptance gate:  warden-devstation-status   → must be PASS
```

## How WardenClyffe assigns the correct host + public IP

- **Host** comes from `warden_infra.hosts` / `resources` (the fleet). A new
  devstation is a `resource_kind = 'devstation'` row bound to a `host_id`.
- **Public IP** comes from `warden_infra.public_ips`: the operator adds the
  address (provider, role), then assigns it (`host_id`) and flips
  `status = active`. Re-homing an address is logged in `ip_migrations`
  (`planned → dual_homed → cutover → complete`). This is how a purchase
  "lands on the correct host and maximizes our public IP" — inventory +
  assignment + auditable transition, all operator-driven from `/admin/edge`.

## Verification gates (what "PASS" asserts)

`warden-devstation-status` checks, and fails non-zero on any miss:
`infisical-agent.service` active · access token present · `warden.env` rendered
(key count) · W writable · plugin binaries present · Postgres accepting.

## Build order (one PR per component, each verifies)

1. Secret pipeline turnkey (units + scripts) — verify: status tool runs and
   reports each check; on a live MI, broker authenticates and renders secrets.
2. `public_ips` migration + `edge` context + endpoints — verify: list/add(401
   anon, 201 operator)/patch round-trip (proven 2026-06-13).
3. `/admin/edge` panel — verify: add an IP, activate it, see it in the list.
4. This spec wired into the provisioning runbook + buildplan P1-4 golden
   template (guard scripts + turnkey scripts auto-copied per user/service).

## Non-goals (until a second customer demands them)

Automated IP procurement/BGP, multi-provider failover orchestration, and
per-tenant secret projects beyond the single devstation MI. The inventory and
assignment model is built to extend to these without reshaping.
