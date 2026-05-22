---
wardenclyffe_touchpoint:
  version: 1
  kind: devstation-runbook
  namespace: wardenclyffe.warden.devstation.runbook
  owner: modules/warden/infrastructure/devstation/README.md
  module: module-01-warden
  reads:
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - docs/WARDEN_ESTABLISHMENT_POAM.md
---

# Warden Devstation Runbook

This folder documents private hosted coding workstations.

## Current Instance

- VMID: `116`
- Name: `warden-devstation-01`
- SSH alias: `warden-devstation`
- IP: `10.0.0.116`
- Workspace: `/workspace/WardenClyffe-latest`

## Daily Use

```bash
ssh warden-devstation
cd /workspace/WardenClyffe-latest
warden-devstation-status
```

Use VS Code or Cursor Remote-SSH with target `warden-devstation` and open
`/workspace/WardenClyffe-latest`.

## Auth

Authenticate tools from inside the VM as needed:

```bash
codex
claude
gh auth login
infisical login
```

Do not paste API keys into chat, Markdown, shell history, or repo files.

## Service Boundary

This VM is an operator devstation. It is not the customer template yet.

Future Clyffe Code workspaces must be created from a clean template with:

- no personal auth state,
- no operator secrets,
- tenant-scoped identity,
- private network access,
- Warden-owned lifecycle and audit.
