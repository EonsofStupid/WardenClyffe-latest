---
wardenclyffe_touchpoint:
  version: 1
  kind: warden-devstation
  namespace: wardenclyffe.warden.devstation
  owner: docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
  module: module-01-warden
  reads:
    - docs/WARDEN_ESTABLISHMENT_POAM.md
    - docs/WARDEN_OPERATOR_CAPSULE.md
    - docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md
    - modules/warden/infrastructure/devstation/README.md
---

# Warden Devstation And Clyffe Code

Warden Devstation is the private hosted coding VM for the operator. It is
separate from the Warden Operator Capsule.

Clyffe Code is the future customer-safe hosted coding workspace product that
can be derived from this pattern after tenancy, identity, networking, billing,
backup, and abuse controls are real.

## Product Boundary

| Name | Audience | Purpose |
|---|---|---|
| Warden Operator Capsule | trusted operator | secret-sensitive bootstrap and infra work |
| Warden Devstation | trusted operator | private VS Code/Cursor/Codex/Claude workstation |
| Clyffe Code | future customer | turnkey private coding workspace |

Do not put customers in the operator capsule or operator devstation. Clyffe
Code must be a tenant-scoped product surface through Warden and Clyffe policy.

## Current Verified Devstation

Verified on 2026-05-22:

| Field | Value |
|---|---|
| VMID | `116` |
| Name | `warden-devstation-01` |
| IP | `10.0.0.116/24` |
| Network | `vmbr1`, internal only |
| Public route | none |
| On boot | enabled |
| SSH alias | `warden-devstation` |
| Friendly editor alias | `devstation.clyffy.ai` |
| User | `wardenop` |
| OS | Ubuntu Server 26.04 LTS cloud image |
| CPU | 8 vCPU, `host` CPU type |
| Memory | 16 GiB, balloon floor 8 GiB |
| Disk | 160 GiB on `local-lvm` |
| Workspace | `/workspace/WardenClyffe-latest` |
| Status helper | `warden-devstation-status` |
| Hosted editor helper | `warden-devstation-code-status` |
| Initial snapshot | `initial-devstation-toolchain-20260522` |

Only SSH and local resolver ports should listen publicly on the VM network.
VS Code or Cursor should connect over Remote-SSH. The browser IDE is
`code-server` bound only to VM-local `127.0.0.1:8080` and reached through an
SSH tunnel from the operator desktop. It must not be exposed directly.

## Toolchain

The first devstation includes:

- VS Code/Cursor Remote-SSH prerequisites.
- Codex CLI.
- Claude Code.
- Infisical CLI.
- GitHub CLI.
- Node 24 LTS and npm.
- Rust stable, rustfmt, clippy, rust-analyzer.
- Go.
- Python and uv.
- SOPS and age.
- `rg`, `fd`, `bat`, `tmux`, build tools, and qemu guest agent.
- Private `code-server` hosted editor with Rust, Go, Python, YAML, and TOML
  extensions seeded.

## Shared Storage

The devstation keeps its local workspace at `/workspace/WardenClyffe-latest`.
After `warden-shared-storage-01` is provisioned, mount the Warden shared storage
client path at:

```text
/workspace/warden-storage
```

Use that mount for portable project sync, artifacts, model/dataset caches, and
agent-portable config. Do not move the devstation OS, personal auth state, or
live database primary data onto the shared mount.

## Connection Model

Use the local desktop only as the client:

```bash
ssh warden-devstation
cd /workspace/WardenClyffe-latest
warden-devstation-status
```

For VS Code or Cursor:

```text
Remote-SSH target: devstation.clyffy.ai
Workspace: /workspace/WardenClyffe-latest
```

The legacy alias `warden-devstation` remains valid. The friendly
`devstation.clyffy.ai` target is an SSH config alias that still reaches the
private VM through the existing jump host; it is not a public A record to
`10.0.0.116`.

Local launch helpers:

```cmd
scripts\local\open-warden-devstation-vscode.cmd
scripts\local\open-warden-devstation-cursor.cmd
```

For the private browser IDE:

```bash
ssh code.devstation.clyffy.ai
```

Then open:

```text
http://127.0.0.1:18080/?folder=/workspace/WardenClyffe-latest
```

`code.devstation.clyffy.ai` is an SSH config alias that forwards local desktop
port `18080` to `127.0.0.1:8080` inside VM `116`. The legacy alias
`warden-devstation-code` remains valid. The current browser IDE uses SSH as
the access gate and `auth: none` inside code-server because the service is
bound to VM-local localhost only. Do not change this to a public bind address.

This is private hosted coding without a public web IDE route.

## Extension Fidelity

The primary local-app path is official VS Code or Cursor over Remote-SSH. That
is the full extension-support path. The private `code-server` browser IDE is a
fallback and should be treated as Open-VSX-first, not Microsoft Marketplace
parity.

## Friendly DNS Policy

For the local-editor-first path, use friendly names at the correct layer:

| Name | Layer | Target |
|---|---|---|
| `devstation.clyffy.ai` | SSH config alias now; private DNS later | VM `116` on `10.0.0.116` |
| `code.devstation.clyffy.ai` | SSH config alias now; private DNS later | VM-local code-server through tunnel |
| `ssh.clyffy.ai` | Cloudflare DNS-only public A record | homebase public IP `104.176.44.101` |
| `*.preview.clyffy.ai` | future Cloudflare DNS-only public A record | Warden-managed edge after LXC `115` |

Do not publish private workspace IPs in public Cloudflare DNS. Public DNS
should point only to the Warden-controlled public jump or edge layer. Private
workspace names belong in WardenNet, OPNsense split DNS, or PowerDNS.

`ssh.clyffy.ai` is live as DNS-only and resolves to `104.176.44.101`.
It is not proxied and does not expose the devstation directly.

## Hosted Editor Service

Current service state:

| Field | Value |
|---|---|
| Runtime | `code-server` |
| Version | `4.121.0` with Code `1.121.0` |
| systemd unit | `code-server@wardenop` |
| Service user | `wardenop` |
| VM bind | `127.0.0.1:8080` |
| Local tunnel | `127.0.0.1:18080 -> 127.0.0.1:8080` |
| Public route | none |
| Default folder | `/workspace/WardenClyffe-latest` |
| User data | `/workspace/.warden-code-server/data` |
| Extensions | `/workspace/.warden-code-server/extensions` |
| Health probe | `http://127.0.0.1:8080/healthz` from the VM |

The Warden UI should eventually control this as a managed workspace service:
start, stop, restart, health, extension inventory, snapshot, backup, tunnel
intent, and route policy. It should not manage this by scraping a browser.

## Template Direction

The service template should become:

```text
clyffe-code-workspace-template
  -> private network only
  -> OIDC/device enrollment through Clyffe Connect or WardenNet
  -> per-user workspace
  -> per-workspace resource limits
  -> no direct Proxmox access
  -> no inherited operator secrets
  -> Warden-owned lifecycle, backup, snapshot, audit
```

Keep the first instance as `warden-devstation-01`; do not convert it into a
Proxmox template until the workflow is proven. When ready, create a clean
template VM that has no personal auth state and no repo secrets.

## Tier Direction

The current VM is a Builder-class proof:

```text
8 vCPU, 16 GiB memory, 160 GiB disk
```

The proposed first flagship offer is Premium Pilot:

```text
16 vCPU, 32 GiB memory, 320 GiB disk
```

Do not resize `warden-devstation-01` until the host resource budget, snapshot,
and rollback point are verified. The Wisconsin host is RAM-constrained, so
Premium Pilot may be better placed on the Virginia host after Warden registers
it.

## Naming

Use boring infrastructure names:

- VM instance: `warden-devstation-01`
- Future template: `clyffe-code-workspace-template`
- Future customer product: `Clyffe Code`
- Future private access client: `Clyffe Connect`

Do not use legacy Wolf names.

## Next Hardening

1. Authenticate Codex, Claude, GitHub CLI, and Infisical inside the devstation
   only if needed for daily work.
2. Add WardenNet or WireGuard access so laptops/desktops can reach the
   devstation without using public routes.
3. Add Warden inventory records for the hosted editor service, devstation
   instances, and future Clyffe Code workspaces.
4. Decide whether customer workspaces use Remote-SSH, code-server,
   OpenVSCode Server, or a WardenClyffe-native client after identity and
   network policy are in place.
5. Add snapshot and backup policy before treating the devstation as durable
   work storage.
