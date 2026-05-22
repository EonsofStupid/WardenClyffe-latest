---
wardenclyffe_touchpoint:
  version: 1
  kind: bounded-context
  namespace: wardenclyffe.warden.proxmox
  owner: modules/warden/bounded-contexts/proxmox/README.md
  module: module-01-warden
---

# Warden Proxmox Context

Owns Proxmox integration for Warden.

Responsibilities:

- Proxmox API client and models.
- node, VM, LXC, storage, backup, snapshot, console, firewall, SDN inventory.
- lifecycle action planning.
- UPID task polling.
- host-local helper policy for `pvesh`, `qm`, `pct`, `pveum`, `vzdump`, and
  `pvesm`.
- sanitized projections for Clyffe.

Source material:

- `wardenclyffe/warden/proxmox.go`
- `wardenclyffe/docs/specs/02-proxmox-connector.md`
- `docs/PROXMOX_FREE_CHEATSHEET.md`

