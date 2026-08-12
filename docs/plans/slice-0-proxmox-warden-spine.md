---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: slice-0-proxmox-warden-spine
  persona: clyffy-operator
  kind: plan
  owner: docs/plans/slice-0-proxmox-warden-spine.md
  module: module-01-warden
  focus_feature: "Building WardenClyffe"
  boundary: services/warden-api/internal/proxmox
  sync:
    qdrant: true
    surreal: true
---

# Slice 0 — Proxmox substrate + Warden control spine

**Status:** approved 2026-07-31 (operator yes).  
**Concept:** Proxmox is the OS that powers VMs/LXCs; Warden makes that easier to manage (technical). Clyffe is customer billing/support/knowledge (out of scope here).

## Goal

Prove the product spine end-to-end:

```text
Proxmox API → Warden inventory → one task-true action → poll UPID → audit → thin UI
```

## In scope

1. Go `internal/proxmox` client (token auth, TLS verify optional).
2. Live inventory: nodes + guests (qemu + lxc) with power state.
3. One action pair: **start** and **stop** a guest (task-true).
4. `shippin_core.action_requests` row per action; `shippin_audit.events` append.
5. Thin React Warden view: list guests + start/stop + task status.
6. Clear degradation when `PROXMOX_*` credentials missing (no silent fake inventory).

## Out of scope

- Auth multi-IdP merge, Bifrost/Netbird/DNS absorption, stylized panel v2, Clyffe billing.
- Full provision/clone pipeline, console proxy, backups, firewall, SDN.
- Caddy/Traefik as product (edge mocks later).

## Boundaries

| Path | Role |
|------|------|
| `services/warden-api/internal/proxmox/` | client + service + handler |
| `modules/warden/bounded-contexts/proxmox/` | context docs |
| `src/domains/warden/proxmox/` | React types, svc, view |
| `src/routes/admin.proxmox.tsx` | operator route under RequireOperator |

## API

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/api/warden/proxmox/status` | configured? node? last error |
| GET | `/api/warden/proxmox/guests` | live inventory |
| POST | `/api/warden/proxmox/guests/{node}/{kind}/{vmid}/start` | task-true start |
| POST | `/api/warden/proxmox/guests/{node}/{kind}/{vmid}/stop` | task-true stop |
| GET | `/api/warden/proxmox/actions/{id}` | action_request status |

`kind` = `qemu` | `lxc`.

## Verify

1. With credentials: inventory matches Proxmox for at least one guest.
2. Start/stop: action_request transitions to succeeded/failed; audit row exists.
3. Without credentials: status endpoint explains setup; no fake guests.
4. UI at `/admin/proxmox` shows inventory + controls.

## Env (never log secrets)

```text
PROXMOX_HOST PROXMOX_PORT PROXMOX_NODE PROXMOX_TOKEN_ID PROXMOX_TOKEN_SECRET PROXMOX_VERIFY_TLS
```

Load from env or `secrets/proxmox.env` (gitignored).

## Evidence

### 2026-07-31 implement

- Go: `services/warden-api/internal/proxmox/{proxmox,service,handler}.go` wired in `main.go`.
- React: `src/domains/warden/proxmox/` + route `/admin/proxmox`.
- Plan doc: this file.
- Live API without credentials:  
  `GET /api/warden/proxmox/status` → `configured:false` + setup message (expected).
- Live inventory/start/stop: **blocked until** operator creates `secrets/proxmox.env` with token (see example).
- Frontend build includes `admin.proxmox` chunk (green).

### Operator verify (when token present)

1. `cp secrets/proxmox.env.example secrets/proxmox.env` and fill token.
2. Restart warden-api.
3. `curl -sS localhost:8081/api/warden/proxmox/status` → reachable true.
4. `curl -sS localhost:8081/api/warden/proxmox/guests` → guest list.
5. POST start/stop on a safe non-prod guest; check action_requests + audit.