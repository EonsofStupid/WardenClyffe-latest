---
wardenclyffe_touchpoint:
  version: 1
  kind: modern-patterns
  namespace: wardenclyffe.patterns.2026-05
  owner: docs/WARDENCLYFFE_MODERN_PATTERNS_2026_05.md
---

# Modern Patterns To Capture - May 2026

This is a pattern list for WardenClyffe. It is not a final architecture
decision.

## 1. Control Plane First, Substrate Second

Warden should model Proxmox as a substrate:

```text
Proxmox raw resource -> Warden resource -> Clyffe service
```

Warden owns the product model, policy, audit, and customer boundary. Proxmox
owns KVM/LXC/storage/network execution until WardenClyffe has a reason to
replace a specific workflow.

## 2. Task Truth, Not Fire-And-Forget

Every infrastructure write should return a Warden action record and poll the
underlying Proxmox task until final outcome.

Pattern:

```text
plan -> approve -> execute -> UPID/task id -> poll -> audit -> notify
```

This is more important than adding more buttons.

## 3. Product Truth Separate From AI Memory

Keep customer/product truth in the chosen relational control plane. Project
curated AI context outward.

Pattern:

```text
product DB -> ai_bridge/outbox -> SurrealDB graph projection -> Qdrant vectors
```

Do not dual-write handlers directly into both product DB and AI stores.

## 4. Markdown Touchpoints As Routing, Not Storage

Markdown touchpoints should be the low-friction human/agent routing layer.
They should not replace the product database.

Pattern:

```text
short touchpoint -> indexed summary -> graph edge -> vector search
```

## 5. Federated Context Mesh

Use many focused MCP domains rather than one giant tool server:

```text
mcp.global.proxmox
mcp.global.warden
mcp.global.clyffe
mcp.global.qdrant
mcp.global.surreal
```

Warden can provide a gateway and observatory, but leaf servers should keep
their ownership and policy boundaries.

## 6. Graph UI For Infrastructure And Mesh

Warden needs a graph view that can drill down:

```text
estate -> node -> namespace/project -> service/resource -> MCP mesh -> tool
```

Graph nodes should include:

- Proxmox nodes.
- Warden nodes.
- Clyffy/Clyffe nodes.
- VMs and containers.
- customer/service ownership.
- routes and public exposure.
- MCP servers.
- Qdrant and SurrealDB sync status.
- stale touchpoints and failed jobs.

## 7. Static-First Docs, App-First Portal

Static documentation should not require PHP. Use a static-first docs build
when the docs surface is separated from the app.

Good candidates:

- Astro for docs/site content.
- Vite/SvelteKit/React only if the page is truly an app surface.
- Caddy or Nginx for static serving.

Clyffe is not just static content. It needs a real app surface for auth,
service ownership, tickets, CRM, KB, and action requests.

## 8. OIDC-First Auth Boundary

Warden and Clyffe should assume external OIDC even if the first pilot has a
local bootstrap admin.

Pattern:

```text
IdP -> Warden session -> Warden policy -> Warden API -> Clyffe scoped view
```

ZITADEL, Authentik, Keycloak, and Ory remain options. The provider choice is
not locked here.

## 9. Database Decision By Workload

Do not pick one backend because it is fashionable.

Use workload roles:

- relational product truth.
- database-manager/managed database support.
- vector retrieval.
- graph/reasoning projection.
- cache/session/queue.
- object storage.

See `docs/WARDENCLYFFE_BACKEND_OPTIONS_2026_05.md`.

## 10. Boring Names Everywhere

Use Warden, Clyffe, Context Mesh, Qdrant, SurrealDB, WardenClyffeDisk,
WardenClyffeNet, and WardenClyffeScale consistently.

Do not use placeholder module names, assistant names, or marketing names for
durable APIs, schemas, services, or permissions.

## Primary References

- Proxmox VE features and API:
  https://www.proxmox.com/en/products/proxmox-virtual-environment/features
  https://pve.proxmox.com/wiki/Proxmox_VE_API
- MCP lifecycle and Streamable HTTP:
  https://modelcontextprotocol.io/specification/2025-11-25/basic/lifecycle
  https://modelcontextprotocol.io/specification/2025-06-18/basic/transports
- PostgreSQL release notes:
  https://www.postgresql.org/docs/release/
- MariaDB 11.8 LTS:
  https://mariadb.com/docs/release-notes/community-server/11.8/what-is-mariadb-118
- Qdrant documentation:
  https://qdrant.tech/documentation/
- SurrealDB overview:
  https://surrealdb.com/docs/what-is-surrealdb
- Dragonfly docs:
  https://www.dragonflydb.io/docs

