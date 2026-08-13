---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: clyffe-code-cockpit
  persona: clyffy-operator
  kind: surface-build-plan
  owner: docs/CLYFFE_CODE_COCKPIT_PLAN.md
  module: module-02-clyffe
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/CLYFFE_CODE_REMOTE_DEVSPACE_PLAN.md
    - docs/WARDEN_REMOTE_AGENT_STREAMS.md
    - ../automaton/docs/23-capability-surface.md
    - ../automaton/docs/24-http-surface.md
    - ../automaton/docs/19-execution-primitives.md
  sync:
    qdrant: true
    surreal: true
---

# Clyffe Code — the operator cockpit

**Status: plan.** Sequencing decision taken 2026-08-13: **dev mode first, viber
mode derived from it.** This plan covers the surface; reach and login are
[`CLYFFE_CODE_REMOTE_DEVSPACE_PLAN.md`](CLYFFE_CODE_REMOTE_DEVSPACE_PLAN.md).

## The sequencing decision, and why

The teaching surface is the cockpit with gauges hidden and guardrails raised —
**not a second product**. Build the honest instrument first, then derive the calm
one with a disclosure level over the same components.

Two reasons, and the second is the load-bearing one:

1. You cannot simplify what you have not made visible. Building the simple
   surface first means guessing which details to hide, and the guess is wrong in
   both directions at once.
2. **A viber learns the model by watching an operator work in a surface that
   shows it.** Seats, turns, capabilities, cost — hide those from the start and
   the product is a chat box with a mystery behind it, which teaches nothing.

The failure mode this ordering exists to prevent is **two codebases that drift**.
Viber mode must be a prop, never a fork.

## What the cockpit is for

One question, answered honestly, at a glance: *what do I have, what is it doing,
what did it cost, and what can I ask it to do next.* Every answer read from a
live engine — never a constant, never a hand-maintained list.

## Ground truth — measured 2026-08-13

Checked against the running engine, not assumed. This is what changes the plan:

| thing | state | consequence |
|---|---|---|
| `GET /api/sessions` | returns **27 real sessions** with `provider`, `status`, `spec`, `policy`, `createdAt`, `live` | the session list is a **rendering job**, not engine work — biggest win for the least work |
| `follow()` in `fluxcap`/engine client | **exists, never called** | resume-on-reload is wiring, not design |
| replay-then-follow | works; every event carries `id` + `seq` | reconnect is idempotent by construction |
| `usage.updated` | emitted by **claude, codex, grok**; **not** cursor or gemini (IDE bridge) | a cost panel is truthful for 3 of 5 — must render *blank*, never zero |
| `cancel()` | **unimplemented in every adapter** | Stop cannot stop. It currently says "Stop reading", which is what it does |
| `/api/caps` | full matrix with `bearing` per capability | the capability inspector is already served |
| seats today | claude, codex, grok, cursor **green**; gemini **auth needed** | readiness must be visible *before* a turn, not discovered mid-turn |
| engine auth | bearer token in `localStorage` | interim scaffolding; dies with the edge (other plan) |

## Stage 1 — the honest instrument

The cockpit core. Nothing here needs engine work; it is all served already.

- **Session list per workspace** — seat, status, live flag, started, model asked
  for. Reads `GET /api/sessions`.
- **Resume on reload** — remember the last session per workspace, call
  `follow()` on mount, replay into the transcript.
- **Reconnect** with backoff and last-seen `seq`.
- **Seat rail** — readiness from `/api/providers`, and when a seat is not ready,
  *what to run to fix it* (`npm run seat:gemini`) rather than a red dot.
- **Capability inspector** — `/api/caps` for the selected seat: what it will
  accept, what it will refuse, what degrades. Bearing shown, because
  refuse-vs-degrade is the concept a viber most needs to internalise.
- **Run stats** — tokens, cost, duration from `usage.updated`; **"not reported"**
  for bridge seats, never `0`.

*Done when:* a reload never loses work, and nothing on screen is a constant.

## Stage 2 — stop the surface from lying

Small, and disproportionately felt. A control that misrepresents itself is worse
than a missing one, because it teaches the wrong model.

- `cancel()` through the adapters → `DELETE /api/sessions/:id` → a **Stop that
  stops**. Until it lands the label stays "Stop reading".
- Usage for cursor + gemini through the bridge, or an explicit *not reported*.
- Errors rendered as themselves — `exitCode`, `errorCode`, the refusal's blocking
  and degraded lists — not collapsed into "something went wrong".

*Done when:* every control does what its label says, and every failure names
itself.

## Stage 3 — reach and login

Specified in [`CLYFFE_CODE_REMOTE_DEVSPACE_PLAN.md`](CLYFFE_CODE_REMOTE_DEVSPACE_PLAN.md).
Summary: nginx edge gated by `shippin-auth`, engine token injected server-side,
`localStorage` token deleted. Tailnet now, public later behind the hardening gate.

*Done when:* logging in from a phone reaches the cockpit with no token in the
browser.

## Stage 4 — the fan-out primitive (research flows)

This is the differentiator, and it is closer than it looks because the capability
layer already carries the piece that makes it computable.

- **One question → N seats, concurrently**, under a **shared output schema**.
  `structuredOutput` already exists (claude takes the schema inline, codex takes
  a file — the adapter absorbs the difference).
- **A run** groups those sessions, so the artifact is the comparison, not N
  transcripts.
- **A synthesis pass** over the structured answers computes **where they align,
  where they diverge, where they collide, and the recommended merge**. Because
  every seat answered into one schema, this is data — not a vibe read off three
  chat panes.
- **Visuals populate from that data**, per run. No bespoke chart per question.

This maps onto **routine / workflow / pass** in `automaton/docs/19` — arrived at
independently from the other direction. That convergence is the strongest signal
in the merge, and the reason this stage should not invent a third vocabulary.

*Done when:* one question fans to every ready seat and returns a single
comparison artifact whose disagreements are addressable.

## Stage 5 — viber mode

A **disclosure level** over Stage 1–4 components. One flag. Never a fork.

- Gauges hidden by default, revealable — the mystery is opt-in, not enforced.
- Guardrails are **policy**, not UI: `budgetUsd` / `maxTurns` ceilings enforced
  engine-side so a viber cannot spend past them, per the capability layer's
  policy-vs-spec split.
- Teaching affordances: why a seat refused, what a capability means, what a turn
  cost — the cockpit's own truths, surfaced gently.

*Done when:* a non-technical viber runs a real turn, hits a refusal, and
understands *why* without a terminal.

## Contract discipline (cross-cutting)

The floor under this will change: the Rust kernel (`mato-kernel`, `mato-daemon`,
`mato-providers`, `mato-acp`) may take over execution once the gemini/cursor
drivability probe answers. So:

- The **event JSON, TurnSpec, and capability semantics are the contract** —
  language-neutral, in `automaton/docs/schemas/`.
- The cockpit talks to **one HTTP/SSE surface** and never learns which engine is
  behind it.
- **No feature in the surface that the Rust daemon could not inherit** — no new
  event shapes invented browser-side, no engine logic in React.

Get that right and the floor swaps with the cockpit noticing nothing.

## Open questions for the operator

1. **Stats depth** — is per-turn cost enough, or does the cockpit need per-run
   and per-day rollups (which implies persistence beyond the journal)?
2. **Run persistence** — do research runs live in the journal as a session group,
   or get their own record in the control plane?
3. **Which seats fan out by default** — all ready seats, or an operator-chosen
   panel per question? Cost scales linearly with the answer.
4. **Viber blast radius** — do vibers get their own workspaces, or read-mostly
   access to the operator's? This is the tenancy question arriving early.

## Non-goals for this pass

- Billing and customer tenancy — operator surface first.
- A second event vocabulary. Everything shown is an `AutomatonEvent`, or it is
  not shown.
- Replacing the terminal. The pane and the cockpit are peers over one journal.
