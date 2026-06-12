---
wardenclyffe_touchpoint:
  version: 1
  kind: ownership-declaration
  namespace: wardenclyffe.mediastack
  owner: hades
  reads:
    - ../AGENTS.md
---

# Mediastack — Ownership Declaration

> **Owner: `hades`** — this boundary is Hades-owned in full.

This document is the single, unambiguous statement of who owns the Mediastack
estate and on whose behalf it runs. Every file under `mediastack/` inherits this
ownership.

## Owner

| Field | Value |
|---|---|
| `owner` | `hades` |
| `steward` | `hades` |
| `access` | `invite-only` |
| `audience` | `premium-community` |
| `member_sources` | `discord-premium`, `invited-friends` |
| `infrastructure_authority` | `warden` |

## What "Hades-owned" means here

- **Hades governs the estate.** Membership, the media library, what gets served,
  who is invited, and the access tiers are Hades' decisions. This is a private
  realm, not a public product.
- **Warden retains infrastructure execution authority.** Proxmox, the host
  fleet, the network fabric, and deploy automation are still executed through
  Warden (per the root `AGENTS.md` Warden/Clyffe split). Warden does the
  plumbing; Hades owns the house.
- **It is its own boundary.** Mediastack is a separate estate, on its own
  isolated network. It is *not* part of the AIaaS (`aiaas`) customer-serving
  estate and must never inherit AIaaS public-exposure defaults.

## Who it serves

The Mediastack VM exists for:

1. **Premium community members** — verified via the Discord premium tier.
2. **Invited friends** — people Hades personally chooses to invite.

No one else. Access is invite-only by default and there are no public routes.

## Relationship to the wider service

Mediastack is one boundary inside the larger WardenClyffe / AIaaS service the
owner is building. It is deliberately walled off:

- It shares the **mesh and naming conventions** (context mesh, MCP federation).
- It does **not** share data planes, public exposure, or customer estates with
  the AIaaS product surface.

See `README.md` for the overview and `docs/ACCESS_POLICY.md` for the member
access model.
