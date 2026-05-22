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
- Module 1 is Warden. Module 2 is Clyffe. Module numbers are planning labels,
  not durable code names.
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
3. `docs/WARDENCLYFFE_MODULE_MAP.md`.
4. `docs/WARDENCLYFFE_NAMING_CONVENTIONS.md`.
5. `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md`.
6. `docs/HOST_FLEET_AND_ONBOARDING.md`.
7. `docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md`.
8. `docs/FOUNDATION_SERVICE_MATRIX.md`.
9. `docs/WARDEN_ESTABLISHMENT_POAM.md`.
10. `docs/WARDEN_OPERATOR_CAPSULE.md`.
11. `docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md`.
12. `docs/MASTER_CLYFFY_ROLLOUT_PLAN.md`.
13. `docs/CLYFFY_DYNAMIC_UI_SPEC.md`.
14. `docs/CLYFFY_DYNAMIC_UI_POAM.md`.
15. `docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md`.
16. `docs/FOUNDATION_APP_RESEARCH_2026_05.md`.
17. `docs/WARDEN_CLYFFE_ARCHITECTURE.md`.
18. `docs/WARDEN_CLYFFE_PILOT_ROADMAP.md`.
19. `wardenclyffe/REGISTRY.md` and `wardenclyffe/registry/context-mesh.yaml`
   for the Go-side Warden/intelligence mesh.
20. Wrapper files such as `CLAUDE.md` and `.cursor/rules/*.mdc`.

Wrappers are pointers. They are not authorities.

## When To Update This Skill

Update this file when:

- the Warden/Clyffe product boundary changes,
- the MCP mesh source of truth moves,
- a new agent family needs a wrapper,
- the intelligence layer gets a new memory or routing contract,
- a new repo-wide safety rule is needed.
