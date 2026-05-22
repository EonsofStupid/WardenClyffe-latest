---
wardenclyffe_touchpoint:
  version: 1
  kind: warden-operator-capsule
  namespace: wardenclyffe.warden.operator_capsule
  owner: docs/WARDEN_OPERATOR_CAPSULE.md
  module: module-01-warden
  reads:
    - docs/WARDEN_ESTABLISHMENT_POAM.md
    - docs/HOST_FLEET_AND_ONBOARDING.md
    - modules/warden/infrastructure/operator-capsule/README.md
    - modules/warden/infrastructure/operator-access/README.md
---

# Warden Operator Capsule

The Warden Operator Capsule is the contained shell/workspace where a trusted
operator agent can perform sensitive infrastructure work without spreading
secrets across the normal workstation, repo, chat, or public logs.

This is the right answer to: "Can Codex work with sensitive information while
keeping it contained?"

Yes, with limits. A model or human that can see a secret can still leak it by
mistake. The capsule reduces blast radius and persistence; it does not make raw
secret exposure magically safe. The policy remains: view secrets only when the
task truly requires it, avoid printing them, rotate after major bootstrap, and
prefer short-lived credentials.

## Current Verified Capsule

```text
warden-operator-capsule
```

Verified on 2026-05-22:

| Field | Value |
|---|---|
| VMID | `114` |
| Type | unprivileged Debian LXC |
| Network | `vmbr1`, internal only |
| IP | `10.0.0.114/24` |
| Public route | none |
| On boot | enabled |
| SSH alias | `warden-capsule` |
| Primary user | `wardenop`, sudo by approved command group only |
| Secret mount | `/run/warden-secrets` on tmpfs |
| Working tree | `/workspace/WardenClyffe-latest` |
| Private exports | `/workspace/private-exports`, gitignored |
| Agent CLIs | `codex`, `claude`, `infisical` installed under `wardenop` |
| Baseline status | `warden-capsule-status` |

Windows PowerShell is now a launcher/bridge only for Warden live infrastructure
work. The permanent operator shell is:

```bash
ssh warden-capsule
cd /workspace/WardenClyffe-latest
```

## What Smart Teams Use

The common pattern across serious infra teams is:

| Pattern | Why it matters here |
|---|---|
| Privileged access workstation or jump host | Sensitive work happens from a hardened, purpose-built place |
| Access broker such as Boundary or Teleport | JIT access, identity policy, and session recording for SSH/database/app access |
| Dynamic secrets through Infisical, Vault, or OpenBao | Credentials are generated on demand, scoped, and expire automatically |
| Dev container or tool capsule | Reproducible tooling without polluting the normal workstation |
| File-mounted secrets | Prefer `/run/secrets/*` or `/run/warden-secrets/*` over `.env` and process environment dumps |
| Workload identity such as SPIFFE/SPIRE later | Services prove who they are without long-lived shared tokens |
| SOPS/age for committed encrypted config later | Repo can carry encrypted config without plaintext secrets |

## Capsule Controls

### Network

- Internal `vmbr1` only.
- No public DNS record.
- No inbound route from Cloudflare/Caddy.
- Allow egress only to required systems:
  - Proxmox host/internal services.
  - Infisical or future secret broker.
  - Cloudflare API when DNS changes are approved.
  - Git remotes.
  - package mirrors.

### Secrets

- Canonical source remains Infisical Cloud and the Go SDK keyring path.
- Capsule can pull secrets into `/run/warden-secrets` as files.
- Secrets should be mode `0600`, owned by `wardenop`.
- Prefer dynamic/short-lived credentials where supported.
- Avoid exporting secrets as environment variables except for short command
  wrappers that immediately exec and unset.
- Do not write plaintext secrets into the repo working tree.

### Shell

- Disable persistent shell history for secret-view sessions:

```bash
export HISTFILE=/dev/null
set +o history
export WARDEN_SECRET_VIEW_ALLOWED=1
```

- Default helpers print paths, status, sizes, modes, and counts, not secret
  values.
- Commands that may display secrets must use an explicit marker:

```bash
export WARDEN_SECRET_VIEW_ALLOWED=1
```

### Storage

- `/run/warden-secrets` is tmpfs and disappears on restart.
- `/workspace/private-exports` is for intentional private archives only.
- Repo `.gitignore` must exclude private exports and secret paths.
- Any private export kept longer than bootstrap should be encrypted or moved to
  the secret backup location.

### Audit

Use two audit modes:

| Mode | Use |
|---|---|
| Metadata audit | default; command category, target, files touched, hashes, success/fail |
| Full session recording | for normal infra work where raw secrets are not printed |

Do not intentionally record a raw secret-view session unless the recording
backend is encrypted and treated as secret material too.

## Warden Policy

Warden should model the capsule as a host capability:

```text
operator_capsule
  -> can read secret references
  -> can run approved host-local helpers
  -> can produce private exports
  -> can propose live writes
  -> cannot publish public routes without approval
  -> cannot commit secret paths
```

## First Implementation Slice

Completed on 2026-05-22:

1. Provisioned LXC `114` as `warden-operator-capsule`.
2. Installed baseline Linux tooling, Node 24 LTS, SOPS, GitHub CLI, Codex CLI,
   Claude Code, and Infisical CLI.
3. Created `wardenop` and installed the Warden operator public SSH key for
   `ssh warden-capsule`.
4. Mounted `/run/warden-secrets` as tmpfs.
5. Cloned the WardenClyffe repo into `/workspace/WardenClyffe-latest`.
6. Added secret-safe helpers:
   - `warden-secret-write`
   - `warden-secret-list`
   - `warden-secret-path`
   - `warden-secret-remove`
   - `warden-secret-breakglass-cat`
   - `warden-capsule-status`
7. Removed the malformed interrupted `warden-capsule-114-to-server1`
   authorized key line from `server1`.

Remaining manual setup:

1. Authenticate `codex` from inside `ssh warden-capsule`.
2. Authenticate `claude` from inside `ssh warden-capsule`.
3. Authenticate `gh` if private GitHub write/push operations are needed.
4. Authenticate `infisical` or configure the approved service-token flow.
5. Add a clean capsule-to-server1 operator path only after restrictions are
   designed.

## Later Hardening

- Replace root SSH on `server1` with a sudo-limited operator account.
- Evaluate Teleport or Boundary for brokered sessions and recording.
- Add OpenBao/Vault only if Infisical dynamic secret coverage is not enough.
- Add SPIFFE/SPIRE for workload identity once multiple Warden nodes exist.
- Add Warden UI to request an operator session and show audit metadata.

## References

- HashiCorp Boundary session recording: `https://developer.hashicorp.com/boundary/docs/session-recording`
- Teleport session recording architecture: `https://goteleport.com/docs/reference/architecture/session-recording/`
- Infisical dynamic secrets: `https://infisical.com/docs/documentation/platform/secrets-mgmt/concepts/dynamic-secrets`
- Docker Compose secrets: `https://docs.docker.com/reference/compose-file/secrets/`
- SPIRE workload identity concepts: `https://spiffe.io/docs/latest/spire-about/spire-concepts/`
- OpenBao dynamic secrets and leases: `https://openbao.org/`
- Dev Container specification: `https://github.com/devcontainers/spec/blob/main/docs/specs/devcontainer-reference.md`
