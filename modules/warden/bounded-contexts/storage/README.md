---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: storage-bounded-context
  persona: clyffy-operator
  kind: subsystem
  owner: modules/warden/bounded-contexts/storage/README.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/ai/WARDENCLYFFE_OFFICIAL_BASELINE.md
    - docs/WARDEN_DEVSTATION_TURNKEY_WARDENNET_PLAN.md
    - services/storage-broker-client
    - wardenclyffedisk
  sync:
    qdrant: true
    surreal: true
---

# Storage — Bounded Context (managed per-tenant storage product)

The domain that turns a **purchase** into an owned, managed, mappable disk. This
is the product behind the "W drive": user buys storage → a per-tenant volume is
provisioned by code → the customer maps it to their workstation. The old shared
SMB box (`10.0.0.117`) is a stopgap to be retired by this context.

## Reviewed: what already exists

- **Data plane (Rust): `wardenclyffedisk/`** — a real distributed FS: FUSE +
  chunk store + replication (shared/quorum) + an **S3-compatible HTTP gateway**
  (axum) + CLI (mount/status/stats). This is the storage engine. Keep it Rust.
- **Control intent (Go): `services/warden-api/internal/automation`** — already
  does `plan → approve → execute` over `shippin_core.action_requests`; the actual
  resource bring-up is delegated to phase-2 executors.
- **The seam was empty:** `services/provisioner` and `services/storage-broker-client`
  were placeholder dirs. This context fills `storage-broker-client`.

## Architecture (locked)

- **Go = web/control plane. Rust = local/data plane. They blend ONLY over a
  versioned network/CLI contract — never cgo/FFI.** Do not rewrite the disk to Go.
- **Runtime is managed by code + deterministic scripts, NOT AI.** AI is strictly
  a **verification/overseer** surface that raises awareness to the operator.
- Boundary: Clyffe (customer) sees only its own volumes + mount grants; Warden
  (operator) owns lifecycle, quota, audit, backup.

```
purchase (Clyffe/Warden)
  -> storage-broker (Go control)        services/storage-broker-client
       contract.Driver  ──CLI──>  wardenclyffedisk-volume (bash, deterministic)
                                     renders /etc/wardenclyffedisk/<id>.toml
                                     systemctl wardenclyffedisk@<id>   (Rust node)
                                     S3 bucket + mount grant
  -> customer maps volume to workstation (S3 creds now; FUSE next)
  overseer: storage-verify --json  ->  AI raises drift/health to operator
```

## Built this pass (compiles + tested end-to-end)

`services/storage-broker-client/` (Go, builds + vets clean):
- `internal/contract` — the Go↔Rust contract (ContractVersion, `Tier`,
  `Volume`, `MountGrant`, `Driver`, code-owned `TierCatalog`).
- `internal/disk` — `CLIDriver`: deterministic driver shelling to the volume
  script, parsing JSON. No AI.
- `internal/volume` — application service (purchase→reconcile→store) + `Store`
  (memory now; Postgres drops in behind the interface).
- `cmd/storage-broker` — chi HTTP control API: `POST/GET/DELETE /api/storage/volumes`,
  `POST …/{id}/mount-grant`, `/healthz`.
- `bin/wardenclyffedisk-volume` — deterministic lifecycle (provision/status/
  deprovision/grant-mount); renders a valid `config.toml` matching `config.rs`;
  LIVE (systemd) or PLAN (no-node dev) mode.
- `systemd/wardenclyffedisk@.service` — per-tenant node unit, hardened.
- `bin/storage-verify` — the overseer: deterministic health/drift JSON for AI to
  surface; mode-aware (plan vs live).

Verified: `purchase → provision → get → mount-grant → list → deprovision` all
green via the Go API → script → JSON seam.

## POA&M (next, in order)

1. **Persistence** — Postgres `Store` (`clyffe_core`/`shippin_core`) replacing
   memory; volume rows + audit events on every transition.
2. **Real nodes** — install `wardenclyffedisk` + the systemd template on the
   storage host; flip `STORAGE_BROKER_MODE=live`; provision a real volume.
3. **Data client** — Go S3 client (minio-go) for bucket lifecycle + quota
   enforcement against the Rust S3 gateway.
4. **Provisioner** — wire `automation.Provision` → storage-broker on
   `provision_workspace`, so a workspace purchase attaches a volume on Proxmox.
5. **Frontend** — `apps/.../domains/clyffe/storage` (coldlight/RAC/REM) for
   purchase + "map to my workstation" (built this pass; wire to live API).
6. **Retire SMB** — migrate W-drive consumers to per-tenant volumes; remove the
   `10.0.0.117` share.

## Safe for agents / needs approval

- Safe: extend the contract/driver/service, add scripts, run `storage-verify`.
- Approval: enabling LIVE mode on a host, deleting volumes, retiring the SMB box,
  any Proxmox provisioning.
