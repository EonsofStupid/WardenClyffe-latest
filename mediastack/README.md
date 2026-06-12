---
wardenclyffe_touchpoint:
  version: 1
  kind: estate-root
  namespace: wardenclyffe.mediastack
  owner: hades
  reads:
    - ../AGENTS.md
  registry: ../wardenclyffe/registry/context-mesh.yaml
---

# Mediastack

> **Owner: `hades` · access: invite-only · audience: premium-community**
> A private media VM on its own isolated network, for premium Discord members
> and personally invited friends.

Mediastack is a **top-tier boundary** inside WardenClyffe — a standalone,
Hades-owned estate that lives alongside `wardenclyffenet/`, `wardenclyffedisk/`,
and the `modules/` product split. It is intentionally walled off from the
AIaaS customer-serving estate.

## At a glance

| | |
|---|---|
| **Owner** | `hades` (see [`OWNERSHIP.md`](OWNERSHIP.md)) |
| **Estate** | `mediastack` |
| **Project** | `homelab-mediastack` |
| **Sub-MCP** | `mcp.project.homelab-mediastack.mediastack` |
| **Network** | isolated / dedicated VM segment |
| **Visibility** | `internal` — no public routes by default |
| **Members** | premium Discord tier + invited friends |
| **Infra authority** | Warden (executes infra; Hades governs the estate) |

## Folder map

```
mediastack/
├── README.md            ← you are here — estate overview
├── OWNERSHIP.md         ← the Hades ownership declaration (authoritative)
├── AGENTS.md            ← subtree agent contract (rules for agents in this boundary)
├── CLAUDE.md            ← working guidance for Claude Code
├── estate.toml          ← machine-readable boundary manifest
├── docs/
│   ├── ARCHITECTURE.md  ← the VM, its network, and how the pieces fit
│   ├── ACCESS_POLICY.md ← invite-only member model + access tiers
│   └── NETWORK.md       ← the isolated network boundary
├── mcp/
│   ├── README.md        ← the mediastack sub-MCP (tools, scope, policy)
│   └── server-card.md   ← MCP server card stub (forward declaration)
├── catalog/
│   ├── README.md        ← media compose templates (estate: mediastack)
│   └── compose/         ← x-warden media templates (internal, private by default)
└── ops/
    └── README.md        ← runbook pointers for the VM
```

## Where this is wired into the mesh

The authoritative registry entries live in
[`../wardenclyffe/registry/context-mesh.yaml`](../wardenclyffe/registry/context-mesh.yaml):

- **Estate** `mediastack` → `mcp.estate.mediastack`
- **Project** `homelab-mediastack` → `mcp.project.homelab-mediastack`
- **Sub-MCP server** `mcp.project.homelab-mediastack.mediastack` (`owner: hades`)

Catalog rules for media templates (`estate: mediastack` required, private by
default) are enforced by [`../wardenclyffe-catalog/SCHEMA.md`](../wardenclyffe-catalog/SCHEMA.md).

## Read next

1. [`OWNERSHIP.md`](OWNERSHIP.md) — who owns this and what that means
2. [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — the VM and its shape
3. [`docs/ACCESS_POLICY.md`](docs/ACCESS_POLICY.md) — the member model
4. [`mcp/README.md`](mcp/README.md) — the sub-MCP surface
