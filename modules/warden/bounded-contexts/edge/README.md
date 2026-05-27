---
wardenclyffe_touchpoint:
  version: 1
  kind: bounded-context
  namespace: wardenclyffe.warden.edge
  owner: modules/warden/bounded-contexts/edge/README.md
  module: module-01-warden
---

# Warden Edge Context

Owns public ingress for WardenClyffe.

Responsibilities:

- public IP inventory.
- domain route registry.
- Caddy/Porter or future edge runtime config.
- TLS certificate visibility and renewal state.
- public exposure audit.
- health checks for public routes.
- rollback records for route changes.
- migration from temporary edge hosts to durable Warden-owned edge nodes.

## Current Dedicated Edge

The clean standalone Caddy edge is:

| Field | Value |
|---|---|
| Instance | `clyffy-edge` / `warden-caddy-edge-01` |
| CTID | `115` |
| IP | `10.0.0.115` |
| Network | `vmbr1`, internal only |
| Public NAT | `vmbr0:80` and `vmbr0:443` to `10.0.0.115` |
| Legacy source | VM `501`, `Fozzy`, `10.0.0.100` |
| Existing boundary VM | VM `111`, `edge`; keep separate from standalone Caddy and audit as OPNsense/boundary candidate |
| Health check | `https://clyffy.ai/healthz` and direct SNI origin checks return `ok` |
| TLS | Caddy `v2.11.3`, Cloudflare DNS-01 ACME, Let's Encrypt `E8` |
| Persistent unit | `warden-edge-nat.service` on `server1` |
| Rollback | `/root/warden-edge-cutover/20260526T193646Z/rollback.sh` |
| Snapshots | `standalone-caddy-installed-20260526`, `edge-cutover-public-80-443-20260526`, `caddy-dns01-cloudflare-working-20260526` |

Run the route gate helper to reconcile host rules:

```bash
scripts/edge/apply-caddy-edge-115-route-gate.sh
```

VM `501` is no longer in the public `80/443/5432` route path. Do not add new
routes to it.

Source material:

- `docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md`
- `docs/WARDEN_CLOUDFLARE_DNS_FOUNDATION.md`
- `modules/warden/bounded-contexts/edge/EDGE_CUTOVER_20260526.md`
- `wardenclyffe/docs/specs/04-edge-and-routing.md`
- `wardenclyffe/docs/infra-state.md`
- `wardenclyffe/registry/domains.yaml`
- `wardenclyffe/registry/services.yaml`
