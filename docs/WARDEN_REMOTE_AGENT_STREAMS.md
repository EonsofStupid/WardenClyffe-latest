---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: warden-remote-agent-streams
  persona: clyffy-operator
  kind: remote-agent-stream-contract
  owner: docs/WARDEN_REMOTE_AGENT_STREAMS.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - docs/WARDEN_OPERATOR_CAPSULE.md
    - docs/WARDEN_SHARED_STORAGE_PLAN.md
    - docs/WARDEN_CLOUDFLARE_DNS_FOUNDATION.md
    - modules/warden/bounded-contexts/dns/README.md
  sync:
    qdrant: true
    surreal: true
---

# Warden Remote Agent Streams

This is the operating contract for making Codex, Claude Code, and future agent
runtimes feel local while their processes run on Warden-controlled Linux hosts.

## Answer

Yes, Warden can provide a local stream directly connected to the devstation or
capsule, but the foundation has two different modes:

| Mode | Status | Contract |
|---|---|---|
| SSH TTY stream | supported now | local terminal opens SSH; Codex/Claude runs on the remote host |
| SSH + tmux stream | supported now | remote agent session survives local terminal disconnects |
| Codex app-server remote | experimental | do not publish; use only behind Warden tunnel/auth after a hardening pass |
| Browser/editor embedding | future | Warden UI creates audited open-intent tasks, not raw browser scraping |

This Codex Desktop thread still runs with the desktop app's local tool surface.
For live remote operation, use the launchers so the actual `codex` process runs
inside `warden-devstation-01` or `warden-operator-capsule`.

## Current Verified Runtime

Verified on 2026-05-27:

| Target | Alias | Runtime | Workspace |
|---|---|---|---|
| Warden Devstation | `devstation.clyffy.ai` | `codex-cli 0.133.0`, Claude Code `2.1.148`, `tmux 3.6` | `/workspace/warden-storage/projects/shippin-platform` |
| Warden Operator Capsule | `capsule.clyffy.ai` | `codex-cli 0.133.0`, Claude Code `2.1.148`, `tmux 3.5a` | `/workspace/WardenClyffe-latest` |

Launchers:

```cmd
scripts\local\open-warden-devstation-codex.cmd
scripts\local\open-warden-devstation-claude.cmd
scripts\local\open-warden-capsule-codex.cmd
scripts\local\open-warden-capsule-claude.cmd
```

Those launchers call:

```bash
scripts/agents/warden-agent-stream.sh <codex|claude> <devstation|capsule>
```

The script attaches to a named `tmux` session when `tmux` is installed and
falls back to a direct TTY stream when it is not.

The Windows launchers invoke the helper with `bash` rather than relying on the
executable bit. This matters because the SMB-mounted shared workspace can mask
Linux execute permissions with its client `file_mode`.

## DNS And URL Shape

| Name | Public DNS? | Meaning |
|---|---|---|
| `ssh.clyffy.ai` | yes, DNS-only | public jump/homebase entry; points to `104.176.44.101` |
| `devstation.clyffy.ai` | no public private-IP record | SSH alias now; private DNS/WardenNet later |
| `code.devstation.clyffy.ai` | no public private-IP record | SSH tunnel alias for private browser IDE |
| `capsule.clyffy.ai` | no public private-IP record | SSH alias now; private DNS/WardenNet later |
| `operator.clyffy.ai` | no public private-IP record | operator-friendly capsule alias |

Public DNS points only to the Warden-controlled public jump or edge. Private
workspace names are SSH aliases now and become split-horizon records through
OPNsense/PowerDNS/WardenNet later.

## Codex Remote App-Server Gate

The installed Codex CLI exposes experimental remote pieces:

```text
codex app-server --listen ws://IP:PORT
codex --remote ws://host:port
codex remote-control start
```

Do not expose those on public DNS. The Warden acceptance gate is:

- listener bound to loopback or private interface only;
- access through SSH/WardenNet tunnel;
- capability token or signed bearer token stored outside git;
- Warden task/audit record for start, stop, attach, and token rotation;
- no raw secrets in session recording;
- route status represented in the MCP mesh and Warden UI.

Until that gate exists, SSH + tmux is the blessed remote stream.

## Extension Pattern

To add another remote agent runtime or customer-safe workspace:

1. Create a Warden workspace descriptor for the VM/LXC.
2. Add private reachability: SSH alias now, private DNS/WardenNet later.
3. Mount or bridge Warden storage according to `docs/WARDEN_SHARED_STORAGE_PLAN.md`.
4. Install the runtime inside the remote host.
5. Add a launcher through `scripts/agents/warden-agent-stream.sh`.
6. Add a Warden UI open-intent action.
7. Add or update the mesh registry entry in `wardenclyffe/registry/context-mesh.yaml`.
8. Add touchpoint metadata so Qdrant and SurrealDB projections can track the
   workspace, runtime, domain, and last verification.
9. Promote customer use only after identity, tenant scope, quotas, backup, and
   abuse controls exist.

## Warden UI Direction

Warden should eventually replace copy-command launchers with audited actions:

- `agent_stream.open`
- `agent_stream.attach`
- `agent_stream.stop`
- `agent_stream.status`
- `agent_stream.rotate_token`

Those actions belong to Warden task/audit records and are projected to the
MCP/intelligence layer as status, not as handwritten memory.
