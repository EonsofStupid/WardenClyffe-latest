---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: ui-component-system-spec
  persona: clyffy-operator
  kind: doc
  owner: docs/specs/UI_COMPONENT_SYSTEM_SPEC.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md
  sync:
    qdrant: true
    surreal: true
---

# UI Component System Spec (coldlight)

Goal: one generic, optimized component series (OKLCH, REM, fluid clamp, fully
typed) with **strict schemas that force adoption** — invalid design values fail
to compile.

## Source of truth: coldlight from devpulse (no approximation)

Current `apps/console/src/lib/design` is a **stand-in** built from
OKLCH/REM/clamp tokens — NOT the real coldlight. **Do not extend it as if it
were.** The real coldlight ships in **devpulse**.

**Ingest unblock (operator):** drop the latest devpulse on the devstation, e.g.:
```
/workspace/devpulse/        (or tell me the path)
  tokens/ (DTCG json)   components/   styles/
```
Then I will: read its real tokens + components, identify its **foundation**
(RAC vs shadcn/Radix vs headless) from the actual code, and rebuild
`lib/design` to match it 1:1 — not a guess.

## Strict schemas that force adoption

1. **Token contract → generated types.** `tokens.json` (DTCG) is the source;
   a build step (Style Dictionary) emits `tokens.css` (vars) AND `tokens.ts`
   (typed unions): `Tone`, `Space` (s1..s7), `Radius`, `FontSize`,
   `SurfaceVariant`, `HueToken`.
2. **Props typed to the unions, never raw.** A component cannot accept
   `color="#abc"` or `padding="13px"` — only `tone: Tone`, `gap: Space`. Invalid
   values are a **compile error**. No `any`, no loose `string` for design props.
3. **Lint guard.** ESLint rule bans raw hex / `px` / arbitrary color in
   `components/**`; design values must come from tokens. CI fails otherwise.
4. **Data/forms = Zod.** Runtime schemas (login, account, provision) validate at
   the boundary and infer the TS types — one schema forces both.

## Generic component series (inventory to author from coldlight)

- Primitives: Stack, Cluster, Grid (exist as stand-ins → replace with coldlight).
- Surface system: Surface + Card variants (flat/raised/embossed/glass/sunken).
- Inputs: Button, TextField, Select, Switch, Checkbox, Radio, Textarea, Field.
- Feedback: Badge, Tooltip, Toast, Alert, Spinner, Skeleton.
- Overlay: Dialog, Menu, Popover, Drawer.
- Data: DataTable, Tabs, Pagination, KeyValue.
- App: AppShell, Sidebar, NavItem, PageHeader, RevealableEmail.

Each: base under `lib/design`, variant colocated with its boundary (per
`WARDENCLYFFE_STRUCTURE_STANDARD.md`).

## shadcn vs coldlight (decide on ingest)

- If devpulse coldlight is **shadcn/Radix-based** → script a true conversion:
  generate shadcn components, then **re-skin every token to coldlight** + apply
  the strict token contract above.
- If coldlight is **RAC-based** (what the stand-in uses) → keep RAC, drop in
  coldlight tokens/components.
- Decision is made by *reading devpulse*, not assumed. No conversion authored
  until the foundation is known.

## Sequence

1. Ingest devpulse → rebuild `lib/design` from real coldlight.
2. Stand up the token contract + generated types + lint guard (force adoption).
3. Author the generic series from coldlight; migrate views onto it.
4. Then domain variants. Production-only baseline; PR per component group.
