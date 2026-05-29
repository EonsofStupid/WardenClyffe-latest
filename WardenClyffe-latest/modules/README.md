---
wardenclyffe_touchpoint:
  version: 1
  kind: module-root
  namespace: wardenclyffe.modules
  owner: modules/README.md
---

# Modules

Product modules live here.

| Path | Module | Durable name |
|---|---|---|
| `warden/` | Module 1 | Warden |
| `clyffe/` | Module 2 | Clyffe |
| `shared/` | shared kernel | WardenClyffe shared contracts |

Rules:

- Use Warden and Clyffe in code and APIs.
- Use module numbers only in planning.
- Keep contexts self-describing with README files.
- Keep cross-module contracts in `modules/shared/contracts/`.
- Do not move the nested Go repo here until an explicit absorption step.

