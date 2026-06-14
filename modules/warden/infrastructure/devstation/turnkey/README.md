# Devstation turnkey package

The no-leak secret pipeline + acceptance gate that normalizes a Warden
devstation. Spec: `docs/specs/WARDEN_DEVSTATION_TURNKEY_BUILD_SPEC.md`.

```text
turnkey/
  bin/
    install-devstation-turnkey.sh   one-shot installer (run as root; idempotent)
    warden-secrets-preflight        ExecStartPre guard for the broker
    warden-secrets-refresh-hook     locks perms + freshness marker on each render
    warden-devstation-status        acceptance gate — PASS/FAIL, non-zero on miss
  etc/warden/
    agent-config.yaml               Infisical Agent config (sourced 0.43.x format)
    secrets.tmpl                    listSecrets DSL → /run/warden-secrets/warden.env
    infisical-mi.env.template        machine-identity template (root-only; no secret)
  systemd/
    infisical-agent.service         the secret broker unit (RuntimeDirectory tmpfs)
```

## Install (on the devstation, as root)

```bash
sudo modules/warden/infrastructure/devstation/turnkey/bin/install-devstation-turnkey.sh
# first run drops /etc/warden/infisical-mi.env — fill it (LIVE client secret),
# then re-run. Broker starts; secrets land at /run/warden-secrets/warden.env.
warden-devstation-status   # → PASS
```

## Secrets never leak

- The repo carries **no** secret values — only the template and the agent
  config that references root-only credential files.
- Secrets materialize to `/run/warden-secrets` (tmpfs), `0700`, cleared on stop.
- `agent-config.yaml` reads `client-id`/`client-secret` from `/etc/warden/infisical/`
  (0600), derived from `/etc/warden/infisical-mi.env` by the installer.

## Operator gate

The machine-identity client secret must be **live**, and the devstation egress
IP must be in the MI's Universal-Auth **Trusted-IPs**. A `401 Invalid
credentials` means the secret rotated/expired — issue a new one. See the
decision log entry `devstation-secret-pipeline-turnkey`.
