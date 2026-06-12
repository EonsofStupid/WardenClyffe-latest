# secrets/

Project-root home for environment/credential files on the Warden Devstation
(`warden-devstation-01`, 10.0.0.116). **Everything in this directory except this
README and `*.example` files is git-ignored** (see root `.gitignore`).

> There is currently **no `.env` on this VM that contains secret *values*.**
> The real secrets live in **Infisical** and are materialized at runtime to the
> root-only tmpfs `/run/warden-secrets/` (currently empty). This directory holds
> the **config** envs + templates so we stop re-discovering them.

## Files

| File | Source | Contains secrets? |
|------|--------|-------------------|
| `warden-storage-client.env` | copy of `/etc/warden/storage-client.env` | No — config only |
| `proxmox.env` (you create from `.example`) | Infisical / operator | **Yes** — keep local only |

## W drive (shared storage) — how it is actually backed

The W drive is a **SMB share**, brokered, with the password in Infisical:

```
SMB server     : 10.0.0.117   share: warden-storage
mount path     : /workspace/warden-storage
broker         : warden-storage-broker (10.0.0.114, ssh-forced-command)
Infisical proj : 4a897376-3cbd-4aeb-8550-c7d3ed7ad113   env: dev
Infisical path : /warden/shared-storage/01
secret name    : WARDEN_SHARED_STORAGE_01_SMB_PASSWORD
runtime cred   : /run/warden-secrets/warden-shared-storage-01.smb (root-only tmpfs)
```

## Proxmox access — secret references

```
PROXMOX_HOST=10.0.0.1  PROXMOX_PORT=8006  PROXMOX_NODE=server1
PROXMOX_TOKEN_ID / PROXMOX_TOKEN_SECRET  -> Infisical (not on disk)
```

Read-only first probe: `GET https://10.0.0.1:8006/api2/json/version`.

## To make secrets available locally

1. Authenticate Infisical on this devstation: `infisical login`
   (project id above, env `dev`), **or**
2. Drop a populated `proxmox.env` here (copy `proxmox.env.example`).
