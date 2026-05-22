---
wardenclyffe_touchpoint:
  version: 1
  kind: fozzy-exit-caddy-handoff
  namespace: wardenclyffe.warden.edge.fozzy_exit
  owner: docs/FOZZY_EXIT_AND_CADDY_HANDOFF.md
  module: module-01-warden
  reads:
    - docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
    - docs/WARDEN_ESTABLISHMENT_POAM.md
    - wardenclyffe/edge/caddy/Caddyfile
    - wardenclyffe/edge/caddy/compose.yaml
---

# Fozzy Exit And Caddy Handoff

This is the focused handoff note for deleting Fozzy VM `501` without losing the
small amount of useful Caddy configuration it still carries.

## Verified Facts

Checked on 2026-05-22:

| Item | Fact |
|---|---|
| Legacy VM | QEMU `501`, name `Fozzy`, IP `10.0.0.100/24`, bridge `vmbr1` |
| Public NAT | host PREROUTING sends `:80`, `:443`, and `:5432` to `10.0.0.100` |
| Caddy runtime | Docker container `caddy-edge` |
| Caddy path on Fozzy | `/opt/wardenclyffe-caddy-edge` |
| Repo standalone Caddy scaffold | `wardenclyffe/edge/caddy/` |
| Non-secret export | `ops/exports/fozzy-caddy-edge-20260522/` |
| Secret material | `/opt/wardenclyffe-caddy-edge/.env` exists and was not copied |

The exported non-secret files are:

- `Caddyfile`
- `compose.yaml`
- `Caddy.Dockerfile`

The live Caddyfile routes are simple:

| Hostname | Behavior |
|---|---|
| `warden.rrflow.ai` | reverse proxy to `10.0.0.102:9006` |
| `porter.rrflow.ai` | allowlisted status response |
| `auth.rrflow.ai`, `sso.rrflow.ai`, `authentik.rrflow.ai` | allowlisted reverse proxy to `10.0.0.103:9000` |
| `beeker.probablydns.com` | Coolify/realtime reverse proxy |
| `clyffydb.probablydns.com` | reverse proxy to Supabase Kong |

## Decision

Fozzy is not the WardenClyffe edge target. The durable target is the standalone
Caddy scaffold under `wardenclyffe/edge/caddy/`, moved onto a clean dedicated
edge host.

Do not migrate Coolify or Supabase-from-Fozzy as part of the WardenClyffe
foundation unless a separate work item intentionally keeps those services.

## What To Keep

Keep:

- route intent from the Caddyfile.
- Dockerfile modules: Cloudflare DNS and rate limit support.
- the dev allowlist pattern until Authentik/Warden owns route policy.

Do not commit:

- `.env`
- `/data/caddy`
- `/config/caddy`
- certificate/key material
- live API tokens

If certificate continuity matters, export `/data/caddy` and `/config/caddy`
through a private, non-repo backup path. If not, let the new edge reissue
certificates cleanly.

## Safe Deletion Gate

Do not destroy VM `501` until these are true:

1. Non-secret Caddy config has been copied or confirmed identical to
   `wardenclyffe/edge/caddy/`.
2. Public NAT for `:80` and `:443` has been moved to the new edge target or
   the public outage is intentionally accepted.
3. Public `:5432` forwarding has been removed or intentionally replaced.
4. Any needed secret/cert material has been captured outside git.
5. The Warden POA&M marks the replacement edge step complete or explicitly
   records the accepted outage.

Destructive command, only after the gate:

```bash
qm shutdown 501 --timeout 120
qm destroy 501 --purge
```

VM `500` is already stopped and should be reviewed separately before purge.
