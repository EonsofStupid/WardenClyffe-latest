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
- Friendly SSH alias: `devstation.clyffy.ai`
- IP: `10.0.0.116`
- Workspace: `/workspace/WardenClyffe-latest`
- Browser IDE alias: `warden-devstation-code`
- Friendly browser IDE alias: `code.devstation.clyffy.ai`
- Browser IDE URL: `http://127.0.0.1:18080/?folder=/workspace/WardenClyffe-latest`

## Daily Use

```bash
ssh warden-devstation
cd /workspace/WardenClyffe-latest
warden-devstation-status
```

Use VS Code or Cursor Remote-SSH with target `warden-devstation` and open
`/workspace/WardenClyffe-latest`.

Prefer the friendlier target in new editor setups:

```text
devstation.clyffy.ai
```

Local launch helpers:

```cmd
scripts\local\open-warden-devstation-vscode.cmd
scripts\local\open-warden-devstation-cursor.cmd
```

For the private hosted browser IDE:

```bash
ssh code.devstation.clyffy.ai
```

Keep that SSH session open, then open
`http://127.0.0.1:18080/?folder=/workspace/WardenClyffe-latest` on the local
desktop.

The hosted editor runs as `code-server@wardenop`, binds only to
`127.0.0.1:8080` inside VM `116`, and is reached through SSH forwarding. Do
not bind it to `0.0.0.0` or route it publicly.

The local fallback helper is:

```cmd
scripts\local\open-warden-devstation-browser-ide.cmd
```

## Status

```bash
warden-devstation-status
warden-devstation-code-status
```

Warden UI should later consume the same facts: unit state, enabled state, bind
address, health endpoint, workspace path, extension inventory, and route
policy.

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
