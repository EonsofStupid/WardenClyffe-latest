---
wardenclyffe_touchpoint:
  version: 1
  kind: subtree-agent-contract
  namespace: wardenclyffe.rust.scale
  reads:
    - ../AGENTS.md
---

# Rust Root Agent Contract

The repo-root `AGENTS.md` applies here.

The current root Rust crate is WardenClyffeScale, an existing MariaDB/MySQL
replication component. Do not treat it as the whole Warden/Clyffe control
plane. New Warden/Clyffe platform work should respect the architecture docs
and avoid making MariaDB replication define the server/customer product.

