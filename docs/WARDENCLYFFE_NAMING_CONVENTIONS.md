---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: naming-conventions
  persona: clyffy-operator
  kind: naming-conventions
  owner: docs/WARDENCLYFFE_NAMING_CONVENTIONS.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  sync:
    qdrant: true
    surreal: true
---

# WardenClyffe Naming Conventions

The naming standard is boring, consistent, and explicit.

## Product Names

| Name | Use |
|---|---|
| WardenClyffe | umbrella platform |
| Warden | operator/server manager and Proxmox control plane |
| Clyffe | customer portal, knowledge base, tickets, CRM |
| Clyffy | assistant/persona only |
| Clyffe Code | the customer hosted-workspace **product** (locked 2026-06-11) |
| Devstation | the **VM substrate** the service runs on |
| DevForge | the hosted **browser-VSCode IDE tile only** — never the product |
| Cortex | the control+intelligence backbone exposed via the MCP gateway |
| `.pulse` | the signed modular update packet format (app \| clyffy \| minion) |

The Clyffy family is **three distinct things — never mixed** (locked 2026-06-11):

| Name | Is |
|---|---|
| Clyffy | the assistant persona/surface (`clyffy.ai`) |
| Clyffy-Go | the Go codebase — orchestrator/SDK and the **master AI template** |
| Clyffy-Dean | the Proxmox/infra **ops MCP agent** (and E→W mover) — not the assistant |

Do not use Clyffy as a database schema, service name, permission namespace, or
infrastructure authority unless the thing is specifically the assistant or
persona layer.

## Module Labels

| Label | Durable name |
|---|---|
| Module 1 | Warden |
| Module 2 | Clyffe |

Module labels are allowed in roadmaps and planning docs. Durable code uses the
product names.

## Files And Directories

Use lowercase kebab-case for new directories, binaries, services, and package
names:

```text
warden
clyffe
warden-api
warden-mcp
clyffe-api
wardenclyffe-disk
wardenclyffe-net
wardenclyffe-scale
```

Product module roots are:

```text
modules/warden
modules/clyffe
modules/shared
```

Do not create durable code under `modules/module1` or `modules/module2`.

Use uppercase snake-case only for repo-level docs that are meant to be visible
in listings:

```text
WARDENCLYFFE_MODULE_MAP.md
WARDENCLYFFE_BACKEND_OPTIONS_2026_05.md
PROXMOX_FREE_CHEATSHEET.md
```

## API Names

Use stable resource names:

```text
/api/warden/...
/api/clyffe/...
/api/mesh/...
/api/proxmox/...
```

Customer-facing Clyffe APIs must never expose Proxmox token names, Proxmox
paths, or raw Proxmox errors. They should expose Warden-owned resources and
safe action names.

## Database Names

Use product-bounded schemas:

```text
warden_core
warden_infra
warden_audit
clyffe_core
clyffe_support
clyffe_kb
clyffe_crm
ai_bridge
```

Use short table names inside schemas:

```text
clyffe_support.tickets
clyffe_kb.articles
warden_infra.resources
warden_audit.events
```

Avoid table names like `clyffe_support_clyffe_support_tickets`.

## MCP Names

Follow the Context Mesh grammar:

```text
mcp.global.proxmox
mcp.global.warden
mcp.global.clyffe
tools.global.proxmox
resources.global.warden
observability.mcp.global.proxmox
```

Tool names use:

```text
<domain>.<verb>_<object>
```

Examples:

```text
proxmox.list_nodes
proxmox.list_vms
proxmox.get_task
proxmox.plan_lifecycle
warden.mesh_status
clyffe.list_services
```

Rules:

- Read-only tools use `list`, `get`, `search`, `read`, `inspect`, or
  `validate`.
- Mutating tools use explicit verbs and require approval policy.
- Tool names stay stable across global, estate, and project scopes.
- Scope belongs in the MCP server ID and policy namespace, not in the tool
  name.

## Markdown Touchpoints

Markdown touchpoints should be short routers, not long copied manuals.

Every touchpoint should answer:

- What subsystem this file owns.
- Which registry or database owns the truth.
- Which agents should read it.
- What is safe to change.
- What requires operator approval.

Use frontmatter:

```yaml
---
wardenclyffe_touchpoint:
  version: 1
  kind: subsystem
  namespace: wardenclyffe.example
  owner: docs/example.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
---
```

## Variables, Functions, And Triggers

The code-level law (locked 2026-06-11; derived from the shipped Go services,
the identity/tenancy spec, and the structure standard — write code that looks
like the code that exists).

Go:

```text
package name == context (lower, no underscores)
constructors: New, NewStore, NewHandler, NewService
methods: Verb or VerbObject — List, Get, CreateRequested, Provision
errors: ErrX (ErrInvalid, ErrNotFound, ErrForbiddenSchema)
no abbreviations; no single-letter names outside tiny scopes
```

Database (PostgreSQL 18 baseline):

```text
functions: <schema>.<verb>_<object>     identity.verify_email, core.set_updated_at
triggers:  trg_<purpose>                trg_audit, trg_set_updated_at, trg_email_reverify
primary keys: uuid default uuidv7()
tables: short nouns inside product-bounded schemas (warden_infra.resources)
```

React / TypeScript:

```text
components: PascalCase.tsx              screens: <Name>View.tsx
services:   <context>.svc.ts            types: types.ts (colocated)
hooks:      useX                        vars/functions: camelCase
landing sections: sections/<NN>-<slug>/section.tsx + section.meta.ts + section.css
```

Workflow triggers (the automation layer):

```text
MCP tools:        <domain>.<verb>_<object>      cortex.commit_decision
agent hooks:      <purpose>-hook.py             parking-lot-hook.py
SurrealDB events: DEFINE EVENT <verb>_<object>
.pulse lifecycle: fixed verbs notify -> freeze -> apply -> restart
```

## Frontend Domains

The canonical `src/domains/` set is exactly: `landing`, `warden`, `clyffy`,
`admin` (locked 2026-06-11). The customer plane is folded under `admin` for
now; the Go `clyffe-api` boundary still stands.

## Forbidden Drift

Do not add new names that mean the same thing:

- `client panel`, `customer panel`, and `Clyffe` should collapse to Clyffe.
- `server panel`, `admin panel`, and `Warden` should collapse to Warden.
- `meshnode`, `context mesh`, and `MCP mesh` need clear use:
  - Context Mesh is the naming and policy fabric.
  - MCP server is a protocol endpoint.
  - Meshnode is a deployed node participating in the mesh.
