---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: clyffe-code-remote-devspace
  persona: clyffy-operator
  kind: remote-devspace-architecture-plan
  owner: docs/CLYFFE_CODE_REMOTE_DEVSPACE_PLAN.md
  module: module-02-clyffe
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/WARDEN_REMOTE_AGENT_STREAMS.md
    - docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - docs/WARDEN_CLOUDFLARE_DNS_FOUNDATION.md
    - ../automaton/docs/20-http-surface.md
    - ../automaton/docs/19-capability-surface.md
  sync:
    qdrant: true
    surreal: true
---

# Clyffe Code — the remote devspace

**Status: plan. Nothing here is built yet except where the inventory says so.**

This is the `Browser/editor embedding | future` row of
[`WARDEN_REMOTE_AGENT_STREAMS.md`](WARDEN_REMOTE_AGENT_STREAMS.md), specified.

## The experience being built

Log in once, from anywhere, and get a panel. From that panel: open a workspace,
talk to a seat in a **web chat**, or drop into a **terminal** on the same box —
and pick up any conversation that is already running, whichever surface started
it. The shape is claude.ai / chatgpt.com / codex cloud: the work lives on the
server, the browser is glass, and closing the laptop does not end the turn.

Three properties make it that rather than a remote shell with a chat bolted on:

1. **One credential.** A session cookie from `shippin-auth`. The browser never
   holds an engine token, an SSH key, or a Zellij token.
2. **Streams outlive their reader.** A turn started in the terminal is visible in
   the web, and the reverse; a reload rejoins mid-stream; a session from
   yesterday replays.
3. **Surfaces are peers.** Web chat and terminal are two readers of one journal,
   not two products that happen to share a box.

## Inventory — what is actually true today

Honest accounting, because a plan built on an overstatement wastes the next
session's time.

### Built and verified

| thing | where | state |
|---|---|---|
| Multi-seat engine, five live seats | `automaton/engine` | claude, codex, grok, cursor green; gemini via Antigravity `agy` through the DevForge bridge |
| Event journal as SSOT | `.automaton/sessions/<id>/events.jsonl` | NDJSON, append-only, one shape for every seat |
| Capability layer | `automaton/docs/19` | a turn's spec is checked before spawn; safety/contract gaps refuse, the rest degrade loudly |
| `automaton serve` | `automaton/engine/serve/` | HTTP + SSE, **loopback only**, bearer token, replay-then-follow |
| Session follow across processes | `serve/tail.mjs` | a session started by a pane is followable by a browser — the cross-surface property already works at the transport layer |
| Web chat, first cut | `src/domains/clyffe/code/views/WorkspaceChatView.tsx` | streams a real seat; **interim auth** — engine URL + token in `localStorage` |
| Session gate | `services/shippin-auth` | Postgres-backed cookie sessions, nginx `auth_request` → 204/401; built for the DevForge web IDE |
| Tailnet | — | `warden-devstation-01`, `clyffy-01/02/03` reachable |
| Zellij web | devstation | `127.0.0.1:8099`, token auth, unproxied |
| DevForge | `projects/devforge` | code-oss 1.112.2 fork with `remote/`; carries a **stale** engine copy at `devforge/automaton` |

### Not built

- **Any remote reach.** `automaton serve` binds loopback. From outside the box,
  none of the above exists. This is the gap that matters most.
- **Login on the devspace.** `shippin-auth` is not in front of anything Clyffe
  Code serves.
- **Resume in the UI.** `engineService.follow()` exists and is never called. A
  reload loses the transcript.
- **A session list.** No way to see or rejoin prior conversations.
- **Terminal in the browser.** Zellij web is not proxied or embedded.
- **The panel.** No internal home with links to workspaces, DevForge, terminal.
- **Per-workspace routing.** One engine target in `localStorage`, not a workspace
  → box → engine mapping from the control plane.
- **Cancel.** No adapter implements it, so nothing can honestly stop a turn.

## Topology

```
        ┌──────────────────────────────────────────────────────────┐
        │ browser (anywhere)                                       │
        │   cookie: shippin_session      ← the only credential     │
        └───────────────┬──────────────────────────────────────────┘
                        │ TLS
        ┌───────────────▼──────────────────────────────────────────┐
        │ Clyffe edge (nginx)                                      │
        │   listen: tailnet now → public later  (config, not code) │
        │   auth_request → shippin-auth /auth/check (204 | 401)    │
        │   holds every downstream credential server-side          │
        └───┬──────────────┬───────────────┬──────────────┬────────┘
            │              │               │              │
   /  (site)│    /api/engine/:ws/*    /term/:ws      /ide/:ws
            │              │               │              │
     TanStack app   automaton serve    Zellij web    DevForge server
                    (workspace box)    (workspace)   (code-oss remote)
                          │
                    .automaton/sessions/*  ← SSOT both surfaces read
```

The workspace boxes stay on the tailnet with no public route, exactly as
[`WARDEN_DEVSTATION_AND_CLYFFE_CODE.md`](WARDEN_DEVSTATION_AND_CLYFFE_CODE.md)
requires. The edge is the only thing that ever listens for the outside world.

## Credential model

**Rule: a browser holds a session cookie and nothing else.**

- `shippin-auth` issues the cookie; nginx `auth_request` validates it on every
  request, including SSE.
- The edge injects the engine bearer token when proxying to
  `/api/engine/:workspaceId/*`. Tokens are per workspace, stored control-plane
  side, rotatable without touching a browser.
- Zellij's token is likewise injected at the edge. It is rotated with
  `zellij web --revoke-all-tokens` on a schedule and on any suspicion.
- The interim `localStorage` engine token in the current chat view is
  **deleted** when the proxy lands. It exists only so the surface could be built
  before the edge; it is not a design.

Why it matters: a token the browser holds is a token in every proxy log, every
`localStorage` XSS payload, and every screen-share. A cookie scoped to the edge
is revocable in one place.

## Sessions and resume

The journal is SSOT and already supports everything below; the work is surfacing
it, not inventing it.

| behaviour | contract |
|---|---|
| rejoin a live turn | `GET /api/engine/:ws/api/sessions/:id/events?stream=1` — replays the journal, emits `replayed`, then follows |
| see what is running | `GET …/api/sessions` — every session in the workspace, `live` flagged |
| cross-surface | a turn started in a Zellij pane is followable in the browser because the follower tails the journal, not the process |
| survive a reload | the panel remembers the last session per workspace and re-follows on mount |
| survive a disconnect | SSE reconnect with the last seen `seq`; the replay is idempotent because every event carries `id` and `seq` |

**Open decision:** whether resume is *per browser* (last session in local state)
or *per account* (the control plane records "your open sessions" across devices).
Account-level is what claude.ai does and what "log in from anywhere" implies.
Recommended: account-level, stored with the workspace record.

## Terminal

A web terminal is a shell. That is not a reason to avoid it — it is the reason
the gate is non-negotiable:

- served only through the edge, behind `auth_request`;
- Zellij binds loopback on the workspace box, never a routable address;
- the multiplexor session is named per workspace so a reconnect attaches rather
  than spawns;
- the same rotation discipline as any other credential.

The chat and the terminal should show **the same session list**. That is the
whole point of the journal being SSOT: `automaton chat` in a pane and the web
chat are two readers, and the terminal is a third view of the same box.

## Exposure ladder

Decision taken: **tailnet now, public later — with the listen address as
configuration, not architecture.** Nothing in the gateway may assume which it is.

Before flipping to public, all of these must be true. This list is the gate:

- [ ] TLS terminated at the edge with a real certificate, HSTS on
- [ ] `shippin_session` cookie: `Secure`, `HttpOnly`, `SameSite=Lax` minimum
- [ ] CSRF protection on every state-changing route (starting a turn is one)
- [ ] rate limiting per account and per IP, on auth and on turn creation
- [ ] audit log: who started which turn, on which workspace, when
- [ ] tenant isolation — a customer never lands on the operator devstation, per
      the product boundary in `WARDEN_DEVSTATION_AND_CLYFFE_CODE.md`
- [ ] abuse controls on spend: `budgetUsd` / `maxTurns` ceilings enforced by
      policy, not by the caller's spec
- [ ] a documented revocation path for every credential the edge holds

Until every box is ticked, the listen address stays on the tailnet.

## Work breakdown

Sized so each phase is independently useful and independently verifiable.

### Phase 1 — reach

The panel works from where the operator actually is.

- nginx edge config in-repo (not only on the host), with `auth_request` wired to
  `shippin-auth`, listen address from config.
- `/api/engine/:workspaceId/*` proxy with server-side token injection.
  SSE-correct: `proxy_buffering off`, no read timeout on the stream.
- Workspace → box → engine mapping in the control plane; `clyffe-api` grows the
  record (host, tailnet address, engine port, token ref).
- `automaton serve` runs as a **systemd user service** on each workspace box,
  loopback bound, token from the control plane.
- Chat view stops reading `localStorage`; all calls go to the edge, cookie-only.

*Done when:* logging in from a phone on the tailnet reaches the panel, opens a
workspace, and streams a seat — with no token anywhere in the browser.

### Phase 2 — resume

- `GET /api/sessions` rendered as a session list per workspace: seat, status,
  last activity, live flag.
- Clicking one calls `follow()` and replays into the transcript.
- The panel remembers the last session per workspace (account-level per the open
  decision above) and re-follows on mount.
- SSE reconnect with backoff and last-seen `seq`.

*Done when:* a turn started in a Zellij pane on the box is picked up in the
browser mid-stream, and a reload rejoins it.

### Phase 3 — the panel

- An internal home: workspaces, their state, their seats, and links to DevForge,
  the terminal, and the chat for each.
- Seat readiness and the capability matrix surfaced from `/api/caps` — the panel
  offers only what the engine can actually do (`automaton/docs/19`).

*Done when:* one page answers "what do I have, and what can it do", with every
answer read from a live engine rather than a constant.

### Phase 4 — terminal

- Zellij web proxied behind the same gate at `/term/:workspaceId`.
- Named sessions per workspace so reconnect attaches.
- Token rotation runbook.

*Done when:* the terminal and the chat show the same sessions and the same box.

### Phase 5 — honesty debts

- `cancel()` through the adapters → `DELETE /api/sessions/:id` → a Stop button
  that stops. Until then the UI says "Stop reading", which is what it does.
- Replace the stale engine copy in `devforge/automaton`.
- A test runner in `shippin-platform` so pure logic (`engine.transcript.ts`) is
  covered by more than an end-to-end run.
- Un-vendor the Automaton palette once the engine ships as a package.

## Open questions for the operator

1. **Resume scope** — per browser or per account? (Recommended: per account.)
2. **Who runs the edge** — the existing nginx on the devstation, or a dedicated
   Clyffe edge host? This decides whether workspace boxes ever see inbound
   traffic at all.
3. **Engine lifecycle** — does `automaton serve` run always-on per workspace, or
   start on first open? Always-on is simpler and makes "what is running" honest;
   on-demand is cheaper.
4. **DevForge embedding** — iframe inside the panel, or a link out to its own
   host? Iframe wants CSP and frame-ancestors work.
5. **Multi-box fan-out** — one panel across `clyffy-01/02/03` as well as the
   devstation, or devstation first?

## Non-goals for this pass

- Customer tenancy and billing. This is the operator surface first; Clyffe Code
  as a *product* still gates on the tenancy work in the turnkey spec.
- Replacing SSH/Remote-SSH. The launchers in `WARDEN_REMOTE_AGENT_STREAMS.md`
  stay the trusted-operator path; this is additive.
- A second event vocabulary. Everything the web shows is an `AutomatonEvent`
  from the journal, or it is not shown.
