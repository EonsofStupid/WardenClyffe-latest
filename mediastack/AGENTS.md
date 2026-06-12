---
wardenclyffe_touchpoint:
  version: 1
  kind: subtree-agent-contract
  namespace: wardenclyffe.mediastack
  owner: hades
  reads:
    - ../AGENTS.md
---

# Mediastack Agent Contract

The repo-root [`AGENTS.md`](../AGENTS.md) applies here. This file adds local
rules for the Mediastack boundary, and nearest-file-wins for this subtree.

## Boundary rules (do not violate)

1. **This is a Hades-owned estate.** `owner: hades`. Do not relabel ownership,
   merge it into the AIaaS estate, or treat it as a customer-facing product
   surface. See [`OWNERSHIP.md`](OWNERSHIP.md).
2. **Private by default.** `default_visibility: internal`. Never add a public
   route, public DNS, or edge exposure to anything in this boundary without an
   explicit, deliberate deploy-time override from the owner. All media template
   ports default to `public: false`.
3. **Isolated network.** This VM lives on its own network segment. Do not wire
   it onto shared AIaaS data planes or the customer estates.
4. **Invite-only membership.** Access is limited to premium Discord members and
   personally invited friends. Do not add open-signup or self-service access
   flows.
5. **Warden still owns infra execution.** Proxmox / host / network changes go
   through Warden. This boundary governs the *estate* (members, library, media
   surface), not the hypervisor.

## When you change things here

- Keep `estate.toml` and the registry entries in
  [`../wardenclyffe/registry/context-mesh.yaml`](../wardenclyffe/registry/context-mesh.yaml)
  in sync. The registry is authoritative for MCP roots; `estate.toml` mirrors it.
- Media compose templates belong in `catalog/compose/` and must follow the
  `category: media` rules in [`../wardenclyffe-catalog/SCHEMA.md`](../wardenclyffe-catalog/SCHEMA.md)
  (`estate: mediastack`, `default_visibility: internal`, all ports private).
- New MCP tools belong to the `mcp.project.homelab-mediastack.mediastack` server;
  document them in `mcp/README.md` and reflect them in the registry server entry.
