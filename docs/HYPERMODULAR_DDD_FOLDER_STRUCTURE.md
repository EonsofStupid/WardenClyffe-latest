---
wardenclyffe_touchpoint:
  version: 1
  kind: ddd-folder-structure
  namespace: wardenclyffe.structure.ddd
  owner: docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md
  reads:
    - docs/WARDENCLYFFE_MODULE_MAP.md
    - docs/WARDENCLYFFE_NAMING_CONVENTIONS.md
    - modules/README.md
---

# Hypermodular DDD Folder Structure

This is the canonical root scaffold for WardenClyffe product work.

The structure is intentionally boring:

- modules are product surfaces.
- bounded contexts own domain language.
- interfaces expose the domain.
- infrastructure adapts external systems.
- contracts describe cross-module/API boundaries.
- touchpoints route agents without becoming duplicate truth.

## Root Shape

```text
modules/
  warden/
    bounded-contexts/
    interfaces/
    infrastructure/
    contracts/
  clyffe/
    bounded-contexts/
    interfaces/
    infrastructure/
    contracts/
  shared/
    contracts/
    primitives/
    observability/
```

## Product Modules

| Path | Module | Purpose |
|---|---|---|
| `modules/warden/` | Module 1: Warden | Proxmox UI manager, operator control plane, infrastructure graph, Warden API, MCP mesh observatory |
| `modules/clyffe/` | Module 2: Clyffe | customer portal, knowledge base, tickets, CRM, customer-safe service panel |
| `modules/shared/` | Shared kernel | contracts and tiny primitives shared across Warden and Clyffe |

Do not place real product code in `WardenClyffe-module1/` or
`WardenClyffe-module2/`. Those are currently AI/runtime payloads and need a
separate classification pass.

## DDD Layer Rules — SUPERSEDED (2026-06-11)

> **Demoted by operator ruling (P1-B).** The per-context
> `domain/application/infrastructure/interface/tests` interior below is
> **historical** and must not be used for new code. The law for context
> internals is the **flat v2 shape** in
> `docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md`
> (`<context>.go` / `service.go` / `handler.go`; React
> `types.ts` / `<context>.svc.ts` / `components/` / `views/` / `index.ts`).
> The root scaffold and the idempotent naming rules in THIS doc remain valid.

Historical shape (do not use):

```text
<context>/
  domain/  application/  infrastructure/  interface/  tests/
```

Import direction (still the rule, expressed in the flat shape):

```text
handler -> service -> <context> (domain + store);  platform is leaf infra
domain code imports no transport
```

## Idempotent Naming Rules

Everything that can be re-run should have stable keys:

- host ids: `host.<region>.<slug>`
- node ids: `node.<region>.<slug>`
- resource ids: `resource.<provider>.<node>.<vmid>`
- action ids: `action.<date>.<short-random>`
- tenant ids: stable UUIDs or `tenant.<slug>` in fixtures only.
- MCP server ids: `mcp.<scope>.<domain>`
- tool ids: `<domain>.<verb>_<object>`

Repeated discovery must update existing records by stable identity, not create
duplicates.

## Self-Describing Folder Contract

Every top-level module and bounded context should include:

- `README.md` for humans and agents.
- `AGENTS.md` when local agent rules differ from root.
- `module.toml` for product modules.
- `context.toml` for bounded contexts once implementation begins.

No folder should require guessing what it owns.

## Warden Contexts

| Context | Owns |
|---|---|
| `proxmox` | Proxmox API models, task polling, lifecycle plans, host-local helper policy |
| `fleet` | hosts, nodes, regions, provider inventory, onboarding |
| `mesh` | Context Mesh registry view, MCP observability, touchpoint sync health |
| `identity` | OIDC/bootstrap identity integration and operator/customer subject mapping |
| `audit` | append-only action/event history |
| `automation` | planned/applied jobs, approvals, schedules |

## Clyffe Contexts

| Context | Owns |
|---|---|
| `services` | customer-visible services, assigned VMs/containers, safe actions |
| `support` | tickets, messages, incidents, approvals |
| `knowledge-base` | customer-safe docs, KB articles, AI-safe source boundaries |
| `account` | organizations, contacts, CRM notes, plan/account view |

## Shared Contexts

Shared code must stay small:

- cross-module DTO contracts.
- ids and tiny value primitives.
- observability event envelopes.
- no business workflow ownership.

If shared code starts making decisions, move it back into Warden or Clyffe.

