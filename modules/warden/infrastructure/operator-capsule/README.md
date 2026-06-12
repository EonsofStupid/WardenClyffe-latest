---
wardenclyffe_touchpoint:
  version: 1
  kind: operator-capsule-runbook
  namespace: wardenclyffe.warden.operator_capsule.runbook
  owner: modules/warden/infrastructure/operator-capsule/README.md
  module: module-01-warden
  reads:
    - docs/WARDEN_OPERATOR_CAPSULE.md
    - docs/WARDEN_ESTABLISHMENT_POAM.md
---

# Warden Operator Capsule Runbook

Use this runbook for the permanent Linux-first Warden operator workspace.

## Login

From Windows Terminal, Cursor, VS Code, or another SSH client:

```bash
ssh capsule.clyffy.ai
cd /workspace/WardenClyffe-latest
warden-capsule-status
```

Compatibility alias:

```bash
ssh warden-capsule
```

PowerShell is only a launcher/bridge for live Warden infrastructure work. Do
the actual operator work from inside `warden-capsule`.

## Headless Agent Launch

Use these local launchers when you want the desktop to be only the client while
Claude Code or Codex runs inside the capsule:

```cmd
scripts\local\open-warden-capsule-claude.cmd
scripts\local\open-warden-capsule-codex.cmd
```

Equivalent manual commands:

```bash
ssh capsule.clyffy.ai -t "cd /workspace/WardenClyffe-latest && claude"
ssh capsule.clyffy.ai -t "cd /workspace/WardenClyffe-latest && codex"
```

`capsule.clyffy.ai` is an SSH config alias. It does not publish
`10.0.0.114` in public DNS. The public hop is `ssh.clyffy.ai`; the private hop
is LXC `114`.

## Agent Auth

Authenticate tools from inside the capsule:

```bash
codex
claude
gh auth login
infisical login
```

Use browser or device-code flows. Do not paste API tokens into chat, Markdown,
shell history, or repo files.

## Secret Files

Secrets should enter the capsule as files in tmpfs:

```bash
printf '%s' "$VALUE_FROM_APPROVED_SOURCE" | warden-secret-write example-name
warden-secret-list
warden-secret-path example-name
warden-secret-remove example-name
```

The helper commands print paths, sizes, modes, and counts. They do not print
secret values by default.

For rare break-glass viewing:

```bash
warden-secret-session
warden-secret-breakglass-cat example-name
```

Break-glass sessions should be followed by rotation when the secret is
bootstrap-critical or long lived.

## Session Cleanup

Before closing a secret-handling session:

```bash
warden-secret-list
warden-secret-remove example-name
history -c || true
```

Restarting the capsule clears `/run/warden-secrets` because it is tmpfs.

## Current Boundary

The capsule does not currently have a clean restricted SSH path back to
`server1`. The interrupted root-authorized key was removed. Add a future
capsule-to-host path only as a sudo-limited Warden operator account with
approved commands and audit.
