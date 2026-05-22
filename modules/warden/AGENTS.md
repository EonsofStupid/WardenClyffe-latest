---
wardenclyffe_touchpoint:
  version: 1
  kind: module-agent-contract
  namespace: wardenclyffe.modules.warden.agents
  reads:
    - ../../AGENTS.md
    - ../../docs/PROXMOX_FREE_CHEATSHEET.md
    - ../../docs/GO_WARDEN_ABSORPTION_PLAN.md
---

# Warden Agent Contract

Warden is operator-facing. It may talk to Proxmox, host-local tools, Warden
storage, Qdrant, SurrealDB projections, and MCP mesh services.

Rules:

- Clyffe must not call Proxmox directly.
- Every Proxmox write must become a Warden action with task polling and audit.
- Credentials are references only; never write token values into files.
- Prefer read-only Proxmox probes before adding write actions.
- Keep host onboarding idempotent.

