# Clyffe Code Local Editor Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make Clyffe Code feel like a normal local desktop coding app while Warden runs the workspace on a managed remote devstation.

**Architecture:** The local client path uses official VS Code or Cursor with Remote-SSH into a Warden-managed Linux VM. Public DNS names only the public jump or edge layer; private workspaces are reached through SSH config, WardenNet, or internal DNS so private VM addresses are not published publicly.

**Tech Stack:** Warden Devstation VM, OpenSSH, VS Code/Cursor Remote-SSH, Cloudflare DNS for public records, PowerDNS/OPNsense for private records, future Clyffe Connect desktop client.

---

## Naming Model

| Name | Layer | Target | Status |
|---|---|---|---|
| `devstation.clyffy.ai` | local SSH alias | `10.0.0.116` through `server1` ProxyJump | configured locally |
| `warden-devstation.clyffy.ai` | local SSH alias | `10.0.0.116` through `server1` ProxyJump | configured locally |
| `code.devstation.clyffy.ai` | local SSH alias with tunnel | `10.0.0.116:8080` to local `127.0.0.1:18080` | configured locally |
| `ssh.clyffy.ai` | Cloudflare public DNS-only A record | `104.176.44.101` | live; DNS-only, non-proxied |
| `*.preview.clyffy.ai` | Cloudflare public DNS-only A record | `104.176.44.101` | later, after Warden edge routing exists |
| `devstation.clyffy.ai` | private DNS | `10.0.0.116` | later, after WardenNet/OPNsense split DNS |

Public Cloudflare must not publish `10.0.0.116`. That address belongs to the private `vmbr1` network.

## Task 1: Local Editor Launchers

**Files:**
- Create: `scripts/local/open-warden-devstation-vscode.cmd`
- Create: `scripts/local/open-warden-devstation-cursor.cmd`
- Create: `scripts/local/open-warden-devstation-browser-ide.cmd`
- Create: `.vscode/extensions.json`

- [x] **Step 1: Add VS Code launcher**

```cmd
@echo off
setlocal
set TARGET=ssh-remote+devstation.clyffy.ai
set WORKSPACE=/workspace/WardenClyffe-latest
code --remote %TARGET% %WORKSPACE%
```

- [x] **Step 2: Add Cursor launcher**

```cmd
@echo off
setlocal
set TARGET=ssh-remote+devstation.clyffy.ai
set WORKSPACE=/workspace/WardenClyffe-latest
cursor --remote %TARGET% %WORKSPACE%
```

- [x] **Step 3: Add browser IDE fallback launcher**

```cmd
@echo off
setlocal
start "Warden Devstation Code Tunnel" ssh -N code.devstation.clyffy.ai
timeout /t 2 /nobreak >nul
start "" "http://127.0.0.1:18080/?folder=/workspace/WardenClyffe-latest"
```

- [x] **Step 4: Add workspace extension recommendations**

```json
{
  "recommendations": [
    "ms-vscode-remote.remote-ssh",
    "ms-vscode-remote.remote-ssh-edit",
    "rust-lang.rust-analyzer",
    "golang.go",
    "ms-python.python",
    "redhat.vscode-yaml",
    "tamasfe.even-better-toml"
  ]
}
```

- [x] **Step 5: Verify VS Code opens the remote workspace**

Run:

```cmd
scripts\local\open-warden-devstation-vscode.cmd
```

Expected:

```text
VS Code opens a Remote-SSH window connected to devstation.clyffy.ai and folder /workspace/WardenClyffe-latest.
```

- [x] **Step 6: Verify Cursor opens the remote workspace**

Run:

```cmd
scripts\local\open-warden-devstation-cursor.cmd
```

Expected:

```text
Cursor opens a Remote-SSH window connected to devstation.clyffy.ai and folder /workspace/WardenClyffe-latest.
```

## Task 2: Friendly SSH Names

**Files:**
- Modify: local operator SSH config at `%USERPROFILE%\.ssh\config`

- [x] **Step 1: Configure local-friendly devstation aliases**

```sshconfig
Host warden-devstation devstation.clyffy.ai warden-devstation.clyffy.ai
  HostName 10.0.0.116
  User wardenop
  IdentityFile ~/.ssh/warden_foundation_01_ed25519
  IdentitiesOnly yes
  ProxyJump server1
  StrictHostKeyChecking accept-new
```

- [x] **Step 2: Configure browser IDE tunnel aliases**

```sshconfig
Host warden-devstation-code code.devstation.clyffy.ai code.warden-devstation.clyffy.ai
  HostName 10.0.0.116
  User wardenop
  IdentityFile ~/.ssh/warden_foundation_01_ed25519
  IdentitiesOnly yes
  ProxyJump server1
  StrictHostKeyChecking accept-new
  LocalForward 127.0.0.1:18080 127.0.0.1:8080
  ExitOnForwardFailure yes
  ServerAliveInterval 5
  ServerAliveCountMax 3
```

- [x] **Step 3: Verify friendly SSH alias**

Run:

```bash
ssh devstation.clyffy.ai "hostname"
```

Expected:

```text
warden-devstation-01
```

## Task 3: Cloudflare Public Jump Record

**Files:**
- Create: `scripts/dns/upsert-cloudflare-a-record.sh`

- [x] **Step 1: Add Linux-first Cloudflare A-record helper**

The helper reads `CLOUDFLARE_API_TOKEN`, prints token presence only, and supports dry-run by default.

- [x] **Step 2: Smoke-test the helper from the devstation**

Run:

```bash
bash /tmp/upsert-cloudflare-a-record.sh \
  --zone-id 40bb8e4477b430c77dbb6c81b3fb6e5f \
  --name ssh.clyffy.ai \
  --target 104.176.44.101
```

Expected:

```json
{
  "action": "dry-run",
  "record_name": "ssh.clyffy.ai",
  "target_ip": "104.176.44.101",
  "proxied": false,
  "token_present": false
}
```

- [x] **Step 3: Authenticate Cloudflare DNS mutation path**

Use one of these approved paths:

```text
Cloudflare connector auth in Codex
```

or:

```text
Infisical-brokered CLOUDFLARE_API_TOKEN inside warden-capsule or warden-devstation
```

Expected:

```text
No token value is printed to chat, shell history, committed docs, or logs.
```

- [x] **Step 4: Create `ssh.clyffy.ai` public DNS record**

Run from a Linux operator workspace with `CLOUDFLARE_API_TOKEN` set:

```bash
scripts/dns/upsert-cloudflare-a-record.sh \
  --zone-id 40bb8e4477b430c77dbb6c81b3fb6e5f \
  --name ssh.clyffy.ai \
  --target 104.176.44.101 \
  --apply
```

Expected:

```json
{
  "success": true,
  "name": "ssh.clyffy.ai",
  "type": "A",
  "content": "104.176.44.101",
  "proxied": false
}
```

Verified:

```text
Infisical Clyffy project root key: cloudflare_api_key
Cloudflare record id: 605ae29461a8db03d11bbe893e7e4974
Resolve-DnsName @1.1.1.1: ssh.clyffy.ai -> 104.176.44.101
dig @1.1.1.1: ssh.clyffy.ai -> 104.176.44.101
```

## Task 4: WardenNet Private DNS

**Files:**
- Modify later: `modules/warden/infrastructure/devstation/hosts/warden-devstation-01.yaml`
- Modify later: PowerDNS/OPNsense private zone config through Warden

- [ ] **Step 1: Add private split-DNS intent to Warden inventory**

Add this record shape to the devstation host descriptor after WardenNet is the active private access path:

```yaml
private_dns:
  zone: clyffy.ai
  records:
    - name: devstation.clyffy.ai
      type: A
      content: 10.0.0.116
    - name: code.devstation.clyffy.ai
      type: A
      content: 10.0.0.116
```

- [ ] **Step 2: Reconcile private DNS through Warden**

Run the future Warden DNS reconciler and verify:

```bash
dig @10.0.0.109 +short devstation.clyffy.ai A
dig @10.0.0.109 +short code.devstation.clyffy.ai A
```

Expected:

```text
10.0.0.116
10.0.0.116
```

## Task 5: Clyffe Connect MVP

**Files:**
- Create later: `modules/clyffe/interfaces/desktop-client/README.md`
- Create later: `modules/clyffe/bounded-contexts/code-workspaces/README.md`
- Create later: `modules/warden/bounded-contexts/devstations/README.md`

- [ ] **Step 1: Define the MVP client contract**

The first desktop client must provide these actions:

```text
Sign in
List workspaces
Start workspace
Open in VS Code
Open in Cursor
Open browser IDE fallback
Show resource tier
Request upgrade
Stop workspace
```

- [ ] **Step 2: Keep customer workspaces tenant-scoped**

The MVP must enforce:

```text
No direct Proxmox access
No operator secrets
No public workspace bind by default
All writes go through Warden approvals/tasks/audit
```
