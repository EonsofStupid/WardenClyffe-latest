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
and a public IP. The access pattern is the single authority on `W:/configuration`;
acceptance is asserted by the installed helpers `warden-infisical-status`
(`machine_identity_active`) and `warden-devstation-status`.

## Minimal requirements (the smallest complete outcome)

A devstation is "established" when, on a fresh clone:

1. The **secret broker** (`warden-infisical-agent.service`) runs and writes the
   token to `/run/warden-secrets/infisical-access-token`; the MI creds are
   encrypted at rest via systemd-creds — nothing in plaintext on disk, on W, or
   in chat.
2. The **W drive** is present, devstation-owned, and writable at the same path
   both sides (`/workspace/warden-storage` = `W:`).
3. The **plugin binaries** (`cortex-mcp`, `intelligence-sync`) are on W.
4. The **data plane** (Postgres) is reachable and migrated to head.
5. A **public IP** is assigned with a role, recorded in the inventory.
6. `warden-infisical-status` reports `machine_identity_active` and
   `warden-devstation-status` reports the host/workspace/tools/git facts.

Nothing ships half-built: a context is complete (broker + verification +
inventory row) or it is not installed.

## Components (named, per the structure + naming law)

| Concern | Where | Names |
|---|---|---|
| **Access authority (single source)** | `W:/configuration/` | `authority.yaml`, `providers/{infisical,runtime-keyring,ssh,github}.yaml`, `clients/{claude,codex}.yaml`, `agents/clyffy.yaml` — every AI-access grant is described here |
| Secret broker (turnkey mirror of the installed authority) | `modules/warden/infrastructure/devstation/turnkey/` | `bin/warden-infisical-bootstrap`, `bin/warden-infisical-status`, `bin/warden-devstation-status`, `etc/warden/infisical/agent-config.yaml`, `systemd/warden-infisical-agent.service` (verbatim mirror of `/usr/local/bin` + `/etc` + the installed unit) |
| Public-IP inventory | `services/warden-api/internal/edge` | table `shippin_infra.public_ips` (0007); `ListIPs`/`CreateIP`/`UpdateIP`; `GET/POST/PATCH /api/warden/edge/ips` |
| IP transition log | (existing) | `shippin_infra.ip_migrations` (0004) — references inventory addresses |
| Admin surface | `src/domains/admin/edge` | `EdgeView`, `edge.svc.ts`, `types.ts`; route `/admin/edge` |
| Host/fleet truth | (existing) | `shippin_infra.hosts`, `shippin_infra.resources` |

## Provisioning checklist (clean clone → established)

```text
[ ] 1. Clone/boot the devstation guest (Proxmox); map W both sides.
[ ] 2. Secret broker (the AUTHORITY — already installed; supply the credential):
       sudo warden-infisical-bootstrap        # prompts for LIVE client id + secret
         - encrypts them with systemd-creds into /etc/credstore.encrypted (0600)
         - starts warden-infisical-agent.service → token at
           /run/warden-secrets/infisical-access-token
         - OPERATOR GATE: issue a live Universal-Auth client secret (current
           status: machine_identity_credentials_missing) + confirm the MI
           Trusted-IP allowlist for the devstation egress IP
       warden-infisical-status                # → status=machine_identity_active
       (or drive both from the Warden UI: /admin/connect)
[ ] 3. W drive: /workspace/warden-storage writable; plugins/bin has
       cortex-mcp + intelligence-sync (build + deploy if absent).
[ ] 4. Postgres reachable; run migrations to head:
       go run ./services/warden-api/cmd/warden-migrate up
[ ] 5. Public IP: add + assign via /admin/edge (or POST /api/warden/edge/ips),
       set role (ingress/egress/exit) and status=active.
[ ] 6. Acceptance:  warden-devstation-status   (host/workspace/tools/git)
                    warden-infisical-status     (machine_identity_active)
```

## How WardenClyffe assigns the correct host + public IP

- **Host** comes from `shippin_infra.hosts` / `resources` (the fleet). A new
  devstation is a `resource_kind = 'devstation'` row bound to a `host_id`.
- **Public IP** comes from `shippin_infra.public_ips`: the operator adds the
  address (provider, role), then assigns it (`host_id`) and flips
  `status = active`. Re-homing an address is logged in `ip_migrations`
  (`planned → dual_homed → cutover → complete`). This is how a purchase
  "lands on the correct host and maximizes our public IP" — inventory +
  assignment + auditable transition, all operator-driven from `/admin/edge`.

## Verification gates

- `warden-infisical-status` — the Infisical state machine: `cli_missing` →
  `machine_identity_not_installed` → `machine_identity_credentials_missing` →
  `machine_identity_inactive` → `machine_identity_token_missing` →
  `machine_identity_active`. Established = `machine_identity_active`.
- `warden-devstation-status` — host, workspace root + W label, tool versions
  (node/codex/claude/gh), and the project git status.
- `/admin/connect` surfaces the same facts in the UI (it shells these helpers).
- Postgres at head: `go run ./services/warden-api/cmd/warden-migrate status`.

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
