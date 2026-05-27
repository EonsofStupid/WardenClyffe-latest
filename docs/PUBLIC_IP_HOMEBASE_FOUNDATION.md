---
wardenclyffe_touchpoint:
  version: 1
  kind: public-ip-homebase
  namespace: wardenclyffe.warden.edge.homebase
  owner: docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/HOST_FLEET_AND_ONBOARDING.md
    - docs/MASTER_CLYFFY_ROLLOUT_PLAN.md
    - docs/PROXMOX_FREE_CHEATSHEET.md
    - wardenclyffe/docs/specs/04-edge-and-routing.md
    - wardenclyffe/docs/decisions/0013-dns-authority-split-horizon.md
    - wardenclyffe/docs/infra-state.md
---

# Public IP Homebase Foundation

This file captures the boring foundation for making `104.176.44.101` the
WardenClyffe homebase while more hosts are added.

Warden owns this as operator infrastructure. Clyffe consumes the resulting
customer-safe routes and service records through Warden APIs.

## Current Verified State

Read-only probes on 2026-05-22 confirmed the host shape. The public edge route
was cut over on 2026-05-26.

| Item | Current fact |
|---|---|
| Homebase host | `server1`, Proxmox VE `9.1.9` |
| Public IP | `104.176.44.101` |
| Public bridge | `vmbr0`, `104.176.44.101/26`, gateway `104.176.44.126`, bridge port `nic0` |
| Internal bridge | `vmbr1`, `10.0.0.1/24`, NAT out through `vmbr0` |
| Current HTTP/HTTPS forward | `warden-edge-nat.service` forwards `vmbr0:80` and `vmbr0:443` to LXC `115` / `10.0.0.115` |
| Removed legacy public forward | VM `501` / `10.0.0.100` no longer receives public `:80`, `:443`, or `:5432` DNAT |
| Public edge TLS | Caddy `v2.11.3` uses Cloudflare DNS-01 ACME with Let's Encrypt `E8` certs for `clyffy.ai`, `rrflow.ai`, `probablydns.com`, and `effing.ai` |
| Rollback material | `/root/warden-edge-cutover/20260526T193646Z/rollback.sh` on `server1` |

Current public probes:

| FQDN | DNS state | Public probe | Notes |
|---|---|---|---|
| `warden.rrflow.ai` | A record to `104.176.44.101` | `303` to `/login` | Warden is reachable |
| `porter.rrflow.ai` | A record to `104.176.44.101` | `200` status response | Caddy/Porter is reachable |
| `auth.rrflow.ai` | A record to `104.176.44.101` | `302` to Authentik flow | Authentik is reachable |
| `clyffy.ai` | Cloudflare proxied records | `200` at `/healthz` | Served by `clyffy-edge` with ACME DNS-01 origin TLS |
| `master.clyffy.ai` | no public A record found | NXDOMAIN | not provisioned |
| `observatory.clyffy.ai` | no public A record found | NXDOMAIN | not provisioned |
| `clyffydb.probablydns.com` | A record to `104.176.44.101` | `401` from Kong | reachable but should be reviewed for public exposure |

## Important Source Conflict

The Go-side routing spec still describes Porter/Caddy on VM `501` as the live
edge. That is now stale. Public HTTP/HTTPS is on LXC `115` `clyffy-edge`; VM
`501` is no longer in the public route path.

## Homebase Ingress Contract

The homebase pattern is:

```text
public DNS
  -> 104.176.44.101
  -> PVE host firewall/NAT
  -> Warden-managed edge service
  -> internal service on vmbr1 or remote host overlay
```

Warden should make this visible and auditable:

- public IP inventory.
- DNS records and provider state.
- edge route records.
- Caddy/Porter config state.
- TLS certificate state.
- backend health probes.
- rollback history.
- route ownership by Warden or Clyffe service.

Do not expose Proxmox `:8006` as the public management product. Warden is the
operator surface. Proxmox API access should become private, allowlisted, or
overlay-only once Warden is reliable.

## Domain Authority Model

Use the existing ADR direction until a newer ADR replaces it:

- Cloudflare is the current public DNS provider for managed public records.
- The canonical Warden Cloudflare DNS token is the Clyffy Infisical secret
  `WARDEN_CLOUDFLARE_DNS_ADMIN`.
- PowerDNS is the intended programmable authoritative source for internal zone
  truth.
- OPNsense Unbound is the intended recursive and split-horizon resolver.
- Tailscale MagicDNS is tactical, not the final naming layer.

The desired record flow is:

```text
Warden route intent
  -> domain registry record
  -> DNS provider sync
  -> edge route generation
  -> TLS issuance/renewal
  -> health verification
  -> audit event
```

The Caddyfile should not become the only source of truth. It can remain the
rendered runtime config while Warden becomes the authority over intent,
approval, health, and rollback.

## Public IP Usage Rules

Use the public IP for:

- HTTP/HTTPS SNI routing for Warden, Clyffe, identity, observability, and
  customer services.
- a small number of audited TCP services only when there is no safer option.
- overlay rendezvous for remote WardenClyffe hosts.

Avoid using the public IP for:

- direct public Proxmox administration.
- direct public database exposure.
- one-off ports that are not represented in Warden inventory.
- customer traffic that bypasses Warden audit and tenancy.

## Remote Host Model

Every new host should register through Warden:

```text
host descriptor
  -> secret references
  -> read-only Proxmox probe
  -> inventory snapshot
  -> private overlay link
  -> internal DNS records
  -> Warden route eligibility
  -> health and audit
```

The Wisconsin host stays the homebase until Virginia is online and promoted to
a regional peer. Remote hosts should not need to expose their Proxmox UI or
random service ports to the public internet. They should reach Warden through a
private overlay and publish services through homebase or through a Warden-owned
regional edge.

Transport options:

| Option | Role |
|---|---|
| OPNsense WireGuard | boring first private backbone for site-to-site and operator access |
| WardenClyffeNet | branded/productized mesh direction once test coverage and operational playbooks are ready |
| Tailscale | temporary fallback and break-glass path until WardenClyffe networking is proven |

## Tailscale Exit Gates

Do not remove Tailscale from a critical path until these gates pass:

1. Warden reaches the homebase Proxmox host over a non-Tailscale path.
2. Warden reaches at least one remote host over the replacement overlay.
3. Internal DNS resolves Warden, Authentik, CA, Qdrant, SurrealDB, and Proxmox
   aliases without MagicDNS.
4. Public DNS routes Warden and Clyffe hostnames through the Warden-managed edge.
5. TLS issuance and renewal are automated and visible in Warden.
6. Break-glass SSH and Proxmox access are documented outside the overlay.
7. Route rollback has been tested for one public service.
8. Tailscale is moved to fallback status before it is removed.

## Foundation Work Order

1. Finish the Fozzy deletion gate now that public routing is on LXC `115`.
2. Keep public TCP `:5432` removed unless Warden explicitly owns a future route.
3. Create a Warden edge context that owns public ingress, route inventory, TLS,
   and domain health.
4. Promote domain route data from Go-side registries into the root Warden model.
5. Use the verified Caddy/Cloudflare DNS-01 edge before adding customer or
   master routes.
6. Add Warden UI panels for domains, routes, certs, edge health, and public IP
   exposure.
7. Add remote host onboarding for the Virginia server using read-only Proxmox
   probes first.
8. Make the private overlay and split-horizon DNS boring before turning off
   Tailscale.

## Master Clyffy Route

The focused rollout for `master.clyffy.ai` is tracked in
`docs/MASTER_CLYFFY_ROLLOUT_PLAN.md`.

Key contract:

- public Cloudflare `master.clyffy.ai` should target `104.176.44.101`.
- internal PowerDNS `master.clyffy.ai` should target the app LXC on `vmbr1`.
- the app target is planned as LXC `120` at `10.0.0.120`.
- the clean public edge is LXC `115`; VM `501` should not receive new public
  routes.
