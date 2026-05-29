---
wardenclyffe_touchpoint:
  version: 1
  kind: shared-kernel
  namespace: wardenclyffe.modules.shared
  owner: modules/shared/README.md
---

# Shared Kernel

Shared code and contracts for Warden and Clyffe.

Keep this small. Shared must not become a dumping ground.

Allowed:

- ID/value primitives.
- API DTO contracts.
- event envelopes.
- observability envelopes.
- test fixtures.

Not allowed:

- Proxmox policy.
- customer workflow decisions.
- auth provider implementation.
- database-specific repositories.

