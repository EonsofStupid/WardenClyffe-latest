---
clyffy_touchpoint:
  version: 2
  workspace_id: clyffy.master
  project_key: clyffy-mcp-orchestrator
  persona: clyffy-operator
  kind: orchestrator-contract
  owner: docs/CLYFFY_MCP_ORCHESTRATOR.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/FOUNDATION_SERVICE_MATRIX.md
    - docs/WARDEN_CLYFFE_ARCHITECTURE.md
    - docs/ai/MCP_MESH_TOUCHPOINTS.md
    - docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md
    - docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md
    - docs/ai/TOUCHPOINT_SYNC_PATTERN.md
    - wardenclyffe/registry/context-mesh.yaml
  sync:
    qdrant: true
    surreal: true
---

# Clyffy MCP Orchestrator

Clyffy is the main MCP orchestrator for WardenClyffe. Warden remains the
infrastructure control plane. Clyffe remains the customer portal. Clyffy
organizes the agent, tool, and intelligence layer so humans, agents, and
services work against the same foundation without re-learning context every
session.

## Boundary

| Layer | Authority | Purpose |
|---|---|---|
| OPNsense | network truth | VLANs, firewall policy, WireGuard/WardenNet, split DNS, route reachability |
| Authentik | human identity truth | SSO, MFA/passkeys, app launcher, OIDC clients, operator/customer groups |
| Better Auth | app-local auth layer | secure local sessions and API identity inside Clyffy, minions, and customer apps |
| Warden | infrastructure/control truth | Proxmox, hosts, guests, DNS intent, edge routes, approvals, tasks, audit |
| Bifrost | bridge/orchestration gateway | controlled crossing between Clyffy, Warden, minions, providers, and internal APIs |
| Clyffy Master VM | assistant runtime | dedicated personal assistant, project memory, agent handoff, Clyffy UI/API |
| AI Observatory | LLM observability/proxy | WardenClyffe-owned Helicone-like traces, spend, latency, quality, provider routing |
| Postgres | product truth | tenants, RBAC, CRM, tickets, inventory, lifecycle, billing refs, audit |
| Qdrant | retrieval memory | embeddings for docs, KB, touchpoints, runbooks, project memory snippets |
| SurrealDB | graph projection | workspace graph, tool graph, handoff graph, reasoning projection |

The short rule:

```text
Clyffy orchestrates context and tools.
Warden executes infrastructure authority.
Clyffe exposes only customer-safe outcomes.
```

## Existing Foundation Mapping

| Guest | Role in orchestrator |
|---|---|
| LXC `102` `warden` | current Warden control-plane app to absorb/extend |
| LXC `103` `authentik` | central SSO/IdP and MCP/Auth specialist target |
| LXC `104` `surreal` | AI graph/reasoning projection plane |
| LXC `106` `qdrant` | vector retrieval plane |
| LXC `108` `clyffy-stepca` | internal CA and trust fabric |
| LXC `109` `clyffy-pdns` | internal authoritative DNS |
| LXC `110` `clyffy-pg-master` | Warden/Clyffe product-truth Postgres |
| VM `111` `edge` | existing boundary VM, pending audit/config completion; not the standalone Caddy service |
| LXC `112` `clyffy-bifrost` | bridge/LLM gateway layer |
| LXC `113` `observatory` | WardenClyffe-owned Helicone-like AI observability |
| LXC `114` `warden-operator-capsule` | secret-sensitive operator shell/agent capsule |
| VM `116` `warden-devstation-01` | private devstation and Clyffe Code seed pattern |
| LXC `115` `clyffy-edge` | clean standalone Caddy public edge |
| LXC `120` proposed `clyffy-master` | dedicated Clyffy master assistant/UI/API runtime |

## MCP Shape

Clyffy Master should run the workspace gateway:

```text
mcp.workspace.clyffy-master-gateway
```

That gateway is the main front door for Clyffy-orchestrated work. It routes to
focused leaves:

- `mcp.workspace.clyffy-master.authentik` for identity read/admin support;
- `mcp.global.warden` for Warden control-plane status and approved actions;
- `mcp.global.dns` for Cloudflare, PowerDNS, split DNS, route readiness, and
  domain verification through Warden policy;
- `mcp.global.proxmox` through Warden policy, never directly to customers;
- `mcp.global.opnsense` for network/firewall/VPN intent;
- `mcp.global.agent-runtime` for devstation/capsule agent stream status and
  future audited open-intent actions;
- `mcp.workspace.clyffy-master.bifrost` for provider/minion bridge actions;
- `mcp.workspace.clyffy-master.observatory` for LLM traces, usage, spend, and quality;
- `mcp.global.qdrant` for retrieval health and approved search;
- `mcp.global.surreal` for graph projection health and approved reads;
- `mcp.global.postgres` for migrations/status only through Warden policy.

The gateway owns routing, policy, trace propagation, and workspace-level auth.
Leaf servers stay small and boring.

## May 2026 Production Bar

Every formal remote MCP server or gateway should meet this bar:

| Capability | Requirement |
|---|---|
| Transport | Streamable HTTP for remote servers; stdio only for local/private integrations |
| Auth | OAuth 2.1 resource-server behavior, RFC 9728 protected resource metadata, audience-restricted tokens |
| Discovery | Server Card plus workspace card where applicable |
| Observability | OpenTelemetry MCP semantic conventions and trace context through `params._meta` |
| Tool names | domain-prefixed dotted names, with aliases only for migration |
| State | stateless gateway by default; durable tasks for long-running operations |
| Safety | deny precedence, tenant/workspace scope, approval tasks for writes |
| UX | Warden UI shows gateway health, leaf health, sync age, and blocked decisions |

## Maintenance Model

The better maintenance method is registry-as-code plus generated projections:

1. `wardenclyffe/registry/context-mesh.yaml` is the MCP registry source.
2. Markdown touchpoints declare ownership and safe routing context. They are
   manifests, not durable memory stores.
3. `scripts/foundation/validate-touchpoints.py` inventories touchpoints.
4. A sync worker validates registry plus touchpoints, hashes content, and emits:
   - Qdrant points for retrieval;
   - SurrealDB graph rows for workspace/tool/doc/task relationships;
   - Postgres task/audit links for episodic execution history;
   - generated context packs for the current agent run;
   - Warden UI status for stale, duplicate, missing, or broken routes.
5. Warden UI edits create pull requests or Warden tasks, not silent drift.
6. Clyffy reads the generated graph/retrieval layer and cites the source
   touchpoint or registry entry when answering or proposing actions.

Do not make the UI, Qdrant, or SurrealDB the source of truth for tool routing.
They are projections. The registry and touchpoints are the authored contract.
Clyffy should not solve context by growing giant Markdown memory files; it
should load small touchpoints, query generated stores, and build disposable
context packs.

## First UI Tabs

Warden should expose these operator tabs for the orchestrator:

| Tab | Shows |
|---|---|
| Foundation | OPNsense, Authentik, Warden, Bifrost, Clyffy Master, AI Observatory, Postgres, Qdrant, SurrealDB |
| Network | OPNsense/WardenNet, VLANs, split DNS, public/private route posture |
| Identity | Authentik apps/groups/claims plus Better Auth app-local boundaries |
| Clyffy Master | VM/LXC status, assistant runtime, model/embedder provider, active workspaces |
| MCP Mesh | gateway status, leaf status, tool list, server cards, auth posture |
| Intelligence | touchpoint inventory, Qdrant sync, SurrealDB projection, stale routes |
| Domains | Cloudflare zones, PowerDNS records, split DNS intent, route readiness |
| Agent Streams | devstation/capsule Codex and Claude streams, attach/open status |
| Bifrost | provider bridge, minion bridge, rate limits, policy failures |
| AI Observatory | traces, usage, cost/spend, provider health, prompt quality signals |
| Tasks/Audit | Warden approvals, MCP calls, tool runs, failed/blocked actions |

## Immediate Implementation Order

1. Keep the capsule/devstation as internal operator workspaces.
2. Audit and configure OPNsense as network authority.
3. Finish Authentik as central app launcher/IdP.
4. Stand up Clyffy Master as the dedicated assistant runtime.
5. Promote the Clyffy Master gateway as the orchestrator front door.
6. Wrap Bifrost and Observatory as first-class WardenClyffe services.
7. Promote DNS/domain management as `mcp.global.dns` behind Warden policy.
8. Promote devstation/capsule agent stream status as `mcp.global.agent-runtime`.
9. Build the sync worker from registry/touchpoints to Qdrant/SurrealDB using
   `docs/ai/SURREALDB_INTELLIGENCE_PROJECTION_V2.md` and the additive schema
   in `schemas/intelligence/surreal-touchpoint-projection.v2.surql`.
10. Extend Warden UI tabs against generated status contracts.
