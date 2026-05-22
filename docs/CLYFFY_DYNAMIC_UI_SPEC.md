---
wardenclyffe_touchpoint:
  version: 1
  kind: clyffy-dynamic-ui-spec
  namespace: wardenclyffe.clyffy.dynamic-ui
  owner: docs/CLYFFY_DYNAMIC_UI_SPEC.md
  module: module-02-clyffe
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/MASTER_CLYFFY_ROLLOUT_PLAN.md
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - docs/CLYFFY_DYNAMIC_UI_POAM.md
    - modules/clyffe/interfaces/assistant/README.md
    - modules/clyffe/bounded-contexts/code-workspaces/README.md
    - modules/clyffe/bounded-contexts/dynamic-content/README.md
---

# Clyffy Dynamic UI Spec

Clyffy is the assistant/persona surface. The first production target is
`master.clyffy.ai`, the personal/master assistant for WardenClyffe operations.
Clyffe remains the customer portal. Warden remains the operator/server-control
authority.

The Clyffy UI must populate from live contracts, content registries, and
approved intelligence projections. It must not become another hand-edited
static page.

## Product Promise

The first useful version should feel like:

```text
Open Clyffy.
See current workspaces, infrastructure status, tasks, docs, and assistant state.
Click a workspace.
Open it locally in VS Code/Cursor, or use browser fallback.
Ask Clyffy what is next.
See answers grounded in Warden inventory, docs, tickets, and touchpoints.
```

For vibe coders, Clyffe Code extends that pattern:

```text
Install Clyffe Connect.
Sign in.
Pick repo and power tier.
Click Open.
Work locally while builds, terminals, agents, previews, and storage run on the
remote devstation.
```

## Boundaries

| Surface | Audience | Owns | Must not do |
|---|---|---|---|
| Warden | operator | Proxmox, hosts, routes, DNS, certs, inventory, audit | expose raw operator power to customers |
| Clyffy | owner/operator assistant | assistant state, project context, Clyffy workspaces, handoffs | become the customer portal or store product truth |
| Clyffe | customer/support | portal, KB, tickets, CRM, customer-safe service panel | talk directly to Proxmox or operator databases |
| Clyffe Code | customer/developer | hosted dev workspaces through Warden APIs | expose host shell, raw Proxmox, or operator secrets |

## Dynamic Content Sources

| Source | System of record | UI use |
|---|---|---|
| Product truth | Postgres | tenants, accounts, tickets, CRM, services, RBAC, workspaces, billing refs, audit |
| Infrastructure truth | Warden inventory API | hosts, nodes, guests, storage, networks, domains, certs, health, tasks |
| Assistant retrieval | Qdrant | docs, KB, runbooks, touchpoints, project memory snippets |
| AI graph projection | SurrealDB | reasoning graph, workspace graph, agent handoff graph, touchpoint relationships |
| Source docs | Markdown touchpoints | route agents to the right registry and context |
| Secrets | Infisical/keyring/brokered files | runtime only; never UI content |
| Public route intent | Warden edge/DNS API | previews, public app URLs, private tunnel state |

The browser UI reads through Warden/Clyffe/Clyffy APIs only. It does not read
Qdrant, SurrealDB, Proxmox, PowerDNS, Cloudflare, or Infisical directly.

## UI Information Architecture

### Master Clyffy Home

Cards populate dynamically:

- Current focus: active sprint, top blockers, next recommended action.
- Workspaces: Warden Devstation, future Clyffe Code workspaces, status, tier,
  editor launch actions.
- Infrastructure: Wisconsin host, Virginia host, edge, DNS, identity,
  Postgres, Qdrant, SurrealDB, Observability.
- Knowledge: recent touchpoints, POA&M changes, unresolved decisions.
- Assistant: chat, suggested actions, handoff history, linked source snippets.

### Workspace Detail

Each workspace shows:

- editor actions: Open in VS Code, Open in Cursor, browser fallback;
- repo state: branch, dirty state, last sync, current plan;
- resource state: CPU, RAM, disk, uptime, tier, upgrade options;
- previews: forwarded ports, app URLs, health;
- agent state: Codex/Claude/Clyffy tasks, summaries, stalled actions;
- controls: start, stop, restart, snapshot, backup, upgrade request.

### Node Network

The node graph should render:

- physical hosts and regions;
- Proxmox nodes;
- VMs/LXCs;
- service roles;
- route/DNS/cert edges;
- workspace ownership;
- MCP mesh/touchpoint edges;
- health and audit status.

Drill-down order:

```text
fleet -> host -> namespace/project -> workspace -> MCP mesh -> task/action
```

### Dynamic Content Admin

Warden/Clyffy needs a quiet content-control surface:

- content slots;
- card registry;
- nav registry;
- feature flags;
- source health;
- last ingestion time;
- Qdrant collection status;
- SurrealDB projection status.

## API Shape

Initial read endpoints:

```text
GET /api/clyffy/home
GET /api/clyffy/workspaces
GET /api/clyffy/workspaces/{workspace_id}
GET /api/clyffy/node-graph
GET /api/clyffy/content-slots
GET /api/clyffy/assistant/context
GET /api/clyffy/plans/current
```

Initial action endpoints:

```text
POST /api/clyffy/workspaces/{workspace_id}/start
POST /api/clyffy/workspaces/{workspace_id}/stop
POST /api/clyffy/workspaces/{workspace_id}/snapshot
POST /api/clyffy/workspaces/{workspace_id}/open-intent
POST /api/clyffy/actions/request
```

Write endpoints create Warden tasks or approval requests. They do not execute
destructive infrastructure changes inline.

## Content Slot Contract

Every dynamic UI card should resolve from a content slot:

```yaml
id: clyffy.home.current-focus
surface: master-clyffy-home
kind: status_card
source:
  type: warden_api
  endpoint: /api/clyffy/plans/current
refresh_seconds: 30
empty_state: No active plan selected.
visibility:
  persona: clyffy-master
  audience: operator
```

The UI can ship default slots, but production content comes from the registry
and APIs.

## Clyffe Code Local-App Contract

The customer-facing dev workspace must prioritize local editor feel:

1. Clyffe Connect establishes private access.
2. The local app lists assigned workspaces.
3. The user chooses VS Code, Cursor, or browser fallback.
4. VS Code/Cursor run locally and connect over Remote-SSH.
5. Builds, terminals, agents, previews, and file operations run on the remote
   workspace.
6. Warden records lifecycle, tier, preview, task, and audit state.

Browser IDE remains a fallback, not the product promise.

## Security Rules

- No public DNS record points at private workspace IPs.
- Public DNS points only at Warden-controlled edge or jump endpoints.
- Customer workspaces do not inherit operator auth state.
- Secrets enter workspaces through brokered runtime mechanisms only.
- Assistant answers cite approved source documents where possible.
- All write actions are task/audit events.
- Destructive actions require explicit approval.

## First MVP Definition

MVP is complete when:

1. `master.clyffy.ai` serves a dynamic home shell.
2. Home cards come from API responses, not hardcoded page text.
3. Warden Devstation appears as a workspace with VS Code/Cursor/browser actions.
4. The current POA&M and active plan appear in the UI.
5. Node graph shows the current single-host foundation and planned Virginia host.
6. Qdrant/SurrealDB source health is visible as status, even before full
   projections are complete.
7. Every action button creates a Warden task or open-intent event.
