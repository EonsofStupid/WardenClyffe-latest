---
wardenclyffe_touchpoint:
  version: 1
  kind: product-module
  namespace: wardenclyffe.modules.warden
  owner: modules/warden/README.md
  module: module-01-warden
---

# Warden

Warden is Module 1.

Warden is the operator/server-control platform and Proxmox UI manager. It owns
infrastructure authority, host onboarding, Warden API, operator workflows,
audit, automation, and MCP mesh observability.

## Bounded Contexts

| Context | Purpose |
|---|---|
| `proxmox` | Proxmox API integration, task polling, VM/LXC lifecycle, storage, backup, console, SDN |
| `fleet` | host registry, remote hosts, regions, hardware, onboarding, health |
| `edge` | public IP ingress, edge routes, TLS state, public exposure audit |
| `dns` | public DNS, split-horizon DNS, provider sync, domain health |
| `mesh` | Context Mesh registry view, MCP observatory, touchpoint sync health |
| `identity` | operator/customer subject mapping, OIDC/bootstrap auth boundary |
| `audit` | action/event records, approvals, task history |
| `automation` | plans, jobs, schedules, idempotent operations |

## Implementation Sources

Current strongest implementation source is the nested Go Warden repo:

- `wardenclyffe/warden/proxmox.go`
- `wardenclyffe/warden/infrastructure_graph.go`
- `wardenclyffe/warden/internal/host/`
- `wardenclyffe/agent/clyffy-dean/`
- `wardenclyffe/registry/context-mesh.yaml`

Absorb behavior deliberately. Do not duplicate policy or credentials.

## Operating Plan

Use `docs/WARDEN_ESTABLISHMENT_POAM.md` as the controlling checklist for
what is done, what is blocked, and which Warden milestone is next.
