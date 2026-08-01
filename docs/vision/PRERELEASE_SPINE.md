# Pre-release spine (vision capture)

Approved direction for methodical build. Amend only with operator sign-off.

## Roles

| Name | Role |
|------|------|
| **Proxmox** | Substrate OS for VMs and LXC |
| **Warden** | Technical control plane for operators |
| **Clyffe** | Customer: billing, support, knowledge, safe orders |
| **Clyffy** | Assistant / MCP orchestration (not product truth) |

## Absorb later (capabilities, not permanent brands)

Auth (Authentik + Zitadel) → Bifrost+Observatory (bridge) → Netbird (net) →  
DNS (PowerDNS + public sync) → edge intent (Caddy/Traefik **mocks then remove**) →  
services catalog → stylized panel.

## First build (Slice 0)

Proxmox inventory + task-true start/stop + audit + thin Warden UI.  
See `docs/plans/slice-0-proxmox-warden-spine.md`.

## Quality

DevPULSE Labs care: plan before code, hyper-modular contexts, registry naming,  
tokenized UI, prove on real artifacts. No shortcuts that hide wrong architecture.
