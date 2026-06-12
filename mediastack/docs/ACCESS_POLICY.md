---
wardenclyffe_touchpoint:
  version: 1
  kind: estate-doc
  namespace: wardenclyffe.mediastack
  owner: hades
---

# Mediastack — Access Policy

> Owner: `hades` · **invite-only** · no public access

Access to the Mediastack VM is **invite-only** and owner-controlled. There is no
open signup and no public route.

## Who may have access

| Tier | Source | Granted by | Notes |
|---|---|---|---|
| `premium` | Discord premium tier | Discord role → invite | Verified premium community members |
| `invited` | Personal invitation | Hades | Friends Hades chooses to invite |
| `owner` | — | — | `hades` |

Anyone not in one of these tiers has **no access**. The default for any
unrecognized identity is deny.

## Principles

1. **Default deny.** Absence of an explicit grant means no access.
2. **Owner-controlled invitations.** Only Hades (or an automation acting on
   Hades' behalf) issues or revokes access.
3. **Premium is verified, not self-asserted.** Premium access derives from the
   Discord premium role, not from a self-service form.
4. **Revocable.** Access can be revoked at any time (e.g. on Discord premium
   lapse) and revocation takes effect immediately.
5. **No public exposure.** Membership never implies a public route; all access
   is over the private/isolated path.

## MCP enforcement

The `mediastack` sub-MCP exposes member-management tools
(`mediastack.list_members`, `mediastack.invite_member`, `mediastack.revoke_member`)
that operate strictly within this policy. They are governed by
`policy.estate.mediastack` and inherit the boundary's default-deny posture. See
[`../mcp/README.md`](../mcp/README.md).
