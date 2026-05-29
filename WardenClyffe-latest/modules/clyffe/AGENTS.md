---
wardenclyffe_touchpoint:
  version: 1
  kind: module-agent-contract
  namespace: wardenclyffe.modules.clyffe.agents
  reads:
    - ../../AGENTS.md
    - ../../docs/WARDENCLYFFE_MODULE_MAP.md
---

# Clyffe Agent Contract

Clyffe is customer-facing.

Rules:

- Never call Proxmox directly.
- Use Warden APIs for infrastructure state and actions.
- Treat every customer action as tenant-scoped and audit-logged.
- Keep knowledge-base and assistant sources customer-safe.
- Destructive actions become requests or approvals unless Warden policy allows
  direct execution.

