---
wardenclyffe_touchpoint:
  version: 1
  kind: bounded-context
  namespace: wardenclyffe.warden.dns
  owner: modules/warden/bounded-contexts/dns/README.md
  module: module-01-warden
---

# Warden DNS Context

Owns domain and resolver integration for WardenClyffe.

## Responsibilities

- public DNS provider records.
- internal split-horizon DNS intent.
- PowerDNS and Cloudflare synchronization contracts.
- OPNsense Unbound integration notes.
- service-name and customer-domain policies.
- DNS health checks.
- DNS change audit and rollback.
- MCP/intelligence projection for domains, zones, records, routes, and certs.

## Current Authority

| Surface | Owner |
|---|---|
| Public DNS provider | Cloudflare |
| Public HTTP/TLS edge | Caddy on LXC `115` |
| Public jump | `ssh.clyffy.ai` DNS-only to `104.176.44.101` |
| Internal DNS target | PowerDNS LXC `109`, later OPNsense split DNS |
| Warden truth | route/DNS intent, approval, task, audit |

Public DNS must never point directly at private `10.0.0.0/24` workspace IPs.
Private workspace names such as `devstation.clyffy.ai` and
`capsule.clyffy.ai` are SSH aliases now and split-horizon names later.

## MCP Domain

Planned registry id:

```text
mcp.global.dns
```

Target tools:

- `dns.zone_inventory`
- `dns.record_inventory`
- `dns.plan_record`
- `dns.apply_record`
- `dns.delete_record`
- `dns.verify_record`
- `dns.route_readiness`

Read tools can run after provider credentials are verified. Write tools require
Warden approval, route readiness, rollback material, and audit.

## Intelligence Projection

Markdown touchpoints describe the contract. Generated stores carry the memory:

- Postgres owns product truth for domains, records, approvals, tasks, and audit.
- Qdrant indexes DNS runbooks, touchpoints, and operator explanations.
- SurrealDB links domain graph entities: zones, records, routes, certs, hosts,
  edge services, agent workspaces, and verification events.

## Extension Checklist

When adding a zone, service domain, or customer domain:

1. Add the domain/zone to Warden intent, not straight to provider state.
2. Verify Cloudflare or internal provider authority.
3. Add route readiness gates: backend health, edge route, TLS plan, rollback.
4. Apply public records only through Warden-controlled helpers.
5. Capture the result in task/audit records.
6. Update `wardenclyffe/registry/context-mesh.yaml` and relevant touchpoints.
7. Let the sync worker project the change to Qdrant and SurrealDB.

## Source Material

- `docs/WARDEN_CLOUDFLARE_DNS_FOUNDATION.md`
- `docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md`
- `docs/WARDEN_REMOTE_AGENT_STREAMS.md`
- `wardenclyffe/docs/decisions/0013-dns-authority-split-horizon.md`
- `wardenclyffe/registry/domains.yaml`
