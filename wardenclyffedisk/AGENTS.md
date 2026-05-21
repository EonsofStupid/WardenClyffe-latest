---
wardenclyffe_touchpoint:
  version: 1
  kind: subtree-agent-contract
  namespace: wardenclyffe.disk
  reads:
    - ../AGENTS.md
---

# WardenClyffeDisk Agent Contract

The repo-root `AGENTS.md` applies here.

WardenClyffeDisk is a Linux/FUSE-oriented storage component. Windows checks
may fail because libfuse is Linux-only; validate on Linux or with the release
workflow when changing runtime behavior.

