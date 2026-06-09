---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: workstation-bridge-template
  persona: clyffy-operator
  kind: doc
  owner: docs/specs/WORKSTATION_BRIDGE_TEMPLATE.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDENCLYFFE_REFERENCE_STACK.md
    - docs/ai/WARDENCLYFFE_OFFICIAL_BASELINE.md
  sync:
    qdrant: true
    surreal: true
---

# Workstation ↔ Service Bridge (template)

How a customer's laptop reaches their devstation/capsule + their W-drive, as a
**Warden-provisioned template feature** — hypervisor-agnostic, secure, revocable.

## Principle

The bridge is a **secure overlay**, not public SMB/NFS. The workstation joins an
encrypted mesh with its service; the W-drive maps over that mesh as a drive.
Warden provisions overlay membership + the mount + access as part of the service
template, tied to the customer (subject) and revoked on deprovision.

## Layers

| Layer | Mechanism | Notes |
|---|---|---|
| Overlay (the bridge) | **WireGuard** | Terminated at **OPNsense** edge (reference stack) and/or coordinated by **Headscale** (Go, self-host) for Tailscale-style enrollment. Per-service keys. |
| Drive map | **W-drive over the overlay** | SMB or SSHFS **only over WireGuard** — never raw public. Maps to a drive letter on the workstation. |
| Shell/IDE | SSH + Remote-SSH / code-server | Over the same overlay (today: ProxyJump via `ssh.clyffy.ai`). |
| Identity | subject (hades) ↔ service ↔ overlay membership | Issued on provision, audited, revoked on deprovision. |

## Provisioning flow (template)

```
purchase → Warden provisions service (devstation/capsule on any hypervisor)
  → issue overlay membership (WireGuard config / Headscale pre-auth key) to the customer
  → attach W-drive volume to the service
  → expose W over the overlay; customer maps it to their workstation drive letter
  → SSH/IDE reachable over the overlay
deprovision → revoke key + unmount + release volume (audited)
```

## Security (boring, strict)

- No public SMB/NFS. Everything rides WireGuard.
- Per-service keys in Infisical; least-privilege; rotate on demand.
- Devstation stays private (`10.0.0.116`); the edge/overlay fronts it.
- Membership + mounts recorded in audit; revocation is one action.

## Build / PR mapping

- `warden` **network** context (new): drives WireGuard via OPNsense API +/or
  Headscale; issues/revokes per-service membership.
- `storage-broker`: maps the W-drive over the overlay (engine decision parked).
- service **template manifest**: declares overlay + volume + IDE so provision is
  one deterministic step.

## Today vs template

- **Today (manual):** SSH (ProxyJump), `scp`/WinSCP, or **SSHFS-Win** to map
  `/workspace` as a Windows drive; this is how we move devpulse in now.
- **Template:** the above, automated per purchase. Not built yet — PR via the
  network context + storage-broker + template manifest.
