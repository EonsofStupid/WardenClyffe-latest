---
wardenclyffe_touchpoint:
  version: 1
  kind: module-map
  namespace: wardenclyffe.modules
  owner: docs/WARDENCLYFFE_MODULE_MAP.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDEN_CLYFFE_ARCHITECTURE.md
    - docs/WARDENCLYFFE_NAMING_CONVENTIONS.md
    - docs/GO_WARDEN_ABSORPTION_PLAN.md
---

# WardenClyffe Module Map

This is the canonical boring product split.

## Product Modules

| Module | Name | Purpose | Audience | Authority |
|---|---|---|---|---|
| Module 1 | Warden | Proxmox UI manager, operator control plane, infrastructure API, MCP mesh observatory | trusted operators | owns infrastructure authority |
| Module 2 | Clyffe | customer portal, customer knowledge base, tickets, CRM, customer-safe service actions | customers and support staff | consumes Warden APIs |

Module numbers are planning labels. Product names are the durable names.
Code, packages, APIs, database schemas, and UI copy should use `warden` and
`clyffe`, not vague numbered module names.

## Current Repository Reality

The current root repo is not cleanly aligned yet:

| Path | Current role | Canonical destination |
|---|---|---|
| `modules/warden/` | canonical scaffold for Module 1 Warden | future Warden product module root |
| `modules/clyffe/` | canonical scaffold for Module 2 Clyffe | future Clyffe product module root |
| `modules/shared/` | canonical scaffold for shared contracts and tiny primitives | shared kernel only |
| `wardenclyffe/` | nested Go Warden repo with the strongest Proxmox, MCP mesh, Authentik, SurrealDB, Qdrant, and roadmap material | source material for Module 1 Warden |
| `src/` | Rust WardenClyffeScale/MariaDB replication code | keep as Scale product until explicitly merged or moved |
| `wardenclyffedisk/` | Rust storage/disk component | shared infrastructure component under Warden |
| `wardenclyffenet/` | Rust network component | shared infrastructure component under Warden |
| `web/` | PHP/static documentation surface | documentation site, later static-first |
| `WardenClyffe-module1/` | large AI runtime repo, appears to be mistral.rs based | not Module 1 Warden until renamed/reclassified |
| `WardenClyffe-module2/` | large AI/runtime repo, appears to be Burn based | not Module 2 Clyffe until renamed/reclassified |

Do not let the current `WardenClyffe-module1` and `WardenClyffe-module2`
folder names define the product architecture. They need a separate vendor or
AI-runtime classification pass before any product-module rename.

## Module 1: Warden

Warden becomes the Proxmox UI manager and operator command center.

Required surfaces:

- Proxmox node, VM, LXC, storage, network, task, backup, snapshot, console, and
  cluster views.
- Public IP, DNS, TLS, and edge-route management for the WardenClyffe homebase.
- Federated node-network graph showing Warden nodes, Proxmox nodes, Clyffy
  nodes, services, routes, and MCP mesh health.
- Drilldown from estate to namespace/project to internal MCP mesh.
- Operator-safe action model: plan, approve, execute, poll task, audit.
- Warden API for Clyffe. Clyffe never talks directly to Proxmox.
- MCP mesh observatory for tools, resources, prompts, policies, traces,
  latency, failures, and context freshness.

## Module 2: Clyffe

Clyffe is the customer-facing service panel.

Required surfaces:

- Customer dashboard for assigned VMs, containers, services, domains,
  databases, backups, tickets, and service health.
- Customer-safe actions: start, stop, restart, console, approved rebuild,
  backup restore request, support request.
- Knowledge base and AI-assisted help that only uses customer-safe sources.
- Tickets, customer notes, contact records, organization records, and CRM
  history.
- Tenant-scoped API access through Warden.

## Shared Components

These components support Warden and Clyffe but are not product modules:

| Component | Role |
|---|---|
| WardenClyffeDisk | shared storage substrate and managed storage feature |
| WardenClyffeNet | shared network substrate and managed network feature |
| WardenClyffeScale | database replication/managed database feature, currently MariaDB focused |
| Context Mesh | MCP naming, policy, observability, and routing fabric |
| Public edge and DNS | Warden-owned public IP ingress, domain routing, TLS, split-horizon DNS |
| Qdrant | vector retrieval and semantic search |
| SurrealDB | AI graph/reasoning projection where useful |
| Object storage | artifacts, reports, exports, docs assets, backup metadata |

## Naming Rule

Use this boring grammar:

```text
module-01-warden       planning label
module-02-clyffe       planning label
warden                 product/code/API noun
clyffe                 product/code/API noun
wardenclyffe           umbrella/platform noun
wardenclyffe-disk      component package or service
wardenclyffe-net       component package or service
wardenclyffe-scale     component package or service
```

Avoid:

- `module1` or `module2` in durable code paths.
- personality names in service IDs, database schemas, permissions, or MCP tool
  names.
- duplicate sources of truth for mesh registries.

## First Alignment Target

The first clean internal build should expose:

1. Warden Proxmox dashboard.
2. Warden infrastructure graph.
3. Warden MCP mesh observatory.
4. Warden tenant/resource ownership model.
5. Clyffe read-only customer dashboard against Warden API.
6. Clyffe ticket and knowledge-base skeleton.

See `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md` for the folder contract and
`modules/README.md` for the physical scaffold.
