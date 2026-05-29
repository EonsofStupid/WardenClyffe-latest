---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: wardenclyffe-core
  kind: edge-route-cutover
  owner: modules/warden/bounded-contexts/edge/EDGE_CUTOVER_20260526.md
  module: module-01-warden
  sync:
    qdrant: true
    surreal: true
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - modules/warden/bounded-contexts/edge/README.md
    - docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
    - docs/WARDEN_ESTABLISHMENT_POAM.md
---

# Edge Cutover 2026-05-26

This touchpoint records the pre-release public edge route cutover from Fozzy to
the Warden-owned standalone Caddy edge.

## Live Route

| Item | Value |
|---|---|
| Host | `server1` |
| Public IP | `104.176.44.101` |
| Runtime | LXC `115`, `clyffy-edge`, `10.0.0.115` |
| Public forwards | `vmbr0:80 -> 10.0.0.115:80`, `vmbr0:443 -> 10.0.0.115:443` |
| Persistent unit | `warden-edge-nat.service` |
| Apply helper | `/usr/local/sbin/warden-edge-nat-apply` on `server1` |
| Rollback | `/root/warden-edge-cutover/20260526T193646Z/rollback.sh` on `server1` |
| Snapshots | `standalone-caddy-installed-20260526`, `edge-cutover-public-80-443-20260526`, `caddy-dns01-cloudflare-working-20260526` |
| TLS | Caddy `v2.11.3` with Cloudflare DNS-01 ACME |

Removed from the public path:

- VM `501` / `10.0.0.100` HTTP and HTTPS DNAT.
- old duplicate `10.1.1.100` HTTP and HTTPS DNAT.
- VM `501` / `10.0.0.100` public TCP `5432` DNAT.

## Health

Verified after cutover:

- direct origin HTTPS with SNI: `https://clyffy.ai/healthz` via
  `104.176.44.101` returned `ok` with `X-Warden-Edge: clyffy-edge`.
- direct origin HTTPS with SNI for `warden.rrflow.ai`, `probablydns.com`, and
  `effing.ai` returned `ok`.
- Cloudflare-proxied `https://clyffy.ai/healthz` returned `ok`.
- direct origin metadata:
  `https://clyffy.ai/.well-known/warden/edge.json` returned the edge service
  identity.
- certificate files for `clyffy.ai`, `*.rrflow.ai`, `probablydns.com`, and
  `effing.ai` are issued by Let's Encrypt `E8`, expiring 2026-08-24.

## Follow-Up

- Render Caddy routes from Warden route intent instead of hand-maintaining the
  bootstrap Caddyfile.
- Rotate the Cloudflare DNS token after bootstrap because one failed validation
  pass ran before the service unit was switched away from environment logging.
- Keep this touchpoint synced into Qdrant and SurrealDB so Warden UI can show
  route freshness, rollback state, and drift.
