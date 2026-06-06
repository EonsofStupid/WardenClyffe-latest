---
wardenclyffe_touchpoint:
  version: 1
  kind: estate-ops
  namespace: wardenclyffe.mediastack.ops
  owner: hades
---

# Mediastack Ops

> Owner: `hades` · infra executed via Warden

Operational runbook pointers for the Mediastack VM. Infrastructure actions
(provisioning, network, backups) are executed through **Warden**, which holds
infra authority; this folder records the Mediastack-specific procedures and any
owner-approved exceptions.

## Runbook index (fill in as the VM is provisioned)

- [ ] **Provision** — Proxmox node, VM sizing, OS baseline
- [ ] **Network** — attach to the isolated segment (see `../docs/NETWORK.md`)
- [ ] **Deploy** — bring up `category: media` templates from `../catalog/compose/`
- [ ] **Members** — onboard/offboard via the `mediastack` sub-MCP membership tools
- [ ] **Backup** — what to back up and where
- [ ] **Exceptions log** — any owner-approved public-exposure override, with date + reason

## Guardrails

- Keep the VM internal-only; no public routes without an explicit owner override
  logged above.
- Preserve network isolation on every infra change.
- Keep `../estate.toml` and the mesh registry in sync after structural changes.
