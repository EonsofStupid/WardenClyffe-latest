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

Parking-Lot is a **multi-stage deliberation engine**, not just a capture macro.
It turns a planning conversation into an ordered agenda, deliberates each item
to a locked decision under a hard round cap, and then crystallizes every lock
into a granular, build-ready spec. The trigger hook and the capture script are
the plumbing; this file is the protocol that runs between them.

## Focus Feature

The default session focus is **Building WardenClyffe**. A session may override
it (`focus_feature: <name>`), but every captured decision and emitted spec
routes back to a focus so the intelligence layer can group it.

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

## The Parking-Lot Protocol (five stages)

Run these stages in order. Each stage has a deterministic artifact so no agent
ever works blind and the whole deliberation is replayable from disk.

### Stage 0 — Enter & Frame
Announce entry once and state the active focus feature. Restate the topic in one
sentence and the decision horizon (what "done" looks like for this session).

### Stage 1 — Build the Agenda (the bullet list)
Decompose the topic into an **ordered bullet list of decision items**. One bullet
= one decision to crystallize. Each item must carry:

- a short **question** (what must be decided),
- a cited **boundary** (the repo-relative path the decision lands in — plan
  quality rule 3; resolve via boundary-guard if the path is new),
- a starting state `open` and `rounds: 0`.

Persist the agenda deterministically:

```bash
python scripts/foundation/parking-lot-agenda.py init \
  --focus "Building WardenClyffe" --boundary "<repo-relative path>"
python scripts/foundation/parking-lot-agenda.py add \
  --agenda "<project_key>" --question "<one-line decision>" \
  --boundary "<repo-relative path>"
```

This writes/updates `docs/ai/parking-lot/agenda/<project_key>.md`. Present the
full bullet list to the operator before deliberating — the agenda is reviewable
before it is worked, exactly like scaffold-first for code.

### Stage 2 — Deliberate each item (≤12 research-backed rounds → crystallize)
Take items **one at a time, in order**. For the current item run the
**hybrid round loop**:

- **Each round** = (a) research the item with real evidence (repo + the
  intelligence layer + web/deep-research where the question is external), then
  (b) present options, a recommendation with rationale, and rejected
  alternatives. The operator may react, let it run another round, or lock.
- Increment the counter every round:
  ```bash
  python scripts/foundation/parking-lot-agenda.py round --agenda "<project_key>" --item <id>
  ```
- **Convergence** = the operator locks, or a clearly-best option survives and the
  operator confirms. On lock, capture it (existing path, Stage 2 output):
  ```bash
  python scripts/foundation/parking-lot-capture.py \
    --type dependency|feature|capability --name "<name>" \
    --decision "<what was locked + why>" --boundary "<repo-relative path>" \
    --focus "Building WardenClyffe"
  python scripts/foundation/parking-lot-agenda.py lock --agenda "<project_key>" --item <id>
  ```
- **Hard cap = 12 rounds.** At the cap, do not loop forever — **fail open to a
  human call**: either the operator force-decides, or the item is **parked**
  with a reason and a follow-up boundary, and the session advances:
  ```bash
  python scripts/foundation/parking-lot-agenda.py park \
    --agenda "<project_key>" --item <id> --reason "<why deferred + what unblocks it>"
  ```

### Stage 3 — Advance & track
After each lock/park, show progress (`N/M locked, K parked`) and move to the next
`open` item. Repeat Stage 2 until no `open` items remain.

```bash
python scripts/foundation/parking-lot-agenda.py status --agenda "<project_key>"
```

### Stage 4 — Crystallize the Build Spec
When every item is `locked` or `park`ed, emit the **granular build spec** — the
durable deliverable the whole session exists to produce:

```bash
python scripts/foundation/parking-lot-spec.py emit --agenda "<project_key>"
```

This compiles the agenda + every captured decision into
`docs/ai/parking-lot/specs/<project_key>.spec.md` as a scaffold with the
**Build Spec Contract** sections below, each pre-seeded from the locked
decisions. The agent then fills the prose; the script guarantees the skeleton,
the frontmatter, and that no locked decision is dropped.

## Round Discipline

- One item at a time. Never crystallize item N+1 before item N is `locked` or
  `park`ed — the agenda enforces this ordering.
- Every round must add evidence or narrow options; a round that only restates is
  not a round. Cite the source (repo path, ADR, primary source, intelligence
  query).
- 12 is a ceiling, not a target. Most items should lock in 1–3 rounds; the cap
  exists to force a human call on the genuinely-hard ones rather than spin.
- **Deterministic over AI**: the agent deliberates, but state transitions
  (round counts, locks, parks, spec emission) go through the scripts. AI is a
  consumer of the workspace, never the thing that mutates its ledger by hand.

## Artifacts & Capture Path

| Stage | Artifact | Written by | Path |
|---|---|---|---|
| 1 | Agenda (items, states, round counts) | `parking-lot-agenda.py` | `docs/ai/parking-lot/agenda/<key>.md` |
| 2 | Decision touchpoints (one line per lock) | `parking-lot-capture.py` | `docs/ai/parking-lot/decisions/<key>.md` |
| 4 | Granular build spec | `parking-lot-spec.py` | `docs/ai/parking-lot/specs/<key>.spec.md` |

All three carry v2 `clyffy_touchpoint` frontmatter (`sync.surreal: true`) and
flow through the same pipeline — never hand-write a database:

```
artifact touchpoint
  -> python scripts/foundation/validate-touchpoints.py --json   (inventory)
  -> sync worker: chunk summary -> Qdrant point
  -> sync worker: graph projection -> SurrealDB
  -> Warden links task/audit/trace where the decision caused work
```

Keep decision lines terse (the validator warns past 1200 sync words on a single
touchpoint). The **build spec** is the place for depth — it is the transcript's
crystallized output, not a manifest.

## Build Spec Contract (the Stage 4 output sections)

Every emitted `*.spec.md` must carry these sections. The emitter scaffolds them;
the agent fills them from the locked decisions. This is what "extremely granular,
10x-foresight" means in practice:

1. **Overview** — focus feature, scope, and explicit **non-goals** (what we are
   deliberately not building until a later trigger).
2. **Golden patterns** — the naming conventions + structure standard this spec
   obeys (`docs/WARDENCLYFFE_NAMING_CONVENTIONS.md`,
   `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md`,
   `docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md`). Every item lands in their shapes.
3. **Locked decisions** — each crystallized item: the decision, the rationale,
   the rejected alternatives, and the boundary it lands in.
4. **Minimal requirements (10x foresight)** — per item, the smallest thing that
   ships *complete* ("ships complete or not at all"), plus what must be
   configured now because it is **10x costlier to retrofit** (the buildplan
   principle). This is the growth-headroom section.
5. **Established patterns / formulas** — the reusable shapes this spec introduces
   or reuses (e.g. colocated `types.ts`/`*.svc.ts`/views; tx-scoped RLS;
   per-tenant egress allowlist), stated as formulas others can apply.
6. **File-creation guidance** — for each deliverable, *notes vs. code*: which
   files are **touchpoints/Markdown** (routing, decisions, runbooks) and which
   are **code** (which boundary, which language, which colocated companions),
   and what must never be created (duplicate boundaries — defer to boundary-guard).
7. **LLM dynamic-flow within control-layer boundaries** — how Clyffy and the
   coding agents are expected to operate against this spec at runtime: the
   designation rules they obey (connector vs plugin, ai-identity, the brokered
   secret boundary), what they may read, and what must never enter an agent's
   context. This is the runtime contract for the consumers of the spec.
8. **Verify** — per item, the explicit check that proves it done (mirrors the
   buildplan's "every item with a verify step").

## Plan Quality Rules (locked 2026-06-12)

1. **Second pass is allowed and preferred.** If a plan or its code could use
   another pass that yields better-quality or better-structured code, take it.
   A revision pass is part of the process, not a failure of the first pass —
   capture what the second pass changed and why.
2. **Scaffold-first.** When it helps execution, fully scaffold the folders
   (per the structure standard) before writing implementation code, so the
   shape is reviewable before it is filled. The agenda (Stage 1) and the spec
   skeleton (Stage 4) are the planning-level expression of this rule.
3. **Cite boundaries in plans.** Every agenda item / spec item names the
   repo-relative boundary it lands in. An item with no cited boundary is not
   plannable — resolve the boundary (via boundary-guard) first.
4. **Filetree review is part of planning.** Any plan that changes the file
   tree includes a boundary-guard review (`--check` each new path) against
   existing boundaries to remove duplicates before approval.

## Source Of Truth

- Agenda, decisions & rationale, and emitted specs: the touchpoints under
  `docs/ai/parking-lot/{agenda,decisions,specs}/` (then projected, per
  `docs/ai/TOUCHPOINT_SYNC_PATTERN.md`).
- Domain identity: nearest `AGENTS.md` + `docs/WARDENCLYFFE_MODULE_MAP.md` +
  `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md`.
- Product/run truth: Postgres/Warden, never these Markdown files.

## Safe For Agents / Needs Approval

- Safe: enter the mode, build/update an agenda, run a deliberation round,
  capture a clearly-stated lock, park an item with a reason, emit/refresh a
  build spec, run the validator.
- Needs operator approval: establishing a **new** domain/boundary (defer to
  `docs/ai/WARDENCLYFFE_BOUNDARY_GUARD_SKILL.md`), reversing a prior lock,
  promoting a spec into executable work outside the touchpoint path.

## Where To Go Next

- `docs/ai/WARDENCLYFFE_BOUNDARY_GUARD_SKILL.md` — before creating any new
  folder/boundary.
- `docs/ai/TOUCHPOINT_SYNC_PATTERN.md` — the projection contract.
- `docs/ai/INTELLIGENCE_TOUCHPOINTS.md` — memory boundaries.
