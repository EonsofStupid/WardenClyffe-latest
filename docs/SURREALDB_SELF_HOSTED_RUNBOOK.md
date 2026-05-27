---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: surrealdb-self-hosted-control
  persona: clyffy-operator
  kind: surrealdb-self-hosted-runbook
  owner: docs/SURREALDB_SELF_HOSTED_RUNBOOK.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/SURREALDB_PUBLIC_SELF_HOSTING_PLAN.md
    - wardenclyffe/docs/cheatsheets/surrealdb-v3.0.5-master.md
    - wardenclyffe/docs/decisions/0028-surreal-cloud-canonical-structure.md
    - wardenclyffe/docs/specs/surreal-cloud-canonical-structure.md
    - ops/surrealdb/warden-surreal-start
    - ops/surrealdb/warden-surreal-export-local
  sync:
    qdrant: true
    surreal: true
---

# SurrealDB Self-Hosted Runbook

This is the operational runbook for LXC `104`, the self-hosted WardenClyffe
SurrealDB instance.

## Current State

Verified on 2026-05-23:

- Host: LXC `104`, hostname `surreal`.
- Version: `3.0.5 for linux on x86_64`.
- Service: `surrealdb.service`, active and enabled.
- Bind: `10.0.0.104:8000`.
- Public `104.176.44.101:8000`: closed from this workstation.
- Persistent path: `SURREAL_PATH=surrealkv:///var/lib/surrealdb`.
- Start wrapper: `/usr/local/sbin/warden-surreal-start`.
- Local export timer: `warden-surreal-export-local.timer`, enabled daily at
  `03:25 UTC` with randomized delay.
- Backup root: `/var/lib/surrealdb/backups`.

## Critical Fix Applied

The old service command placed `surrealkv:///var/lib/surrealdb` after
`--allow-net`. In SurrealDB v3, `--allow-net` accepts optional values, so the
storage URL can be interpreted as a capability value instead of the datastore
path. The safe pattern is to set `SURREAL_PATH` explicitly and omit the
positional storage argument.

The service also now reads `/etc/surrealdb/surreal-root.env` through a wrapper
instead of putting the password directly in `ExecStart`.

## Backup And Restore State

Good pre-fix export used for restore:

- `/var/lib/surrealdb/backups/20260523T051104Z`

Verified post-restore export:

- `/var/lib/surrealdb/backups/20260523T051629Z`
- `clyffy.ai_memory`: `436227` bytes compressed
- `clyffy.knowledge_base`: `1592` bytes compressed
- `clyffy.persona`: `1263` bytes compressed
- `wardenclyffe.ai_memory`: `5648` bytes compressed
- `wardenclyffe.main`: `76` bytes compressed

Ignore the tiny `20260523T051500Z` export. It was captured after the service
restart exposed the storage-path bug and before the restore completed.

## Installed Files

Repo templates:

- `ops/surrealdb/warden-surreal-start`
- `ops/surrealdb/warden-surreal-export-local`

Live installed files:

- `/usr/local/sbin/warden-surreal-start`
- `/usr/local/sbin/warden-surreal-export-local`
- `/etc/systemd/system/surrealdb.service`
- `/etc/systemd/system/warden-surreal-export-local.service`
- `/etc/systemd/system/warden-surreal-export-local.timer`

## Operator Checks

```bash
ssh server1 "pct exec 104 -- systemctl is-active surrealdb"
ssh server1 "pct exec 104 -- /usr/local/bin/surreal isready --endpoint http://10.0.0.104:8000"
ssh server1 "pct exec 104 -- systemctl status warden-surreal-export-local.timer --no-pager"
ssh server1 "pct exec 104 -- journalctl -u warden-surreal-export-local.service -n 20 --no-pager"
```

## Cloud Export Gate

Infisical access from `capsule.clyffy.ai` can resolve the current `SURREAL_*`
keys under the Clyffy project path. The Surreal Cloud endpoint did not accept
CLI traffic on 2026-05-23:

- HTTPS health returned `503 Service Unavailable`.
- WebSocket returned `403 Forbidden`.

Do not claim cloud export/import complete until the cloud instance responds.
After the cloud endpoint is resumed or corrected, export cloud databases to the
capsule first, hash them, copy them to LXC `104`, and import into staging
namespaces/databases before touching production namespaces.

## Cloud To Self-Hosted Import Order

1. Confirm cloud instance is active from Surrealist or SurrealDB Cloud.
2. Export each cloud-primary namespace/database to `/workspace/private-exports`
   on `capsule.clyffy.ai`.
3. Store SHA-256 manifests beside every export.
4. Copy exports to `/var/lib/surrealdb/imports/<timestamp>/` on LXC `104`.
5. Import into staging targets first, for example
   `cloud_import_staging.projects_devpulse`.
6. Compare table counts and critical rows.
7. Take a fresh local backup.
8. Promote only with an operator-visible Warden task.

## Next Hardening

- Replace broad `--allow-funcs` and `--allow-net` with a precise capability
  allow-list after schema/function audit.
- Move public access through Caddy, Authentik, and Warden policy proxy only.
- Add Warden UI panels for version, backup age, bind address, public exposure,
  and cloud export/import status.
