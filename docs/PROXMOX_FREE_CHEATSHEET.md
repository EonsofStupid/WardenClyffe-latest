---
wardenclyffe_touchpoint:
  version: 1
  kind: proxmox-cheatsheet
  namespace: wardenclyffe.proxmox
  owner: docs/PROXMOX_FREE_CHEATSHEET.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  module: module-01-warden
---

# Proxmox Free Cheat Sheet

This is the working Warden cheat sheet for Proxmox VE as a free/open-source
substrate. It is a build map, not legal advice.

Verified May 21, 2026 against official Proxmox pages where reachable.

## Current Official Baseline

- Proxmox VE 9.1 ISO is the current Proxmox VE download listed by Proxmox.
- Proxmox VE source code is released under GNU AGPLv3.
- Proxmox states that open-source access gives full access to functionality.
- Proxmox VE manages KVM virtual machines and LXC containers from one web UI.
- The same management surface is available through GUI, CLI, and REST API.
- Proxmox VE supports clustering, RBAC/ACLs, multiple auth realms, HA, SDN,
  storage, Ceph, firewall, backup, and Proxmox Backup Server integration.

Primary references:

- https://www.proxmox.com/en/downloads
- https://www.proxmox.com/en/products/proxmox-virtual-environment/features
- https://pve.proxmox.com/wiki/Proxmox_VE_API
- https://pve.proxmox.com/pve-docs/api-viewer/index.html
- https://pve.proxmox.com/pve-docs/pve-package-repos-plain.html

## Free Usage Posture

Proxmox VE can be used without a paid subscription. The practical free setup
usually means:

- Use the no-subscription repository instead of the enterprise repository.
- Do not rely on official enterprise support.
- Treat updates as operator-owned responsibility.
- Keep backups and rollback plans before applying updates.
- Do not remove or bypass licensing/subscription UI behavior in WardenClyffe.
  Warden should configure repositories and report status, not patch Proxmox
  branding or nags.

For WardenClyffe, the clean position is:

```text
Warden manages Proxmox VE through documented APIs and host-local tools.
WardenClyffe branding appears in Warden/Clyffe, not by modifying Proxmox VE UI.
```

## API Basics

Base URL:

```text
https://<host>:8006/api2/json
```

API token header:

```text
Authorization: PVEAPIToken=<user>@<realm>!<tokenid>=<secret>
```

Ticket auth:

- Read/write browser-style sessions use ticket auth.
- Write requests with ticket auth require `CSRFPreventionToken`.
- API tokens do not need CSRF headers for POST/PUT/DELETE because they are not
  browser-cookie sessions.

Warden should prefer API tokens for server-to-server automation.

## Host-Local Helpers

Use REST API first. Use host-local commands only where Proxmox does not expose
the operation cleanly or where the local CLI is the safer operator workflow.

Useful host-local tools:

```text
pvesh    exposes the REST API from a node
qm       QEMU VM management
pct      LXC management
pveum    users, roles, tokens, ACLs
vzdump   backups
pvesm    storage
pvecm    cluster management
journalctl/systemctl/logs for host diagnostics
```

Do not invent nonexistent REST endpoints. The existing Go repo notes that
`POST /nodes/{node}/lxc/{vmid}/exec` does not exist in PVE 9.1.9.

## Warden Coverage Map

| Proxmox area | Warden status target | Customer exposure |
|---|---|---|
| Version and auth probe | required | hidden |
| Node inventory and health | required | summarized only |
| VM/LXC inventory | required | scoped assigned resources only |
| Lifecycle actions | required | safe actions only |
| Task polling | required | status only |
| Snapshots | required | scoped request/restore flow |
| Backups | required | restore request and history |
| Templates | required | approved catalog only |
| Storage | required | quotas and assigned volumes only |
| Network and SDN | required | service-level status only |
| Firewall | required | approved service rules only |
| Console | required | scoped console only |
| Users, roles, ACLs | required | never direct |
| Cluster | required | never direct |
| HA/migration/replication | required | status/request only |

## Existing Go Warden Coverage

Current Go Warden source:

- `wardenclyffe/warden/proxmox.go`
- `wardenclyffe/docs/specs/02-proxmox-connector.md`
- `wardenclyffe/agent/clyffy-dean/clyffy_dean/tools/proxmox.py`
- `wardenclyffe/registry/context-mesh.yaml`

Already covered in Go Warden:

- `/version` probe.
- `/cluster/resources?type=vm` inventory.
- `/nodes/{node}/status`.
- `/nodes/{node}/storage`.
- VM/CT lifecycle actions.
- QEMU snapshot action.
- Audit call after state-changing actions.

Known gaps:

- Multi-node aggregation.
- UPID task polling.
- LXC snapshots.
- Backup browse/restore.
- ISO/template upload.
- network, SDN, firewall, storage management.
- cluster operations and migration.
- customer pool/tenant scoping.

## API Surface To Absorb First

Phase 1 should be read-heavy and safe:

```text
GET /version
GET /nodes
GET /cluster/status
GET /cluster/resources
GET /nodes/{node}/status
GET /nodes/{node}/storage
GET /nodes/{node}/tasks
GET /nodes/{node}/qemu
GET /nodes/{node}/lxc
```

Phase 2 should add actions with task polling:

```text
POST /nodes/{node}/qemu/{vmid}/status/start
POST /nodes/{node}/qemu/{vmid}/status/shutdown
POST /nodes/{node}/qemu/{vmid}/status/reboot
POST /nodes/{node}/qemu/{vmid}/status/stop
POST /nodes/{node}/lxc/{vmid}/status/start
POST /nodes/{node}/lxc/{vmid}/status/shutdown
POST /nodes/{node}/lxc/{vmid}/status/reboot
POST /nodes/{node}/lxc/{vmid}/status/stop
GET  /nodes/{node}/tasks/{upid}/status
GET  /nodes/{node}/tasks/{upid}/log
```

Phase 3 should cover service-provider features:

- clone from approved template.
- create LXC/VM from product plan.
- resize CPU, RAM, and disk.
- snapshots and backups.
- migration when server2 joins.
- firewall and SDN views.
- console broker.
- customer ownership mapping.

Check every endpoint against the official API viewer before coding.

## Warden Policy Model

Every Proxmox action should flow through this pipeline:

```text
discover -> normalize -> plan -> policy gate -> execute -> poll task -> audit -> project to mesh
```

Minimum action record:

```text
request_id
actor_id
actor_type
tenant_id
resource_id
proxmox_node
proxmox_vmid
action
risk_level
approval_state
upid
started_at
finished_at
result
error_summary
```

## Customer Boundary

Clyffe must not expose raw Proxmox:

- no Proxmox host URL.
- no Proxmox token IDs.
- no raw Proxmox permission paths.
- no direct Proxmox task logs unless sanitized.
- no destructive direct actions without Warden policy.

Clyffe sees Warden resources:

```text
clyffe service
clyffe server
clyffe container
clyffe backup
clyffe ticket
clyffe action request
```

