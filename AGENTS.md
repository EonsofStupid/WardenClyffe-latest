---
wardenclyffe_touchpoint:
  version: 1
  kind: root-agent-contract
  namespace: wardenclyffe.root
  base_skill: docs/ai/WARDENCLYFFE_BASE_SKILL.md
  mesh_touchpoints: docs/ai/MCP_MESH_TOUCHPOINTS.md
  current_mesh_registry: wardenclyffe/registry/context-mesh.yaml
  intelligence_contract: docs/ai/INTELLIGENCE_TOUCHPOINTS.md
---

# WardenClyffe Agent Contract

This is the root project context for Codex, Claude, Cursor, Gemini, and
other coding agents working in this repository.

Read this before making changes. Subtree `AGENTS.md` files add local rules
and nearest file wins for that subtree.

## What This Repo Is

WardenClyffe is being moved into a clean Warden/Clyffe product split:

- **Warden** is the operator/server-control platform.
- **Clyffe** is the customer portal, knowledge base, tickets, CRM, and
  customer-safe service panel.
- **Module 1** is Warden. **Module 2** is Clyffe. Module numbers are planning
  labels; product/code names should stay Warden and Clyffe.
- Proxmox is the current infrastructure substrate for the internal
  two-server pilot.

The nested `wardenclyffe/` repo contains the active Go Warden work and
the most complete Context Mesh/MCP/intelligence scaffold. Treat it as an
important source of current operational truth, but do not mutate it unless
the task is explicitly about the Go Warden repo.

## Read Next

Use this order when you need architecture or agent context:

1. `docs/ai/WARDENCLYFFE_BASE_SKILL.md`
2. `docs/ai/MCP_MESH_TOUCHPOINTS.md`
3. `docs/ai/INTELLIGENCE_TOUCHPOINTS.md`
4. `docs/WARDENCLYFFE_MODULE_MAP.md`
5. `docs/WARDENCLYFFE_NAMING_CONVENTIONS.md`
6. `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md`
7. `docs/HOST_FLEET_AND_ONBOARDING.md` for host onboarding and fleet work
8. `docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md` for public IP, domain, edge, and Tailscale-exit work
9. `docs/FOUNDATION_SERVICE_MATRIX.md` for VM/LXC app choices and configuration gates
10. `docs/WARDEN_ESTABLISHMENT_POAM.md` for done/needed POA&M status and next live milestones
11. `docs/WARDEN_OPERATOR_CAPSULE.md` for the Linux-first operator workspace and secret-handling capsule
12. `docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md` for the private VS Code VM and future hosted coding service
13. `docs/MASTER_CLYFFY_ROLLOUT_PLAN.md` for master.clyffy.ai, Postgres update, and Clyffy route work
14. `docs/FOUNDATION_APP_RESEARCH_2026_05.md` for primary-source app-stack research
15. `docs/WARDEN_CLYFFE_ARCHITECTURE.md`
16. `docs/PROXMOX_FREE_CHEATSHEET.md` for Proxmox management work
17. `docs/WARDENCLYFFE_BACKEND_OPTIONS_2026_05.md` for backend evaluation
18. `docs/GO_WARDEN_ABSORPTION_PLAN.md` when folding Go Warden into the root direction
19. `docs/WARDEN_CLYFFE_PILOT_ROADMAP.md`
20. `modules/README.md` for the root product module scaffold
21. `wardenclyffe/AGENTS.md` when working with or borrowing from the Go Warden repo
22. `wardenclyffe/registry/context-mesh.yaml` for current Context Mesh/MCP registry shape

## Agent Wrapper Rule

This file is the cross-agent wrapper. Thin wrappers may exist for specific
tools:

- `CLAUDE.md`
- `.cursor/rules/wardenclyffe-intelligence.mdc`
- `.cursor/skills/wardenclyffe-base/SKILL.md`
- `.agents/skills/wardenclyffe-base/SKILL.md`

Do not let wrappers become new authorities. Update the base files under
`docs/ai/` first, then adjust wrappers only if their pointer text changes.

## Safety Rules

- Never commit secrets, token values, passwords, cookies, private keys, or
  live API responses that expose credentials.
- Use repo-relative paths in committed docs.
- Treat customer actions as tenant-scoped and audit-logged.
- Treat destructive infrastructure actions as approval workflows unless a
  human explicitly asks for execution.
- Preserve Warden/Clyffe terminology. Warden is operator-facing; Clyffe is
  customer-facing.

## MCP And Intelligence Rule

Markdown files with `wardenclyffe_touchpoint` frontmatter are intentional
touchpoints for agents and future MCP mesh tooling. They should describe:

- what the file owns,
- which registry or source of truth it defers to,
- which agents should read it,
- what must not be changed from that file.

Do not hardcode a second tool registry in prose. The current registry source
is `wardenclyffe/registry/context-mesh.yaml` until a root registry is promoted.
