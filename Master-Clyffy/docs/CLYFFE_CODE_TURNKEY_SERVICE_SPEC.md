---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: clyffe-code
  persona: clyffy-operator
  kind: clyffe-code-turnkey-service-spec
  owner: docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md
  module: module-02-clyffe
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - docs/CLYFFY_DYNAMIC_UI_SPEC.md
    - docs/CLYFFY_DYNAMIC_UI_POAM.md
    - docs/WARDENCLYFFE_BACKEND_OPTIONS_2026_05.md
    - docs/ai/TOUCHPOINT_SYNC_PATTERN.md
  sync:
    qdrant: true
    surreal: true
---

# Clyffe Code Turnkey Service Spec

Clyffe Code is the future customer-safe hosted coding workspace product. The
first proof is the private operator VM `warden-devstation-01`; the customer
product must be built from a clean template, not from the operator VM.

## Experience Contract

The user should feel like they are working locally:

- the visible app is VS Code, Cursor, or a WardenClyffe-native client;
- files, terminals, builds, agents, and language servers run on the workspace;
- extensions install into the remote workspace where possible;
- the user does not manage SSH keys, Proxmox, reverse proxies, ports, packages,
  DNS records, or secrets;
- Warden owns lifecycle, quota, task state, audit, backups, and route intent;
- Clyffe shows only customer-safe workspace controls.

## Access Model

| Phase | Access path | Notes |
|---|---|---|
| Internal proof | VS Code/Cursor Remote-SSH through `devstation.clyffy.ai` SSH alias | already working for `warden-devstation-01` |
| Private browser fallback | SSH tunnel to VM-local `code-server` | private only; no public bind |
| Managed customer MVP | Clyffe Connect or WardenNet private access | hides SSH and opens the local editor |
| Later hosted browser option | OIDC-gated browser IDE behind Warden edge | only after tenant isolation and abuse controls |

Public Cloudflare records point to the Warden-controlled public jump or edge
only. Private workspace addresses stay in WardenNet, OPNsense split DNS, or
PowerDNS.

## Extension Fidelity

The full extension support path is official VS Code or Cursor using
Remote-SSH. In that model the VS Code Server runs on the workspace and most
language/build/debug extensions run remotely while the local desktop keeps the
normal app shell.

The browser fallback is not equivalent. `code-server` is valuable for emergency
access and simple workflows, but it uses Open VSX by default and cannot be
treated as full Microsoft Marketplace parity. Keep browser IDE as fallback
until WardenClyffe has a deliberate marketplace policy.

## Tier Model

| Tier | vCPU | Memory | Disk | Fit |
|---|---:|---:|---:|---|
| Starter | 2-4 | 8 GiB | 80-120 GiB | small websites, docs, light Node/Python |
| Builder | 8 | 16 GiB | 160-250 GiB | normal full-stack work and agent use |
| Premium Pilot | 16 | 32 GiB | 320 GiB | internal flagship Clyffy/WardenClyffe work |
| Power | 24-32 | 64 GiB | 500 GiB+ | heavy builds, large monorepos, concurrent agents |
| GPU | workload-specific | 64 GiB+ | 500 GiB+ | model serving, local inference, media workloads |

The current `warden-devstation-01` is a Builder-class proof at 8 vCPU, 16 GiB,
and 160 GiB. The proposed internal flagship target is Premium Pilot, but do not
resize the VM until a host resource check, snapshot, and rollback point are
captured. The Wisconsin host is RAM-constrained, so Premium Pilot may belong on
the Virginia host once it is registered.

## Product Building Blocks

| Capability | First implementation | Later productized version |
|---|---|---|
| Workspace VM | Proxmox VM cloned from clean template | Warden workspace scheduler |
| Editor launch | VS Code/Cursor Remote-SSH | Clyffe Connect one-click open |
| Browser fallback | private `code-server` tunnel | OIDC-gated browser IDE where allowed |
| Agent launch | Warden `/agent-workspaces` copy-command launchers | audited open-intent actions through Warden tasks |
| Dependency setup | repo scripts and devcontainer metadata | Warden preflight and project profiles |
| Secrets | Infisical brokered to runtime files | Warden Secrets facade over Infisical/OpenBao-compatible broker |
| Preview ports | SSH tunnel/manual | Warden-managed preview intent and edge routing |
| Snapshots | Proxmox snapshot request | Warden task/audit workflow |
| Backups | VM/template backup policy | tenant-aware backup and restore |
| Billing/upgrade | documentation only | Clyffe upgrade request and Warden approval |

## Clyffe Workspace Extension

Build a small extension or local helper only after the base Remote-SSH path is
stable. The first extension should avoid owning the editor runtime; it should
make WardenClyffe effortless around the editor.

MVP commands:

- sign in to Clyffe;
- list workspaces;
- open workspace;
- run project preflight;
- install missing dependencies through approved scripts;
- show ports/previews;
- show resource tier and usage;
- request upgrade;
- request snapshot;
- show Warden task status;
- open Clyffy chat for the current project.

The extension must call Clyffe/Warden APIs. It must not call Proxmox directly
and must not read operator secrets.

## Backend Shape

Clyffe Code should use the WardenClyffe platform backend rather than invent a
separate one:

- Postgres owns tenants, users, workspaces, resource tiers, approvals, tasks,
  audit, route intent, billing references, and lifecycle state.
- NATS JetStream is the preferred future event bus for workspace lifecycle
  events and async jobs.
- Dragonfly is cache/session/ephemeral coordination, not product truth.
- Object storage stores artifacts, attachments, snapshots metadata exports,
  logs bundles, and generated reports.
- Qdrant stores retrieval vectors for docs, project memory, and support KB.
- SurrealDB stores AI graph and workspace reasoning projections.
- ClickHouse is a later observability and usage analytics candidate.

## Workspace Touchpoints

Every managed workspace should have a small, maintained touchpoint set. If the
UI calls these "touchpads" later, the committed contract still uses
`clyffy_touchpoint` so Codex, Claude, Cursor, and future MCP agents all see the
same boring schema.

Workspace touchpoints should capture:

- workspace identity and tier;
- repo/project identity;
- approved tools and MCP leaves;
- dependency/preflight summary;
- important docs, runbooks, and decisions;
- Clyffy assistant memory boundaries;
- Warden task/audit links;
- customer-safe Clyffe visibility rules.

Sync behavior:

- Markdown remains the human-authored routing layer.
- Qdrant stores embeddings and retrieval payloads for summaries, docs,
  runbooks, KB, and project memory snippets.
- SurrealDB stores graph projections such as workspace -> repo -> project ->
  MCP nodes -> tasks -> docs -> agents.
- Postgres remains product truth for ownership, billing, resource state,
  approvals, and audit.
- Warden shows sync health, staleness, duplicates, and broken routes.
- Clyffe shows only customer-safe knowledge and workspace status.

## Preflight Direction

Every workspace should have a generated preflight record:

```text
repo detected -> language runtimes -> package managers -> service ports
-> required secrets by reference only -> editor extensions -> build/test commands
-> preview routes -> backup policy -> idle policy -> upgrade hints
```

Preflight output is stored as Warden task data and customer-safe summaries in
Clyffe. Secret values, raw environment files, and operator-only host details
must never appear in customer-visible output.
