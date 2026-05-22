---
wardenclyffe_touchpoint:
  version: 1
  kind: foundation-app-research
  namespace: wardenclyffe.foundation.app-research
  owner: docs/FOUNDATION_APP_RESEARCH_2026_05.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/FOUNDATION_SERVICE_MATRIX.md
    - docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
    - docs/WARDENCLYFFE_BACKEND_OPTIONS_2026_05.md
---

# Foundation App Research - May 2026

This note records the current app-stack research behind the WardenClyffe
foundation. It is intentionally conservative: choose tools that make the
two-server pilot reliable first, then productize the control surfaces in
Warden and Clyffe.

## Decision Summary

| Layer | Recommendation | Confidence | Why |
|---|---|---|---|
| Network boundary | Keep OPNsense | high | Mature firewall/router layer, strong fit for VLANs, WireGuard, Unbound, firewall policy, and future Warden API integration |
| Public HTTP/TLS edge | Standardize on Caddy | high | Simple route model, automatic HTTPS, reverse proxy support, admin API, Go-native extension path |
| Legacy dynamic container edge | Do not standardize on Traefik | high | Traefik is good for Docker/Kubernetes provider discovery, but the WardenClyffe public edge should be Warden-rendered and auditable |
| Identity | Keep Authentik for the foundation | medium-high | Good current fit for OIDC/OAuth2, proxy providers, blueprints, passkeys, and self-hosted app gating |
| Future identity alternative | Park Zitadel | medium | Worth studying later, but not a reason to halt Authentik; current Zitadel production docs prefer PostgreSQL, not Dragonfly |
| DNS authority | Keep PowerDNS + Cloudflare sync + OPNsense Unbound | high | Programmable zone API, DNSSEC support, split-horizon architecture |
| Product database | Use PostgreSQL | high | Boring source of truth for tenants, tickets, CRM, inventory, RBAC, audit, workflows |
| Vector retrieval | Use Qdrant | high | Purpose-built vector retrieval; use tenant payload partitioning instead of many collections by default |
| AI graph/projection | Keep SurrealDB, scoped | medium | Strong fit for agent memory, graph, and reasoning projection; do not make it the Warden/Clyffe product truth |
| Secrets | Infisical -> OS keyring | high | Machine identity and Universal Auth fit the current keyring sync design |

## Findings

### OPNsense

OPNsense remains the right boundary appliance for the foundation. The 26.1
series notes call out continued firewall hardening, modern IPv6 work, and a
broadening MVC/API surface. The API documentation includes WireGuard client,
server, and service operations, which matters because Warden eventually needs
to show and manage overlay state instead of leaving it as hand-maintained
firewall config.

WardenClyffe use:

- keep VM `111` as the intended boundary if live audit confirms it is really
  OPNsense and the interfaces are correct.
- make OPNsense own VLANs, WireGuard, firewall policy, and recursive DNS
  placement.
- do not make Tailscale the final network source of truth.

### Authentik

Authentik remains a good current identity foundation. Its OAuth2 provider
supports OIDC, common OAuth2 flows, PKCE, device code, refresh-token rotation,
and scope mappings. Its blueprint system supports versioned, repeatable config.

WardenClyffe use:

- finish passkey enrolment, recovery codes, realms, OIDC clients, claims,
  policies, and backups.
- keep Authentik until the Warden/Clyffe foundation is stable.
- build a specialist around Authentik before considering a replacement.

Zitadel stays a parking-lot option. The useful 2026 correction is that Zitadel
production docs now prefer PostgreSQL for self-hosting. Do not treat Zitadel as
evidence that WardenClyffe needs Dragonfly as a product database.

### Caddy

Caddy is the right public edge for WardenClyffe. Automatic HTTPS obtains and
renews certificates for qualifying names and redirects HTTP to HTTPS. Its
reverse proxy supports configurable upstreams, health checks, load balancing,
request/header manipulation, and modern HTTPS upstream behavior. Its admin API
can be used for Warden-rendered config, but the admin endpoint must remain
private.

WardenClyffe use:

- provision dedicated edge LXC `115`.
- render routes from Warden intent into Caddy config or API calls.
- keep route health, TLS state, and rollback history in Warden.
- avoid exposing the Caddy admin API publicly.

### Traefik

Traefik is not bad software; it is just not the right final public edge for
this build. Its strength is provider-driven dynamic configuration, especially
Docker/Kubernetes/service-discovery environments. WardenClyffe's public edge
should be boring, centrally audited, and generated from Warden route intent.

WardenClyffe use:

- keep Traefik only where Coolify or a local Docker app still needs it.
- do not run Traefik and Caddy as competing public edge authorities.
- migrate public ingress away from VM `501`.

### PowerDNS And Unbound

PowerDNS Authoritative is a strong fit for programmable DNS. Its HTTP API can
read statistics and modify zones, metadata, DNSSEC key material, and zone
content. DNSSEC support is built in. OPNsense Unbound remains the recursive and
split-horizon layer.

WardenClyffe use:

- PowerDNS owns internal zone intent.
- Cloudflare remains public DNS for the immediate path.
- Warden should drive a sync job and health checks.
- PowerDNS API access must stay internal and credentialed.

### PostgreSQL

PostgreSQL remains the correct Warden/Clyffe source of truth. As of 2026-05-14,
PostgreSQL 18.4 is current, while the live LXC `110` is Postgres 17. That is
fine for the pilot if patched, but new production work should plan for the
current supported major once migrations and backups are boring.

WardenClyffe use:

- tenants, users, RBAC, inventory, Proxmox resource mappings, tickets, CRM,
  audit, approvals, workflow state, and route intent belong in Postgres.
- Qdrant and SurrealDB reference product IDs; they do not own customer truth.

### Qdrant

Qdrant remains the right vector store. Its multitenancy guidance recommends a
single collection per embedding model with payload-based tenant/use-case
partitioning for most deployments, instead of creating hundreds or thousands of
collections.

WardenClyffe use:

- use collection-per-embedding-model as the default.
- filter by tenant/project/source metadata.
- snapshot collections and test restore.
- never expose raw vector memory directly to Clyffe customers.

### SurrealDB

SurrealDB 3.0 is worth keeping for AI memory and graph/reasoning projections.
Its own positioning is agent memory with vector, graph, and SQL-style retrieval
in one query. That is useful for Clyffy, MCP mesh touchpoints, and internal
reasoning projections.

WardenClyffe use:

- keep SurrealDB as Plane A / AI graph projection.
- do not use it as the Warden/Clyffe product source of truth.
- define sync contracts from Postgres and Markdown touchpoints.

### Infisical

Infisical Universal Auth fits the current design: machine identities exchange a
client ID and client secret for short-lived access tokens. Periodic tokens can
help with secret-zero bootstrapping. The docs also note that trusted-IP
restrictions are paid on Infisical Cloud, so local runtime safety still needs
OS keyring, short token lifetimes, service boundaries, and careful redaction.

WardenClyffe use:

- keep Infisical as canonical hosted secret source for now.
- keep OS keyring as runtime local cache.
- keep secret values out of docs, logs, and examples.

## Architecture Rule

Do not let any foundation app become the product architecture by accident.

Warden should own:

- desired state.
- approvals.
- audit.
- health.
- rollback.
- route/resource ownership.
- customer-safe projections for Clyffe.

The foundation apps execute specialized jobs underneath Warden.

## Sources Checked

- OPNsense 26.1 release notes: `https://docs.opnsense.org/releases/CE_26.1.html`
- OPNsense WireGuard API: `https://docs.opnsense.org/development/api/core/wireguard.html`
- OPNsense Unbound docs: `https://docs.opnsense.org/manual/unbound.html`
- Authentik OAuth2/OIDC provider docs: `https://docs.goauthentik.io/add-secure-apps/providers/oauth2/`
- Authentik blueprints docs: `https://docs.goauthentik.io/customize/blueprints/`
- Caddy automatic HTTPS docs: `https://caddyserver.com/docs/automatic-https`
- Caddy reverse proxy docs: `https://caddyserver.com/docs/caddyfile/directives/reverse_proxy`
- Caddy API docs: `https://caddyserver.com/docs/api`
- Traefik configuration docs: `https://doc.traefik.io/traefik/getting-started/configuration-overview/`
- PowerDNS HTTP API docs: `https://doc.powerdns.com/authoritative/http-api/`
- PowerDNS DNSSEC docs: `https://doc.powerdns.com/authoritative/dnssec/`
- PostgreSQL docs: `https://www.postgresql.org/docs/`
- Qdrant multitenancy docs: `https://qdrant.tech/documentation/manage-data/multitenancy/`
- Qdrant backup docs: `https://qdrant.tech/documentation/cloud/backups/`
- SurrealDB 3.0 overview: `https://surrealdb.com/3.0`
- Infisical Universal Auth docs: `https://infisical.com/docs/documentation/platform/identities/universal-auth`
- Zitadel production setup docs: `https://zitadel.com/docs/self-hosting/manage/production`

