---
wardenclyffe_touchpoint:
  version: 1
  kind: mcp-surface
  namespace: wardenclyffe.mediastack.mcp
  owner: hades
  registry_entry: mcp.project.homelab-mediastack.mediastack
  reads:
    - ../AGENTS.md
---

# Mediastack Sub-MCP

> Owner: `hades` · estate `mediastack` · project `homelab-mediastack`

The **mediastack sub-MCP** is the governed agent surface over the private media
VM. It is a leaf in the WardenClyffe federated MCP mesh, scoped to the
`mediastack` estate and the `homelab-mediastack` project.

The authoritative server definition lives in the mesh registry:
[`../../wardenclyffe/registry/context-mesh.yaml`](../../wardenclyffe/registry/context-mesh.yaml)
→ server `mcp.project.homelab-mediastack.mediastack`.

## Identity

| Field | Value |
|---|---|
| `id` | `mcp.project.homelab-mediastack.mediastack` |
| `slug` | `hades-mediastack-mcp` |
| `class` | `leaf` |
| `owner` | `hades` |
| `estate` | `mediastack` |
| `project` | `homelab-mediastack` |
| `tools_namespace` | `tools.project.homelab-mediastack.mediastack` |
| `policy` | `policy.estate.mediastack` |
| `endpoint_env` | `MEDIASTACK_MCP_URL` |

## Scope

A read-leaning operational surface over the private media stack, plus
owner-governed membership management. It is **internal-only** and inherits the
boundary's default-deny posture — it must never be exposed publicly.

## Tools (target surface)

Library / streaming (read):

- `mediastack.library_status` — health/size of the media libraries
- `mediastack.list_media` — list items in a library
- `mediastack.stream_sessions` — current playback/transcode sessions
- `mediastack.storage_status` — storage pools / capacity

Membership (owner-governed, write):

- `mediastack.list_members` — current premium/invited members
- `mediastack.invite_member` — invite a premium member or friend (per access policy)
- `mediastack.revoke_member` — revoke access

Requests:

- `mediastack.request_status` — status of member media requests

All membership tools operate strictly within
[`../docs/ACCESS_POLICY.md`](../docs/ACCESS_POLICY.md) (invite-only, default
deny, owner-controlled).

## Status

`scaffold` — registry entry is a forward declaration. Transport, server card,
auth, and tool implementations land with the build-out. See
[`server-card.md`](server-card.md).
