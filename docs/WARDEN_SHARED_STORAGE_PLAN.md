---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: warden-shared-storage
  persona: clyffy-operator
  kind: infrastructure-storage-plan
  owner: docs/WARDEN_SHARED_STORAGE_PLAN.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDEN_ESTABLISHMENT_POAM.md
    - docs/HOST_FLEET_AND_ONBOARDING.md
    - modules/warden/infrastructure/shared-storage/README.md
  sync:
    qdrant: true
    surreal: true
---

# Warden Shared Storage Plan

This is the storage boundary for the Warden foundation while server2 is still
being finished.

## Decision

Use a **400 GB Warden hot storage tier on server1** now.

Server1 owns the near-term authoritative shared workspace namespace. The
workstation, Warden Devstation, Warden Operator Capsule, and main Clyffy VM are
clients of that namespace. The workstation external drive must not be treated
as the authority.

When server2 is ready, migrate the storage authority to server2 and keep
server1 as a control-plane/homebase node unless a later capacity review changes
that.

## Current Server1 Storage State

Verified on 2026-05-26:

| Item | Value |
|---|---|
| Host | `server1` |
| Proxmox storage | `local-lvm` |
| Carve | `400 GiB` |
| Service | LXC `117`, `warden-shared-storage-01` |
| Service IP | `10.0.0.117/24` on `vmbr1` |
| Server data root | `/srv/warden/storage` |
| Export | internal SMB/CIFS share `warden-storage` |
| Local WSL mount verified | `/mnt/warden/storage` with `~/warden-storage` symlink |
| Risk class | disk/storage write completed for bootstrap tier; future expansion still requires explicit apply step |
| Preflight | passed on 2026-05-26 before provisioning CTID/VMID `117` |

The 400 GB carve fits the current host, but it is a bootstrap tier. Do not use
it to justify large model hoarding, cold backups, or customer storage promises.

## Verified Client State

Verified on 2026-05-27:

| Client | Result |
|---|---|
| Workstation WSL | `warden-storage status` shows `/mnt/warden/storage` mounted from `//10.0.0.117/warden-storage` with about `371 GiB` available |
| Workstation WSL symlink | `~/warden-storage` resolves to `/mnt/warden/storage` |
| Workstation WSL write test | write/read/delete passed under `scratch/` |
| Shared project path | `/mnt/warden/storage/projects/WardenClyffe-latest` |
| Shared project root commit | verify with `git rev-parse --short HEAD` after each sync |
| Shared nested Go Warden commit | `9d5162f` |
| Shared root Git status | full SMB status timed out after 30s on 2026-05-27; commit and changed-file hashes verified |
| Shared nested Go Git status | full SMB status timed out after 30s on 2026-05-27; commit and changed-file hashes verified |
| Devstation mount | `/workspace/warden-storage` mounted and reads the same project path |
| Capsule access | no kernel mount; brokered `smbclient` can read `projects/WardenClyffe-latest/AGENTS.md` without printing the secret |

## Service

| Field | Value |
|---|---|
| Service name | `warden-shared-storage-01` |
| Warden id | `storage.us-wi.foundation-01.hot-01` |
| CTID/VMID | `117` |
| Backing host | `host.us-wi.foundation-01` |
| Backing storage | Proxmox `local-lvm` |
| Data size | `400 GiB` |
| Network | `vmbr1`, internal only |
| Public route | none |
| Server/capsule/devstation mount path | `/workspace/warden-storage` |
| Local WSL mount path | `/mnt/warden/storage` |
| Local WSL convenience symlink | `~/warden-storage` |
| Server-side data root | `/srv/warden/storage` |
| Windows target | `W:` after private VPN or approved tunnel |

Use a dedicated storage LXC or storage VM boundary rather than installing file
services directly on the Proxmox host. The default implementation target for
the 400 GB bootstrap tier is a small Debian storage LXC because server1 already
has a current Debian template and the service can expose SMB/CIFS and SFTP
without burning VM memory. If NFSv4 becomes mandatory before server2 is ready,
promote the file-service boundary to a small VM.

## Export Policy

| Client | Path | Mode |
|---|---|---|
| Warden Operator Capsule | `/workspace/warden-storage` | internal CIFS first; NFSv4 only if promoted to VM |
| Warden Devstation | `/workspace/warden-storage` | internal CIFS first; NFSv4 only if promoted to VM |
| Main Clyffy VM | `/workspace/warden-storage` | internal CIFS first; NFSv4 only if promoted to VM |
| Workstation WSL | `/mnt/warden/storage` plus `~/warden-storage` symlink | direct private reachability or approved tunnel; no public SMB |
| Workstation Windows | `W:` later | private VPN/WardenNet first; no public SMB |

Do not expose SMB, NFS, or raw storage services publicly. Workstation access
must use private reachability such as WardenNet/WireGuard/Tailscale while
headscale is being established, or an approved SSH tunnel. Remote-SSH into the
devstation remains the clean daily coding path once the project has been synced.
Git-heavy operations on the SMB working tree are now known to be slow enough
to time out; prefer devstation-local clones for heavy branch/status work and
use the share as the project sync/artifact authority until a Warden storage
client replaces raw SMB.

## Directory Layout

```text
/srv/warden/storage/
  projects/
  agent-portable/
  mcp/
  models/
  datasets/
  artifacts/
  exports/
  backups/
  cache/
  scratch/
```

Rules:

- `projects/` holds synced working trees and portable project state.
- `agent-portable/` holds safe per-project agent wrappers and generated config
  templates, not secrets.
- `mcp/` holds portable MCP manifests and generated context packs.
- `models/` and `datasets/` are bounded caches until server2 exists.
- `backups/` is for backups and exports, not live database data directories.
- `cache/` and `scratch/` are disposable.

## What This Storage Must Not Own

- Infisical secrets or raw token material.
- Live Postgres, SurrealDB, or Qdrant primary data directories.
- Customer promises beyond the internal pilot.
- Public shares.
- Workstation-only authority.

Product truth remains in Postgres. Intelligence projection remains in
SurrealDB and Qdrant. Markdown remains the touchpoint layer.

## Server2 Migration Direction

Server2 should become the real storage-heavy node when it is ready.

Operator-reported target hardware:

| Tier | Reported capacity | Intended role |
|---|---:|---|
| NVMe | `6.4 TB` | hot workspaces, model/cache hot tier, high-IO builds |
| SSD | `8 x 1.6 TB` | active VM disks, tenant workspaces, replicated hot/warm data |
| SAS | `6 x 1.6 TB` | backups, archives, cold datasets, bulk artifacts |
| Memory | `384 GB` | larger AI/service consolidation |
| CPU | `96 cores` | Warden/Clyffe compute expansion |

The SAS tier is still useful. Keep latency-sensitive builds, databases, and
active model cache on NVMe/SSD; use SAS for sequential, cold, backup, artifact,
and replication workloads.

## First Implementation Gate

Completed bootstrap gate:

1. Read-only Proxmox/storage inventory passed.
2. CTID/VMID `117` was available.
3. `local-lvm` had sufficient space for the 400 GiB bootstrap carve.
4. LXC `117` was provisioned as `warden-shared-storage-01`.
5. `/srv/warden/storage` was created with the planned directory layout.
6. SMB/CIFS share `warden-storage` was configured internal-only.
7. The WSL workstation mount was verified with write/read/delete at
   `/home/hades/warden-storage`; normalize the final local path to
   `/mnt/warden/storage` before broader sync.

Next implementation gate:

1. Keep the shared project path as the migration authority while local Windows
   and WSL copies are reconciled.
2. Mount or bridge the share to main Clyffy after its role is stable.
3. Keep capsule access secret-safe; if kernel CIFS stays blocked in the
   unprivileged LXC, use brokered `rsync`/`smbclient` until capsule is promoted
   to a VM or another approved storage client shape.
4. Add Windows `W:` only after private VPN/WardenNet routing is established.

The disk carve is an explicit Warden write action. It should create a task,
approval, and audit record once Warden has that workflow.
