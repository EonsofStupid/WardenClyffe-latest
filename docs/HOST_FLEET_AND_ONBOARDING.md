---
wardenclyffe_touchpoint:
  version: 1
  kind: host-fleet-onboarding
  namespace: wardenclyffe.fleet.onboarding
  owner: docs/HOST_FLEET_AND_ONBOARDING.md
  module: module-01-warden
---

# Host Fleet And Onboarding

Warden must become the panel for managing any host we add, starting with the
current Wisconsin server and the Virginia Cisco UCS C240 M5 build.

## Known Fleet Intent

| Stable id | Location | Hardware note | Role | Status |
|---|---|---|---|---|
| `host.us-wi.foundation-01` | Wisconsin | 88-core server, 128 GB RAM | first WardenClyffe foundation and AIaaS pilot | existing |
| `host.us-va-cisco-01` | Virginia | Cisco UCS C240 M5, 96 cores | new Proxmox capacity and AIaaS expansion | building |

The exact provider, public IPs, secrets, and token values belong in secrets and
host registry data, not in docs.

## Public Homebase

The current Wisconsin host is also the public homebase:

| Item | Value |
|---|---|
| Public IP | `104.176.44.101` |
| Public bridge | `vmbr0` |
| Internal bridge | `vmbr1`, `10.0.0.1/24` |
| Current public edge dependency | HTTP/HTTPS forwards to `10.0.0.100` |

See `docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md` for the domain, edge, and
Tailscale-exit foundation notes.

## Host Descriptor

Use a stable descriptor shape:

```yaml
id: host.us-wi.foundation-01
name: foundation-01
kind: proxmox
region: us-wi
status: active
roles:
  - warden-control-plane
  - aiaas-foundation
hardware:
  cpu_cores: 88
  ram_gb: 128
credentials:
  proxmox_api_token: platforms/proxmox/foundation-01/api-token
  ssh_key: ssh/warden-to-foundation-01
```

For the Virginia Cisco host:

```yaml
id: host.us-va-cisco-01
name: cisco-ucs-c240-m5-01
kind: proxmox
region: us-va
status: building
roles:
  - warden-worker
  - aiaas-compute
hardware:
  cpu_cores: 96
  ram_gb: null
credentials:
  proxmox_api_token: platforms/proxmox/cisco-ucs-c240-m5-01/api-token
  ssh_key: ssh/warden-to-cisco-ucs-c240-m5-01
```

## Idempotent Onboarding Pipeline

```text
register host descriptor
  -> validate stable id
  -> verify credentials are referenced, not embedded
  -> probe Proxmox /version
  -> list nodes
  -> list VM/LXC/storage inventory
  -> create or update Warden host record
  -> attach host to estate
  -> attach namespaces/projects
  -> enable mesh health checks
  -> show in Warden graph
```

Every step must be safe to re-run.

## Access Contract

The non-secret Proxmox access contract lives at:

- `modules/warden/infrastructure/proxmox-access/README.md`
- `modules/warden/infrastructure/operator-access/README.md`
- `modules/warden/infrastructure/operator-access/hosts/foundation-01.yaml`

Current expected variables:

```text
PROXMOX_HOST
PROXMOX_PORT
PROXMOX_NODE
PROXMOX_TOKEN_ID
PROXMOX_TOKEN_SECRET
PROXMOX_VERIFY_TLS
```

The first real operation should be a read-only `/version` probe, followed by
read-only inventory. Do not run write actions until Warden shows the host and
the operator confirms the target.

## Operator SSH Bootstrap

Some bootstrap operations still need host-local Proxmox commands such as
`pct`, `qm`, `pvesh`, `pvesm`, and `vzdump`. Those should be treated as a
verified operator access path, not random ad hoc shell access.

For `host.us-wi.foundation-01`, the current SSH host fingerprint is:

```text
SHA256:vSWJ9KW9M+1w2my9hlCsmpIRnCgk7ClMjmVCiJL39Xc
```

Local setup helper:

```powershell
.\scripts\setup-warden-host-ssh.ps1 -ExpectedFingerprint "SHA256:vSWJ9KW9M+1w2my9hlCsmpIRnCgk7ClMjmVCiJL39Xc"
```

That is a dry run. After it matches, `-Apply -WriteConfig -EnsureKey` creates
the local durable trust/profile and a dedicated Warden operator SSH key if it
does not already exist. The generated public key must be installed on the host;
the private key must never be pasted into chat or committed.

Current dev fallback discovered on this workstation:

```text
E:\dev\clyffy\secrets\.env.proxmox
```

Runtime should still converge on the SDK path:

```text
Infisical Cloud -> clyffy-go/sdk/secrets/sync -> OS keyring -> Warden/Clyffe
```

## Warden UI Surfaces

Warden should add:

- Fleet list: hosts, region, status, hardware, role, health.
- Host detail: Proxmox version, nodes, storage, VM/LXC counts, task history.
- Graph view: Wisconsin and Virginia nodes, Clyffy/Clyffe services, mesh
  links, public routes, resource ownership.
- Edge and DNS view: public IPs, public forwards, DNS records, edge routes,
  TLS state, route health, and rollback history.
- Host onboarding wizard: register, probe, verify, save.
- Mesh drilldown: host -> estate -> namespace/project -> MCP domain -> tool.

## Clyffy Orchestrator Demonstration

The master Clyffy VM should be treated as a Warden-managed service:

- Warden owns the VM/container and infrastructure policy.
- Clyffy orchestrator owns assistant behavior and handoff workflows.
- Clyffe can demonstrate customer-safe interaction through Warden APIs.
- Codex/Claude/Cursor integration should be visible as mesh participants, not
  hidden scripts.

## YAGNI Guardrail

Build only the next reliable slice:

1. read-only host registration and Proxmox probe.
2. inventory graph.
3. task-polling lifecycle actions.
4. master Clyffy VM as a managed service.
5. Clyffe read-only service dashboard.

Do not build billing, marketplace, distributed SQL, or public customer signup
until the two-host Warden foundation is reliable.
