---
wardenclyffe_touchpoint:
  version: 1
  kind: foundation-service-matrix
  namespace: wardenclyffe.foundation.services
  owner: docs/FOUNDATION_SERVICE_MATRIX.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDEN_CLYFFE_PILOT_ROADMAP.md
    - docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
    - docs/WARDEN_ESTABLISHMENT_POAM.md
    - docs/SURREALDB_PUBLIC_SELF_HOSTING_PLAN.md
    - docs/SURREALDB_SELF_HOSTED_RUNBOOK.md
    - docs/MASTER_CLYFFY_ROLLOUT_PLAN.md
    - docs/FOUNDATION_APP_RESEARCH_2026_05.md
    - wardenclyffe/docs/infra-state.md
    - wardenclyffe/docs/foundation-status.md
    - wardenclyffe/docs/foundation-remaining-work.md
    - wardenclyffe/docs/specs/network-master-trio.md
---

# Foundation Service Matrix

This file answers which existing VM/LXC services form the WardenClyffe
foundation, what still has to be configured, and whether the app choice is
solid for eventual customer services.

It is a planning projection. Live truth still comes from Proxmox plus the
Go-side inventory docs until Warden promotes this into a database-backed host
registry.

## Short Answer

The foundation stack is directionally solid:

- **OPNsense** for network boundary, VLANs, WireGuard, firewall policy, and
  split-horizon resolver placement.
- **Authentik** for current OIDC/SSO/MFA/passkey identity.
- **Caddy** for public HTTP/TLS edge and simple route automation.
- **Traefik** only as legacy/Coolify-local routing, not the final public edge.
- **PowerDNS + Unbound + Cloudflare** for programmable public/internal DNS.
- **Postgres** for Warden/Clyffe product truth.
- **Qdrant + SurrealDB** for AI retrieval and graph/reasoning projections.
- **Infisical + OS keyring** for secrets.

See `docs/FOUNDATION_APP_RESEARCH_2026_05.md` for the primary-source research
behind these app choices.

`docs/CLYFFY_MCP_ORCHESTRATOR.md` is the orchestrator boundary that ties these
services together. Clyffy is the main MCP orchestrator; Warden is still the
infrastructure/control authority.

The foundation is not customer-service ready until OPNsense, Authentik, Caddy,
DNS, backups, and Warden audit are configured as gates below.

## Existing Guest Matrix

| VMID | Guest | Current role | Keep / replace | Configuration still owed | Customer-service readiness |
|---|---|---|---|---|---|
| `101` | `buffer` | Apt-Cacher-NG estate package cache | Keep | document cache policy and backup if config matters | internal utility only |
| `102` | `warden` | Warden Go server manager, public at `warden.rrflow.ai` | Keep and make Go Warden the active implementation authority; Rust is parked/reference | OIDC with Authentik, Proxmox reconciler cron, audit hardening, backup config, Clyffe-safe API boundary | operator-useful, not customer-ready |
| `103` | `authentik` | Identity provider | Keep for foundation | passkey enrolment, recovery codes, realm separation, OIDC clients, policy bindings, custom claims, step-ca cert, blueprints, backups, Infisical mirrors | good choice once configured |
| `104` | `surreal` | AI state and graph/reasoning projection | Keep as AI plane, not product truth | root rotation, schema cleanup, sync contracts, public-safe Warden proxy route, cloud export/import after endpoint resumes | local backups and persistence fixed; publish only through Caddy/Auth/Warden policy proxy |
| `105` | `nomad` | Scheduler, scope unclear | Park until scope is documented | decide what belongs in Nomad vs systemd/LXC-native; verify network gateway anomaly | not a customer foundation until scoped |
| `106` | `qdrant` | Vector store | Keep | collections, snapshot schedule, mirror decision, ingestion pipeline from approved docs/touchpoints | solid for KB/search once scoped |
| `107` | `harrier` | TEI embedder | Keep if bakeoff confirms | run embedder bakeoff, wire into prompt/KB pipeline, cap resource use | internal AI utility |
| `108` | `clyffy-stepca` | Internal CA | Keep | cold-store root, ACME smoke test, backups, cert issuance runbooks, trust propagation | foundation-critical |
| `109` | `clyffy-pdns` | PowerDNS authoritative | Keep | load all zones, Cloudflare sync job, API restriction, backups, DNSSEC/CAA/DS policy | solid once configured |
| `110` | `clyffy-pg-master` | Postgres control-plane/userdata candidate | Keep as product truth direction | migrations, backup, replication/mirror strategy, ownership schema | correct foundation DB direction |
| `111` | `edge` | Existing boundary VM; not the standalone Caddy service | Keep if audit confirms config | live config audit, WAN/LAN mapping, WireGuard, ACL groups, Unbound split-horizon, firewall rules, config backups, Warden API token | required before real customers |
| `112` | `clyffy-bifrost` | LLM gateway | Keep | provider keys, Observatory wrapping, analytics, rate limits | internal AI service until tenant policy exists |
| `113` | `observatory` | WardenClyffe-owned Helicone-like LLM observability | Keep if maintained internally | OIDC gate through Authentik, deploy completion, trace ingestion, retention, backups, Better Auth boundary if embedded in apps | useful for AIaaS operations |
| `114` | `warden-operator-capsule` | Secret-safe Linux operator capsule | Keep | brokered secrets, restricted operator path, audit hardening | internal operator-only |
| `116` | `warden-devstation-01` | Private VS Code/Cursor/Codex/Claude devstation with SSH-tunneled code-server | Keep | backup policy, WardenNet access, future Warden UI lifecycle controls | internal operator-only; Clyffe Code seed pattern |
| `115` | `clyffy-edge` | Dedicated standalone Caddy public edge | Keep | Caddy `v2.11.3`, Cloudflare DNS-01 ACME, `10.0.0.115`, public `80/443` active, `/healthz` and edge metadata OK, rollback and TLS snapshot captured | required for customer domains |
| `120` | proposed `clyffy-portal` | Clyffe/Clyffy customer/master surface | Build later | depends on identity, DNS, edge, CA, Warden API, Postgres schema | not started |
| `500/501` | `fozzy` / `Fozzy` | dead legacy Coolify/Traefik/Caddy edge dependency | Ready to delete after dependency check | public `80/443/5432` removed from VM `501`; verify no private dependency remains | not acceptable as final foundation |

## App Choice Guidance

| Layer | Recommended foundation app | Why | Do not do |
|---|---|---|---|
| Network boundary | OPNsense | Mature firewall/router appliance, clean home for VLANs, WireGuard, Unbound placement, and customer/operator ACLs | Do not treat Tailscale as the final network authority |
| Identity | Authentik | Strong self-hosted IdP for OIDC/OAuth2, SAML/LDAP/proxy-provider patterns, MFA/passkeys, and app gating | Do not fork or replace it until the Authentik specialist has run cleanly for a while |
| Future identity alternative | Zitadel | Worth later evaluation, but current production docs prefer PostgreSQL and do not justify a foundation pivot | Do not pause Authentik to chase a parking-lot migration |
| Public edge | Caddy | Simple route config, automatic HTTPS, HTTP/3, good fit for Warden-generated Caddyfile/API config | Do not run Caddy and Traefik as competing public edges |
| Legacy app edge | Traefik | Solid dynamic proxy for Docker/Kubernetes/Coolify-style environments | Do not make it the WardenClyffe public edge while Caddy is the chosen route authority |
| DNS authority | PowerDNS + Cloudflare public DNS + OPNsense Unbound | Programmable zones, internal/public split, DNS-01 path, customer subdomain automation | Do not rely on manual registrar panels for customer-service routing |
| Product data | Postgres | Best boring source of truth for tenants, tickets, CRM, inventory, RBAC, audit, workflows | Do not put Warden/Clyffe customer truth in SurrealDB or Qdrant |
| AI retrieval | Qdrant | Purpose-built vector retrieval for docs, KB, prompts, and project memory | Do not expose raw vector memory directly to customers |
| AI graph/projection | SurrealDB | Useful for AI state, graph context, and reasoning projections | Do not let it become the billing/ticket/customer source of truth |
| AI context public route | SurrealDB behind Warden proxy | Gives agents and operator tools a real HTTPS endpoint without exposing raw database authority | Do not publish LXC `104:8000` directly |
| Secrets | Infisical -> OS keyring | Good separation between canonical secrets and local runtime resolution | Do not commit local `.env`, PATs, root cert keys, or live responses |
| Observability | Observatory/Helicone for LLM plus OTel/Grafana/Loki-class infra telemetry | Needed to operate AIaaS and customer services with confidence | Do not ship customer services without logs, metrics, traces, and restore drills |
| App-local auth | Better Auth in Clyffy/minions | Gives Clyffy/minions their own secure session/API layer while Authentik remains SSO | Do not let app-local auth become a second human identity source of truth |

Current Postgres note: the live LXC `110` is Postgres 17. That is acceptable
for the pilot if patched and backed up. New production packaging should target
the current supported major once migrations, backup, and restore drills are
boring.

The immediate Postgres work for `master.clyffy.ai` is tracked in
`docs/MASTER_CLYFFY_ROLLOUT_PLAN.md`: patch LXC `110` within PostgreSQL 17
after backup, and treat PostgreSQL 18 as a separate major-upgrade gate.

## Network Master Trio

For WardenClyffe customer services, the durable trio is:

```text
Caddy      = what public route/protocol/TLS is exposed
OPNsense   = where packets may go, by VLAN/site/peer/customer boundary
Authentik  = who the subject is, with OIDC/MFA/claims/policy
```

That trio is the right foundation because each piece fails in a different
direction. A mistaken route can still be stopped by identity or network policy.
A mistaken identity claim can still be bounded by VLAN/firewall policy.

## Required Gates Before Customer Service

1. Delete/decommission VM `501` after confirming no private dependency remains.
2. Audit/remove/justify public TCP `:5432` exposure.
3. Confirm VM `111` OPNsense live config and make it the boundary authority.
4. Configure WireGuard and split-horizon DNS through OPNsense Unbound.
5. Finish Authentik passkey, realms, OIDC clients, claims, policies, and backups.
6. Load PowerDNS zones, create Cloudflare sync, and finish CAA/DNSSEC policy.
7. Issue and test step-ca certificates through ACME.
8. Configure backups for every stateful service.
9. Add Warden API surfaces for edge, DNS, identity, firewall, and audit state.
10. Build Clyffe only against Warden's tenant-safe API.

## References

- `wardenclyffe/docs/infra-state.md`
- `wardenclyffe/docs/foundation-status.md`
- `wardenclyffe/docs/foundation-remaining-work.md`
- `wardenclyffe/docs/specs/network-master-trio.md`
- OPNsense documentation: `https://docs.opnsense.org/`
- Authentik documentation: `https://docs.goauthentik.io/docs/`
- Caddy documentation: `https://caddyserver.com/docs/`
- Traefik documentation: `https://doc.traefik.io/traefik/`
