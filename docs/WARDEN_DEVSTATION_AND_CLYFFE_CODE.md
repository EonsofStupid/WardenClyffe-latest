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
| User | `wardenop` |
| OS | Ubuntu Server 26.04 LTS cloud image |
| CPU | 8 vCPU, `host` CPU type |
| Memory | 16 GiB, balloon floor 8 GiB |
| Disk | 160 GiB on `local-lvm` |
| Workspace | `/workspace/WardenClyffe-latest` |
| Status helper | `warden-devstation-status` |
| Initial snapshot | `initial-devstation-toolchain-20260522` |

Only SSH and local resolver ports should listen by default. VS Code or Cursor
should connect over Remote-SSH; a browser IDE must not be exposed publicly.

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

## Connection Model

Use the local desktop only as the client:

```bash
ssh warden-devstation
cd /workspace/WardenClyffe-latest
warden-devstation-status
```

For VS Code or Cursor:

```text
Remote-SSH target: warden-devstation
Workspace: /workspace/WardenClyffe-latest
```

This is private hosted coding without a public web IDE.

## Template Direction

The service template should become:

```text
clyffe-code-workspace-template
  -> private network only
  -> OIDC/device enrollment through Clyffe Connect
  -> per-user workspace
  -> per-workspace resource limits
  -> no direct Proxmox access
  -> no inherited operator secrets
  -> Warden-owned lifecycle, backup, snapshot, audit
```

Keep the first instance as `warden-devstation-01`; do not convert it into a
Proxmox template until the workflow is proven. When ready, create a clean
template VM that has no personal auth state and no repo secrets.

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
3. Add Warden inventory records for devstation instances and future Clyffe Code
   workspaces.
4. Decide whether customer workspaces use Remote-SSH, code-server,
   OpenVSCode Server, or a WardenClyffe-native client after identity and
   network policy are in place.
5. Add snapshot and backup policy before treating the devstation as durable
   work storage.
