---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: clyffe-code-workspaces
  persona: clyffy-operator
  kind: clyffe-code-workspaces
  owner: modules/clyffe/bounded-contexts/code-workspaces
  module: module-02-clyffe
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md
    - docs/CLYFFY_DYNAMIC_UI_SPEC.md
    - docs/CLYFFY_DYNAMIC_UI_POAM.md
    - docs/ai/TOUCHPOINT_SYNC_PATTERN.md
  sync:
    qdrant: true
    surreal: true
---

# Clyffe Code Workspaces

Clyffe Code is the future customer-safe managed devstation product.

The first product rule is local-app feel:

```text
Local VS Code/Cursor client.
Remote Warden-managed workspace.
Customer never handles SSH, Proxmox, keys, routes, or package bootstrap.
```

Warden owns workspace provisioning, resource tiers, snapshots, backups, route
intent, previews, task state, and audit. Clyffe shows only the customer-safe
workspace surface.

## First Tier Model

| Tier | Fit |
|---|---|
| Starter | small projects and lightweight dev |
| Builder | normal full-stack projects |
| Premium Pilot | internal flagship Clyffy/WardenClyffe workspace |
| Power | heavy builds and concurrent agents |
| GPU | model and media workloads |

The current operator VM is a Builder proof. Premium Pilot is the first target
offer once host resources and rollback are verified.

## Customer UI mock (redline)

Interactive product mock in the root web app (no live provision):

- `/clyffe/code` — workspaces list, double‑click Open
- `/clyffe/code/order` — order tier + agents
- `/clyffe/code/$id` — detail / start / stop
- `/clyffe/code/$id/open` — Connect theater (customer vs behind-the-scenes)

Source: `src/domains/clyffe/code/`. Marked MOCK UI until wired to Warden tasks.

## MVP Customer Actions

- Create workspace from approved template.
- Open in VS Code.
- Open in Cursor.
- Open browser fallback.
- View previews and ports.
- Request resource upgrade.
- Stop/start workspace.
- Request snapshot or restore.

## Extension Direction

The first Clyffe Code extension should make the normal local editor effortless:
sign in, list workspaces, open the workspace, run preflight, show ports, show
tier/usage, request upgrades, request snapshots, and show Warden task status.
It must call Clyffe/Warden APIs and must not call Proxmox directly.

## Intelligence Layer

Clyffe Code workspaces are first-class intelligence surfaces. Each workspace
gets Markdown touchpoints that sync to Qdrant for retrieval and to SurrealDB
for graph/reasoning projection. Warden owns sync jobs and health. Clyffe only
shows customer-safe summaries, KB, project context, and Clyffy assistant state.

## Blocked Actions

- Direct Proxmox access.
- Host shell outside the assigned workspace.
- Operator secret access.
- Arbitrary public route creation.
- Destructive actions without approval/task flow.
