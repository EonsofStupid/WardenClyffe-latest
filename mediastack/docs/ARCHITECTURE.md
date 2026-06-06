---
wardenclyffe_touchpoint:
  version: 1
  kind: estate-doc
  namespace: wardenclyffe.mediastack
  owner: hades
---

# Mediastack — Architecture

> Owner: `hades` · invite-only · premium-community

## Shape

Mediastack is a single **dedicated VM on its own isolated network segment**.
It hosts the private media stack (acquisition → library → streaming) and the
`mediastack` sub-MCP that gives agents a governed, read-leaning surface over it.

```
                 Warden (infra execution authority)
                 Proxmox / host fleet / network fabric
                            │  provisions & runs
                            ▼
        ┌───────────────────────────────────────────┐
        │  Mediastack VM  (isolated network segment) │   owner: hades
        │                                            │
        │   media services (category: media)         │
        │     acquisition · library · streaming      │
        │                                            │
        │   mediastack sub-MCP  ──────────────────┐  │
        │     mcp.project.homelab-mediastack       │  │
        │     .mediastack                          │  │
        └──────────────────────────────────────────┼──┘
                            ▲                       │
            invite-only     │                       │ federated into the
     premium Discord +      │                       │ WardenClyffe MCP mesh
       invited friends ─────┘                       ▼
                                       context-mesh.yaml (estate: mediastack)
```

## Boundaries

| Concern | Mediastack | AIaaS (for contrast) |
|---|---|---|
| Estate | `mediastack` | `aiaas` |
| Network | isolated VM segment | shared product fabric |
| Visibility | `internal` (private) | may be `public` |
| Audience | invite-only community | customers |
| Owner | `hades` | `wardenclyffe` |

## How the layers relate

- **Warden** provisions and runs the VM and owns infra execution. It does *not*
  own the estate's membership or media decisions.
- **Hades** owns the estate: members, library, access tiers, invitations.
- **The mesh** federates the `mediastack` sub-MCP in via the context-mesh
  registry, under the `mediastack` estate root, using the standard
  workspace → project → estate → global resolution with deny-precedence.

## Why it's separate

The media stack carries different legal, exposure, and audience characteristics
from the AIaaS product. Keeping it as its own estate on its own network means:

- AIaaS public-exposure defaults can never leak into it.
- Its data plane (`mediastack-private`) never mixes with customer planes.
- Membership stays invite-only and owner-controlled.
