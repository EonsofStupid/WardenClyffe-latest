# WardenClyffe pre-release STATE

Living ledger for operators and agents. **Read this first.**  
Update when a slice lands, blocks, or the next cut changes.

## Current focus

**Slice 0 — Proxmox substrate + Warden control spine**  
Plan: `docs/plans/slice-0-proxmox-warden-spine.md`

```text
Proxmox = OS for VMs/LXCs
Warden  = technical control plane (inventory + task-true actions + audit)
Clyffe  = customer billing / support / knowledge (later)
```

## Product north star (locked direction)

- Hosting for **vibers who grow**: coding cloud → domains → services.
- Absorb engines under Warden contexts (auth, bridge, net, dns, services); **Caddy/Traefik = mocks/roadmap then remove**.
- Quality bar: DevPULSE Labs discipline (plan before code, modular, tokens, proof).
- Visual later: neubrutal + clay + liquid-glass **tokens**, fast by design.

## Stack (devstation)

| Piece | Port / path | Status |
|-------|-------------|--------|
| Postgres | `127.0.0.1:5432` db `wardenclyffe` | required |
| warden-api | `:8081` | Slice 0 + existing contexts |
| clyffe-api | `:8082` | thin account API |
| console | `:5173` | TanStack Start, root `src/` |
| Proxmox | `PROXMOX_HOST:8006` | needs `secrets/proxmox.env` |

## Where to start (human)

```bash
cd /workspace/WardenClyffe-latest   # or your clone path
make setup                         # tools + npm + go modules + secrets templates
# edit secrets/proxmox.env with API token (never commit)
make stack                         # APIs + console
# laptop: ssh -N -L 5173:127.0.0.1:5173 warden-devstation
# open http://127.0.0.1:5173/login  →  operator / warden-dev
# Slice 0: http://127.0.0.1:5173/admin/proxmox
make verify-slice0
```

## Secrets / Infisical

- CLI logged in; project `4a897376-3cbd-4aeb-8550-c7d3ed7ad113` env `dev`.
- `make sync-secrets` → `scripts/dev/sync-secrets-from-infisical.sh`.
- **Present:** `SERVER1_TAILNET_*`, Cloudflare, Authentik client secrets (names), etc.
- **Missing for Slice 0:** `PROXMOX_TOKEN_ID`, `PROXMOX_TOKEN_SECRET` in Infisical root `/`.
- Machine-identity agent on this host is **inactive** (`warden-infisical-agent` dead; `/run/warden-secrets` empty) — user session CLI works.

## Known broken / incomplete (allowed in pre-release)

- Public edge `warden.rrflow.ai` may be down; product URL not the primary dev path yet.
- Proxmox live inventory **blocked until** Infisical has Proxmox API token keys (then `make sync-secrets`).
- Clyffe support/billing/KB UI largely unbuilt.
- Auth is bootstrap operator, not Authentik+Zitadel merge.
- Bifrost/Netbird/PowerDNS/Observatory not absorbed.
- Some fleet views existed before routes; admin nav still evolving.
- Visual system not yet ColdLight-grade.

## Next after Slice 0 verify

1. Live start/stop proof against a **safe** guest on server1.
2. Capability capture docs for **auth** (Authentik + Zitadel under `auth`).
3. Do not expand stylized panel until spine is proven.

## Agent rules (pre-release)

1. Read this file + active plan before coding.
2. Plan in `docs/plans/` for non-trivial work.
3. Prefer align structure over greenwashing.
4. Never commit secrets.
5. Prove on real stack (`make verify-slice0` / real UI), not memory.
