---
wardenclyffe_touchpoint:
  version: 1
  kind: go-warden-absorption-plan
  namespace: wardenclyffe.absorb-go
  owner: docs/GO_WARDEN_ABSORPTION_PLAN.md
  source_repo: wardenclyffe/
  target_module: module-01-warden
---

# Go Warden Absorption Plan

This plan folds the existing Go Warden work into the WardenClyffe direction
without losing working behavior or confusing it with Clyffe.

## Current Execution Decision

As of the 2026-05-26 foundation reset, **Go Warden is the active implementation
authority** for the next slice. Rust remains parked/reference material until the
Go Warden source of truth, storage/workspace foundation, Proxmox coverage, and
Warden/Clyffe boundaries are boring and verified.

Do not start new Rust implementation work as part of this plan. When this file
mentions Rust contracts below, treat them as future replacement criteria, not
current execution instructions.

## Source Material

Primary Go Warden sources:

- `wardenclyffe/AGENTS.md`
- `wardenclyffe/REGISTRY.md`
- `wardenclyffe/warden/proxmox.go`
- `wardenclyffe/warden/infrastructure_graph.go`
- `wardenclyffe/warden/internal/host/`
- `wardenclyffe/agent/clyffy-dean/`
- `wardenclyffe/registry/context-mesh.yaml`
- `wardenclyffe/docs/specs/02-proxmox-connector.md`
- `wardenclyffe/docs/specs/09-context-mesh-and-naming.md`
- `wardenclyffe/docs/specs/05-clyffe-client.md`

Do not mutate the nested Go repo as part of absorption unless the task is
explicitly about that repo.

## Target Shape

| Target | Role |
|---|---|
| `warden` | Module 1 operator app, Proxmox UI manager, Warden API |
| `warden-mcp` | formal MCP server/gateway for Warden and Proxmox domains; Go-side behavior first, Rust formalization later |
| `clyffe` | Module 2 customer portal, customer-safe API consumer |
| `context-mesh` | registry, policy, touchpoints, and observability contract |
| `ai-memory` | Markdown touchpoints, SurrealDB projections, Qdrant vectors |

Exact directories are not decided yet. The naming above is the product
boundary to preserve during migration.

## Absorption Order

### Phase 0: Freeze vocabulary

- Module 1 means Warden.
- Module 2 means Clyffe.
- Clyffy means assistant/persona.
- Proxmox is substrate, not customer-facing brand.
- Warden owns Proxmox authority.
- Clyffe consumes Warden tenant-safe APIs.

### Phase 1: Port Proxmox client model

Absorb from Go:

- `ProxmoxConfig`
- `ProxmoxResource`
- `ProxmoxStorage`
- `ProxmoxNodeStatus`
- `ProxmoxInventory`
- API token auth header behavior
- version/auth probe
- inventory endpoints
- lifecycle and snapshot actions
- audit-after-action pattern

The eventual replacement target should split this into:

```text
warden-proxmox-client
warden-proxmox-model
warden-proxmox-policy
warden-proxmox-tasks
warden-proxmox-audit
```

### Phase 2: Add task truth

The Go version returns API acceptance for actions. Warden needs task truth:

- capture returned UPID.
- poll task status.
- fetch task log.
- store final outcome.
- expose task timeline in Warden.
- expose sanitized status in Clyffe.

### Phase 3: Port infrastructure graph

Absorb `infrastructure_graph.go` as the base visual model:

- providers.
- hosts.
- LXCs/VMs.
- services.
- routes.
- customers/tenants.
- health widgets.
- exposure posture.
- ownership edges.

Extend it with Context Mesh graph nodes:

- MCP servers.
- MCP-shaped services.
- tools/resources/prompts.
- namespace/project overlays.
- Qdrant/Surreal projection status.
- stale touchpoint warnings.

### Phase 4: Promote Context Mesh

Use the Go registry as the source material:

- `wardenclyffe/registry/context-mesh.yaml`
- `wardenclyffe/docs/specs/09-context-mesh-and-naming.md`
- `wardenclyffe/agent/mcp-cluster/`

Root WardenClyffe should end with one promoted registry, not two.

### Phase 5: Build Clyffe against Warden API

Clyffe starts only after Warden has:

- resource ownership model.
- tenant membership model.
- customer-safe action request model.
- tickets/support schema.
- knowledge-base schema.
- audit events.

Clyffe should not inherit Warden's operator pages.

## Migration Rules

- Port concepts, tests, and behavior before changing names.
- Preserve working Go behavior as the active reference until a future
  replacement has tests and operator approval.
- Do not port secrets, local paths, or live credential examples.
- Do not duplicate Proxmox policy in multiple services.
- Do not let Clyffe call Proxmox directly.
- Do not let AI memory become product truth.

## Parked Future Contracts

Minimum future contracts before replacing Go behavior:

```text
ProxmoxClient
ProxmoxInventoryService
ProxmoxTaskService
WardenAuditSink
WardenPolicyGate
WardenResourceOwnership
ContextMeshRegistry
TouchpointIndexer
QdrantProjector
SurrealProjector
```

Each contract needs tests with fixture JSON from Proxmox-style responses. This
section is intentionally parked until Go Warden is the clean, committed,
operational source of truth.

## Success Criteria

Go Warden can be considered ready for a replacement conversation when the next
implementation can:

1. show the same inventory as Go Warden.
2. perform the same lifecycle actions with stricter task polling.
3. render a better infrastructure graph.
4. expose the same or better Context Mesh registry view.
5. serve Clyffe through tenant-safe APIs.
6. pass tests without relying on live Proxmox.
