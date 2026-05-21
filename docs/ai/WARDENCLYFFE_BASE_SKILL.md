---
wardenclyffe_touchpoint:
  version: 1
  kind: base-skill
  namespace: wardenclyffe.skill.base
  wraps:
    - AGENTS.md
    - CLAUDE.md
    - .cursor/rules/wardenclyffe-intelligence.mdc
    - .cursor/skills/wardenclyffe-base/SKILL.md
    - .agents/skills/wardenclyffe-base/SKILL.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
---

# WardenClyffe Base Skill

This is the source skill that other AI wrappers should defer to.

## Operating Frame

- Warden is the operator/server-control platform.
- Clyffe is the customer portal, knowledge base, tickets, CRM, and
  customer-safe service panel.
- Proxmox is the current substrate for the internal two-server pilot.
- The nested `wardenclyffe/` repo contains the active Go Warden scaffold and
  current Context Mesh registry.

## How Agents Should Work

1. Read the nearest `AGENTS.md` before editing.
2. Use Warden/Clyffe terminology consistently.
3. Keep customer-facing actions scoped through Warden APIs.
4. Treat MCP mesh definitions as registry-owned, not prose-owned.
5. Add or update Markdown touchpoints when a new domain, subsystem, or
   intelligence route is introduced.
6. Never commit secrets or live credential material.

## Source-Of-Truth Order

Use this order when sources disagree:

1. Live code and configs in the relevant subtree.
2. Nearest `AGENTS.md`.
3. `docs/WARDEN_CLYFFE_ARCHITECTURE.md`.
4. `docs/WARDEN_CLYFFE_PILOT_ROADMAP.md`.
5. `wardenclyffe/REGISTRY.md` and `wardenclyffe/registry/context-mesh.yaml`
   for the Go-side Warden/intelligence mesh.
6. Wrapper files such as `CLAUDE.md` and `.cursor/rules/*.mdc`.

Wrappers are pointers. They are not authorities.

## When To Update This Skill

Update this file when:

- the Warden/Clyffe product boundary changes,
- the MCP mesh source of truth moves,
- a new agent family needs a wrapper,
- the intelligence layer gets a new memory or routing contract,
- a new repo-wide safety rule is needed.

