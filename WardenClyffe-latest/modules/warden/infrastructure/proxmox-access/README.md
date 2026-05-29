---
wardenclyffe_touchpoint:
  version: 1
  kind: proxmox-access-contract
  namespace: wardenclyffe.warden.proxmox.access
  owner: modules/warden/infrastructure/proxmox-access/README.md
  module: module-01-warden
---

# Proxmox Access Contract

This folder documents how Warden accesses Proxmox. It must never contain token
values, passwords, cookies, private keys, or live secret responses.

## Required Environment

Warden-compatible Proxmox access uses:

```text
PROXMOX_HOST
PROXMOX_PORT
PROXMOX_NODE
PROXMOX_TOKEN_ID
PROXMOX_TOKEN_SECRET
PROXMOX_VERIFY_TLS
```

Only these values are safe to show in logs or docs:

```text
PROXMOX_HOST
PROXMOX_PORT
PROXMOX_NODE
PROXMOX_VERIFY_TLS
```

Do not print:

```text
PROXMOX_TOKEN_ID
PROXMOX_TOKEN_SECRET
```

Token ID may not be as sensitive as token secret, but treating both as hidden
keeps the workflow simple and safe.

## Secret Source

Runtime source of truth:

```text
Infisical Cloud -> clyffy-go secrets sync -> OS keyring
```

The code-level SSOT for that runtime path is:

```text
E:\dev\clyffy-go\sdk\secrets
E:\dev\clyffy-go\sdk\secrets\infisical
E:\dev\clyffy-go\sdk\secrets\keyring
E:\dev\clyffy-go\sdk\secrets\sync
```

Provider sources are represented by the SDK as:

```text
SourceInfisical
SourceKeyring
SourceCache
SourceEnv
SourceVault
```

The OS keyring service name convention is:

```text
clyffy:<variant-id>
```

Dev fallback source:

```text
E:\dev\clyffy\secrets\.env.proxmox
```

Generic repo fallback:

```text
WARDENCLYFFE_SECRETS_DIR
<repo-parent>/secrets/.env*
<repo>/secrets/.env*
<repo>/.env
```

Acceptable file patterns inside that directory:

```text
proxmox.env
warden.env
foundation-01.proxmox.env
cisco-ucs-c240-m5-01.proxmox.env
.env.proxmox
```

Files should contain environment assignments only. Example with placeholders:

```env
PROXMOX_HOST=10.0.0.1
PROXMOX_PORT=8006
PROXMOX_NODE=server1
PROXMOX_TOKEN_ID=<redacted>
PROXMOX_TOKEN_SECRET=<redacted>
PROXMOX_VERIFY_TLS=false
```

## First Probe

The first allowed operation is read-only:

```text
GET https://<host>:<port>/api2/json/version
```

If that succeeds, the next read-only probes are:

```text
GET /nodes
GET /cluster/resources?type=vm
GET /nodes/{node}/status
GET /nodes/{node}/storage
```

No write action should run until the read-only inventory is visible in Warden
and the host identity is confirmed.

## Local Helper

Use the repo helper to check readiness without printing secrets:

```powershell
.\scripts\check-proxmox-access.ps1 -EnvFile "$env:WARDENCLYFFE_SECRETS_DIR\proxmox.env"
```

On this workstation, the current dev fallback is:

```powershell
.\scripts\check-proxmox-access.ps1 -EnvFile "E:\dev\clyffy\secrets\.env.proxmox"
```

When ready for a read-only version probe:

```powershell
.\scripts\check-proxmox-access.ps1 -EnvFile "$env:WARDENCLYFFE_SECRETS_DIR\proxmox.env" -Probe
```

or, using the current dev fallback:

```powershell
.\scripts\check-proxmox-access.ps1 -EnvFile "E:\dev\clyffy\secrets\.env.proxmox" -Probe
```

## Warden UI First Slice

The first UI update should add or improve:

- Proxmox connection state.
- host name and region.
- Proxmox version.
- node status.
- VM/LXC counts.
- storage usage.
- last probe time.
- safe next action.
