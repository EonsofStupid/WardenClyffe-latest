---
wardenclyffe_touchpoint:
  version: 1
  kind: operator-access-contract
  namespace: wardenclyffe.warden.operator_access
  owner: modules/warden/infrastructure/operator-access/README.md
  module: module-01-warden
---

# Operator Access Contract

This folder documents the host-local access path Warden uses when Proxmox API
coverage is not enough and an operator-approved host command is required.

Examples: `pct`, `qm`, `pvesh`, `pveum`, `pvesm`, `vzdump`, firewall/NAT
inspection, and one-time bootstrap operations.

## Rule

Warden should prefer the Proxmox API for normal inventory and task workflows.
SSH is the host-local helper path, not the primary product API.

Host-local SSH access must be:

- pinned to a verified host fingerprint.
- represented by a non-secret host profile.
- backed by a dedicated key reference.
- auditable before it becomes a Warden write action.
- limited to operator-approved workflows until Warden has policy enforcement.

## Local Bootstrap Helper

Use:

```powershell
.\scripts\setup-warden-host-ssh.ps1 -ExpectedFingerprint "SHA256:vSWJ9KW9M+1w2my9hlCsmpIRnCgk7ClMjmVCiJL39Xc"
```

That command is a dry run. It captures the offered SSH host key into a temporary
known_hosts file and compares it to the expected fingerprint.

To write the local host trust and SSH config after the dry run matches:

```powershell
.\scripts\setup-warden-host-ssh.ps1 `
  -ExpectedFingerprint "SHA256:vSWJ9KW9M+1w2my9hlCsmpIRnCgk7ClMjmVCiJL39Xc" `
  -Apply `
  -WriteConfig `
  -EnsureKey
```

`-EnsureKey` creates a local private key if it does not exist. The private key
must never be pasted into chat or committed. The public key is safe to paste
into the Proxmox host console for `/root/.ssh/authorized_keys` or a future
non-root sudo operator account.

## Current Host Profiles

- `hosts/foundation-01.yaml`

These profiles are not secret stores. They contain host identity, expected
fingerprints, aliases, and secret references only.
