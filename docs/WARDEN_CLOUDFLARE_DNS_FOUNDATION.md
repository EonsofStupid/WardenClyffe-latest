---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: warden-cloudflare-dns
  persona: clyffy-operator
  kind: dns-provider-foundation
  owner: docs/WARDEN_CLOUDFLARE_DNS_FOUNDATION.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
    - docs/WARDEN_ESTABLISHMENT_POAM.md
    - modules/warden/bounded-contexts/dns/README.md
    - modules/warden/bounded-contexts/edge/README.md
  sync:
    qdrant: true
    surreal: true
---

# Warden Cloudflare DNS Foundation

This file captures the working Cloudflare DNS provider contract for Warden.

## Canonical Secret

| Field | Value |
|---|---|
| Secret authority | Infisical |
| Project | Clyffy `4a897376-3cbd-4aeb-8550-c7d3ed7ad113` |
| Environment | `dev` |
| Path | `/` |
| Secret key | `WARDEN_CLOUDFLARE_DNS_ADMIN` |
| Runtime env alias | `CLOUDFLARE_API_TOKEN` |

The token value must not be printed in chat, logs, docs, or git.

## Verified Access

Verified on 2026-05-26 from the Warden operator capsule:

| Check | Result |
|---|---|
| Secret present | yes |
| Cloudflare zones list | `200` |
| DNS records list | `200` for target zones |
| Target account | `Jessay@gmail.com's Account` |
| Target zones found | `clyffy.ai`, `rrflow.ai`, `probablydns.com`, `effing.ai` |

The successful token fingerprint prefix for this verification was
`4178a4bfaf0520ff`. This is a non-secret SHA-256 prefix used only to prove the
capsule read the updated token value.

## Caddy DNS-01 Note

During the 2026-05-26 edge cutover, the standalone Caddy edge was upgraded to
Caddy `v2.11.3` with:

- `github.com/caddy-dns/cloudflare@v0.2.4`
- `github.com/mholt/caddy-ratelimit@v0.1.0`

The canonical Infisical value is brokered on LXC `115` as:

| Runtime path | Owner/mode | Notes |
|---|---|---|
| `/etc/caddy/secrets/cloudflare.env` | `root:caddy`, `0640` | exposes only `CLOUDFLARE_API_TOKEN` to the Caddy service |

The first bootstrap copy contained a hidden carriage return from the Windows
launch path. That was stripped before validation. Caddy is now using real ACME
DNS-01 through Cloudflare; this is not Caddy internal TLS and not a temporary
certificate path.

The Caddy DNS-01 policy includes `propagation_delay 20s` and
`propagation_timeout 10m` because `effing.ai` propagated more slowly than
Caddy's default wait window during bootstrap.

Verified on 2026-05-26:

| Zone | ACME state |
|---|---|
| `clyffy.ai`, `*.clyffy.ai` | issued by Let's Encrypt `E8` |
| `rrflow.ai`, `*.rrflow.ai` | issued by Let's Encrypt `E8` |
| `probablydns.com`, `*.probablydns.com` | issued by Let's Encrypt `E8` |
| `effing.ai`, `*.effing.ai` | issued by Let's Encrypt `E8` after increasing DNS propagation timeout |

Provider cleanup after issuance removed stale `_acme-challenge.effing.ai` TXT
records from Cloudflare. Resolver caches may show old TXT answers until their
TTL expires.

## Target Zones

| Zone | Zone id | Role |
|---|---|---|
| `clyffy.ai` | `40bb8e4477b430c77dbb6c81b3fb6e5f` | Clyffy/Clyffe product brand |
| `rrflow.ai` | `f3d6d6626dd2efa884ab7688f3196697` | business/internal foundation |
| `probablydns.com` | `286e4e10a766acbffa50a297ee9cd1de` | infrastructure and legacy routing |
| `effing.ai` | `a6453fb939f6a586fa77afc137720119` | personal estate |

## Helper Scripts

Inventory:

```bash
scripts/dns/cloudflare-domain-inventory.sh
```

Upsert an A record by zone name:

```bash
scripts/dns/upsert-cloudflare-a-record.sh \
  --zone-name clyffy.ai \
  --name master.clyffy.ai \
  --target 104.176.44.101
```

The upsert helper is dry-run by default. Use `--apply` only after the edge
route and backend health checks exist.

## Provider Rules

- Cloudflare owns public DNS provider state.
- PowerDNS owns planned internal authoritative/split-horizon records.
- Caddy owns runtime HTTP/TLS routing until Warden renders route intent.
- Warden owns route intent, approval, audit, health, and rollback.
- Public records must point to the Warden-controlled public edge or public jump,
  never directly to private `10.0.0.0/24` service IPs.

## Warden DNS Management Contract

Warden DNS/domain management is not just a helper script. It is a bounded
context and MCP/intelligence domain.

| Layer | Responsibility |
|---|---|
| Authoring | Warden route/DNS intent records, reviewed before apply |
| Public provider | Cloudflare zones and DNS records |
| Internal provider | PowerDNS/OPNsense split-horizon records |
| Edge | Caddy route and certificate state |
| Product truth | Postgres tables for domains, records, routes, tasks, approvals, and audit |
| Retrieval | Qdrant indexes domain runbooks, touchpoints, and operator guides |
| Graph projection | SurrealDB links domains, zones, services, routes, certs, hosts, and agents |
| MCP leaf | planned `mcp.global.dns` behind Warden policy |

The DNS leaf should expose boring, idempotent tools:

- `dns.zone_inventory`
- `dns.record_inventory`
- `dns.plan_record`
- `dns.apply_record`
- `dns.delete_record`
- `dns.verify_record`
- `dns.route_readiness`

Write tools require Warden approval tasks, route health gates, and rollback
material. Read tools may run directly once the operator token path is verified.
