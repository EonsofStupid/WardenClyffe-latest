---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.intelligence
  project_key: boundary-guard-skill
  persona: clyffy-operator
  kind: subsystem
  owner: docs/ai/WARDENCLYFFE_BOUNDARY_GUARD_SKILL.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  agents:
    - claude
    - codex
    - cursor
    - gemini
    - antigravity
  reads:
    - AGENTS.md
    - docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md
    - docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md
    - docs/WARDENCLYFFE_MODULE_MAP.md
    - docs/WARDENCLYFFE_NAMING_CONVENTIONS.md
  sync:
    qdrant: true
    surreal: true
---

# WardenClyffe Boundary Guard Skill

This is the **source skill**. Wrappers under `.claude/`, `.agents/`, `.cursor/`,
and `.codex/` are thin pointers. It is the required companion to
`docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md`: the parking-lot skill decides
*what* gets built; boundary-guard decides *where*, and stops duplicate effort
before a new folder/boundary is created.

## The Rule

**During development mode, before anything new is created** (a new top-level
folder, module, package, service, or domain boundary), boundary-guard must run
first so Clyffy reviews the current file tree and confirms the new boundary is
not a duplicate of an existing one.

In Claude Code this is enforced by a `PreToolUse` hook
(`scripts/foundation/boundary-guard-hook.py`) wired to `Bash` (it catches
`mkdir`) and `Write` (new files in an unrecognized top-level boundary). The hook
is **advisory**: it injects a tree diff + duplicate-candidate report into
context rather than hard-blocking, so the agent reviews before proceeding.

## The Watch-Then-Review Loop

1. **Snapshot** the file tree to a manifest:
   ```bash
   python scripts/foundation/boundary-guard-tree.py --snapshot
   ```
   Writes `docs/ai/parking-lot/filetree.manifest.json` (boundaries, depth,
   owners inferred from nearest `AGENTS.md`).
2. **On change**, regenerate and diff:
   ```bash
   python scripts/foundation/boundary-guard-tree.py --diff
   ```
   Reports added/removed boundaries since the last snapshot.
3. **Before creating** a proposed path, check it for duplicates:
   ```bash
   python scripts/foundation/boundary-guard-tree.py --check "<proposed/path>"
   ```
   Returns the nearest existing boundaries by name/purpose similarity and an
   exit code: `0` = looks new and safe, `2` = likely duplicate — review first.
4. **Clyffy reviews** the report. If a match exists, reuse it (extend the
   existing boundary, add the decision touchpoint there). If genuinely new,
   establish the domain deliberately and record it.

## Planning-Time Review (locked 2026-06-12)

Boundary-guard is not only a pre-creation hook — it is part of **planning**:
any plan or spec that changes the file tree runs `--check` on every proposed
path and cites the resulting boundary in the plan item. Plans whose filetree
deltas were not reviewed against existing boundaries are incomplete.

## Turnkey Distribution (locked 2026-06-12)

The guard travels with the product. The foundation scripts
(`boundary-guard-tree.py`, `boundary-guard-hook.py`, `parking-lot-capture.py`,
`validate-touchpoints.py`) are **part of the workspace template**: they are
automatically copied into every provisioned user devstation/service (golden
template + `.pulse` app updates keep them current). This is the internal
control guidance — every workspace, customer or operator, carries the same
filetree review so duplicates are caught everywhere, not just in this repo.

## Domain Identification

A boundary maps to a domain via the same rule the parking-lot capture uses:
nearest `AGENTS.md` → `modules/module-*` → `workspace_id` + `project_key`.
- Existing domain → route the work and the captured decision into it.
- New domain → this skill gates creation; once approved, seed it with an
  `AGENTS.md` and a `clyffy_touchpoint` per `docs/ai/TOUCHPOINT_TEMPLATE.md`.

## Source Of Truth

- Folder/boundary law: `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md`,
  `docs/WARDENCLYFFE_MODULE_MAP.md`, `docs/WARDENCLYFFE_NAMING_CONVENTIONS.md`.
- Live tree state: `docs/ai/parking-lot/filetree.manifest.json` (generated,
  disposable — regenerate, don't hand-edit).

## Safe For Agents / Needs Approval

- Safe: snapshot, diff, and check the tree; reuse an existing boundary.
- Needs operator approval: creating a **new** top-level boundary/module/domain
  after the duplicate check, or overriding a `--check` exit code of `2`.

## Where To Go Next

- `docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md` — what is being locked in.
- `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md` — the canonical folder shape.
