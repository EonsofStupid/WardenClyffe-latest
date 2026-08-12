---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: warden-shared-storage
  persona: clyffy-operator
  kind: shared-storage-runbook
  owner: modules/warden/infrastructure/shared-storage/README.md
  module: module-01-warden
  reads:
    - docs/WARDEN_SHARED_STORAGE_PLAN.md
    - docs/WARDEN_ESTABLISHMENT_POAM.md
---

# Warden Shared Storage Runbook

This folder owns the Warden shared storage foundation.

## Current Service

| Field | Value |
|---|---|
| Instance | `warden-shared-storage-01` |
| CTID/VMID | `117` |
| Backing host | `server1` / `host.us-wi.foundation-01` |
| Backing Proxmox storage | `local-lvm` |
| Data allocation | `400 GiB` |
| Network | `vmbr1` only |
| IP | `10.0.0.117/24` |
| Server data root | `/srv/warden/storage` |
| Server/devstation/capsule mount | `/workspace/warden-storage` |
| Local WSL mount | `/mnt/warden/storage` |
| Local WSL symlink | `~/warden-storage` |
| Windows client mount | `W:` after private VPN/WardenNet |
| SMB share | `//10.0.0.117/warden-storage` |

The storage service is internal-only. Do not publish SMB, NFS, or raw storage
ports through Cloudflare, Caddy, OPNsense, or public NAT.

## Verified Clients

Verified on 2026-05-27:

| Client | Status |
|---|---|
| Workstation WSL | mounted at `/mnt/warden/storage`; `~/warden-storage` symlink resolves there |
| Workstation WSL remount | `warden-storage unmount && warden-storage mount` passed through capsule/Infisical broker |
| Shared root project | `/mnt/warden/storage/projects/WardenClyffe-latest`; verify with `git rev-parse --short HEAD`; full SMB status can time out |
| Shared nested Go Warden | `wardenclyffe/`; verify with `git -C wardenclyffe rev-parse --short HEAD`; full SMB status can time out |
| Warden Devstation | mounted at `/workspace/warden-storage`; `warden-storage.service` enabled and active |
| Warden Devstation remount | `systemctl restart warden-storage.service` passed through restricted capsule broker |
| Warden Operator Capsule | no kernel mount; brokered `smbclient` read verified for `projects/WardenClyffe-latest/AGENTS.md`; forced broker command serves devstation remounts |

`gh auth status` on `warden-devstation-01` still reports not logged in. Treat
GitHub write/push operations from devstation as blocked until the operator
authenticates `gh` there.

## Preferred Shape

The bootstrap shape is a dedicated storage LXC:

```text
server1 local-lvm
  -> CT 117 warden-shared-storage-01
  -> 400 GiB mount point
  -> /srv/warden/storage
  -> private CIFS/SFTP exports
  -> capsule/devstation/main Clyffy clients
```

This keeps the Proxmox host boring. The host provides storage and
virtualization; the storage container provides file services. If NFSv4 becomes
required before server2 is online, promote this boundary to a small VM.

## Client Order

1. Use the shared project path for migration:
   `/mnt/warden/storage/projects/WardenClyffe-latest`.
2. Use `warden-devstation-01` for daily coding workflows against
   `/workspace/warden-storage/projects/WardenClyffe-latest`.
3. Use brokered `smbclient`/`rsync` from `warden-operator-capsule` for
   secret-sensitive operator workflows.
4. Mount from main Clyffy after its role is stable.
5. Mount from native Windows as `W:` only after WardenNet/WireGuard or another
   approved private path exists.

The capsule is an unprivileged LXC. If kernel CIFS mounting remains blocked,
use secret-brokered `smbclient` or `rsync` from the capsule until the operator
capsule is promoted to a VM or another approved storage-client shape.

## Preflight

From a Linux operator shell with server1 SSH access:

```bash
scripts/storage/preflight-warden-shared-storage-01.sh server1
```

The preflight is read-only. It verifies storage capacity, CTID/VMID
availability, and current guest state. It must pass before any disk/storage
write action.

## Local Client Command

Use a boring command shape for workstation WSL access:

```bash
warden-storage status
warden-storage mount
warden-storage path
warden-storage unmount
```

The human-facing command should broker the SMB credential from the capsule and
Infisical, write only a temporary CIFS credentials file under
`/run/warden-secrets`, and avoid printing the secret. The root-only stdin helper
is an internal implementation detail, not the command operators should run.

Install the command from the repo:

```bash
sudo scripts/storage/install-warden-storage-client.sh local-wsl
sudo scripts/storage/install-warden-storage-client.sh devstation
```

The installer writes `/etc/warden/storage-client.env`. That file is client
identity and path config only; it must not contain passwords or tokens.

Devstation uses a dedicated SSH key named
`~/.ssh/warden-storage-broker_ed25519` against capsule. Capsule restricts that
key with `from="10.0.0.116"`, `restrict`, and the forced command
`/home/wardenop/bin/warden-storage-secret-broker`. The accepted original
command is `warden-storage-read-secret`; a plain manual SSH is rejected.

## Naming

Use these names unless a later Warden registry entry supersedes them:

- Service: `warden-shared-storage-01`
- Storage id: `storage.us-wi.foundation-01.hot-01`
- Server/devstation/capsule mount: `/workspace/warden-storage`
- Local WSL mount: `/mnt/warden/storage`
- Local WSL symlink: `~/warden-storage`
- Windows drive: `W:` after private network routing
- Host data root: `/srv/warden/storage`
