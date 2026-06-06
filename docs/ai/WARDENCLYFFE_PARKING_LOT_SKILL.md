---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.intelligence
  project_key: parking-lot-skill
  persona: clyffy-operator
  kind: subsystem
  owner: docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md
  module: module-01-warden
  focus_feature: Building WardenClyffe
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  agents:
    - claude
    - codex
    - cursor
    - gemini
    - antigravity
  reads:
    - AGENTS.md
    - docs/ai/WARDENCLYFFE_BASE_SKILL.md
    - docs/ai/INTELLIGENCE_TOUCHPOINTS.md
    - docs/ai/TOUCHPOINT_SYNC_PATTERN.md
    - docs/ai/WARDENCLYFFE_BOUNDARY_GUARD_SKILL.md
  sync:
    qdrant: true
    surreal: true
---

# WardenClyffe Parking Lot Skill

This is the **source skill**. The per-agent wrappers under `.claude/`,
`.agents/`, `.cursor/`, and `.codex/` are thin pointers to this file. Edit
behavior here; never let a wrapper become a second authority.

## Focus Feature

The default session focus is **Building WardenClyffe**. A session may override
it (`focus_feature: <name>`), but every captured decision routes back to a
focus so the intelligence layer can group it.

## When This Skill Activates

Parking-Lot mode is **forced**, not optional. Enter it whenever the work turns
to planning or decision-making — even if the operator never typed
`/parkinglot`. Triggers:

- planning / scoping / roadmap / sprint / milestone / POAM talk,
- "let's decide / lock in / pick / settle on / go with / commit to",
- choosing a **dependency**, **feature**, **capability**, library, service,
  protocol, schema, or boundary,
- comparing options or recording a trade-off.

In Claude Code this is enforced by the `UserPromptSubmit` hook
(`scripts/foundation/parking-lot-hook.py`). In Cursor it is enforced by the
always-apply rule `.cursor/rules/parking-lot.mdc`. Codex uses
`.codex/skills/parking-lot/`. When the host has no hook, the wrapper
`description` is written so the model auto-invokes on the same triggers.

## What Parking-Lot Mode Does

1. **Announce** entry once: state the active focus feature.
2. **Listen for locks.** When the operator (or the agreed plan) *locks in* a
   dependency, feature, or capability, treat it as a durable decision.
3. **Identify the domain by location/boundary.** Map each decision to the
   subtree it concerns (nearest `AGENTS.md` / `modules/module-*` / the path
   under discussion). That boundary determines `workspace_id` + `project_key`.
   - If the boundary matches an **existing** domain, route there.
   - If it is **new**, mark `domain_status: new` and hand off to the
     **boundary-guard** skill before any folder is created.
4. **Capture** the decision through the sanctioned path (below).
5. **Never** hand-write into a database. Markdown touchpoints are the source;
   the sync worker projects them into Qdrant + SurrealDB.

## Capture Path (Touchpoint → Sync Worker → SurrealDB)

Capture is a one-liner that appends/updates a decision touchpoint:

```bash
python scripts/foundation/parking-lot-capture.py \
  --type dependency|feature|capability \
  --name "<short name>" \
  --decision "<what was locked in and why>" \
  --boundary "<repo-relative path the decision concerns>" \
  --focus "Building WardenClyffe"
```

This writes/updates `docs/ai/parking-lot/decisions/<project_key>.md` with v2
`clyffy_touchpoint` frontmatter (`sync.surreal: true`). It infers
`workspace_id`/`project_key` from `--boundary`, dedupes by decision key, and
stamps the lock. The decision then flows through the existing pipeline:

```
decision touchpoint
  -> python scripts/foundation/validate-touchpoints.py --json   (inventory)
  -> sync worker: chunk summary -> Qdrant point
  -> sync worker: graph projection -> SurrealDB
  -> Warden links task/audit/trace where the decision caused work
```

The captured Markdown is the **bidirectional sync touchpad**: humans and agents
edit it; the worker projects it; staleness/duplication is reported back in
Warden. Keep each decision terse — the touchpoint is a manifest, not a
transcript (validator warns past 1200 sync words).

## Source Of Truth

- Decisions & rationale: the decision touchpoints under
  `docs/ai/parking-lot/decisions/` (then projected, per
  `docs/ai/TOUCHPOINT_SYNC_PATTERN.md`).
- Domain identity: nearest `AGENTS.md` + `docs/WARDENCLYFFE_MODULE_MAP.md` +
  `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md`.
- Product/run truth: Postgres/Warden, never these Markdown files.

## Safe For Agents / Needs Approval

- Safe: enter the mode, capture a clearly-stated lock, update an existing
  decision touchpoint, run the validator.
- Needs operator approval: establishing a **new** domain/boundary (defer to
  `docs/ai/WARDENCLYFFE_BOUNDARY_GUARD_SKILL.md`), reversing a prior lock,
  writing anything outside the touchpoint path.

## Where To Go Next

- `docs/ai/WARDENCLYFFE_BOUNDARY_GUARD_SKILL.md` — before creating any new
  folder/boundary.
- `docs/ai/TOUCHPOINT_SYNC_PATTERN.md` — the projection contract.
- `docs/ai/INTELLIGENCE_TOUCHPOINTS.md` — memory boundaries.
