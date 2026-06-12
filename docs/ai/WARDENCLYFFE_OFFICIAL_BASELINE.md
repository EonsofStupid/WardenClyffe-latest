---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: official-baseline
  persona: clyffy-operator
  kind: doc
  owner: docs/ai/WARDENCLYFFE_OFFICIAL_BASELINE.md
  module: module-01-warden
  status: pre-release-baseline
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/ai/parking-lot/decisions/apps.md
    - docs/ai/parking-lot/decisions/modules-warden-infrastructure.md
    - docs/ai/parking-lot/decisions/apps-console-src-lib-design.md
  sync:
    qdrant: true
    surreal: true
---

# WardenClyffe Official Baseline (pre-release)

The canonical architecture for the single WardenClyffe repo. Locked decisions
live in `docs/ai/parking-lot/decisions/`; this doc is the readable synthesis.
Coldlight-specific token values + component inventory are filled in **on ingest**
(pending devpulse/Surreal access) — marked `‹on-ingest›` below.

## 1. Architecture spine

- **React is a dumb presentation layer. Go drives.** All logic, state, and
  authority live in Go services; React renders what Go provides and sends intent
  back. No business logic in React.
- **UI domains mirror Go domains 1:1 and stay parallel.** A domain that exists in
  Go (`warden`, `clyffe`, `clyffy`) exists in the UI under the same name. Adding a
  Go domain means adding the parallel UI domain; they never drift.
- **Optimize for true scale + correctness over preserving what exists.** Existing
  `apps/console` is raw material, not a constraint.

## 2. Canonical frontend structure

One canonical frontend app (anchored under `apps/`, not a top-level
`wardenclyffe/` — that name is the Go repo).

```
apps/<frontend>/src/
  lib/design/                 coldlight component library + theme (THE design system)
    tokens/tokens.json        DTCG source of truth (feeds CSS + the Figma-clone)
    styles/{tokens,base,semantic}.css   generated; ordered via @layer
    components/ui/            primitives rendered ONLY from tokens
    index.ts
  domains/
    warden/   { services/<kind>/<name>.svc.ts + types.ts, components/, views/ }
    clyffe/   { … same shape … }
    clyffy/   { … same shape … }
  services/                   cross-cutting app services (api client, query, auth)
  utils/
  app/                        shell, providers, router
```

- `src/services/` = app-wide; `src/domains/<d>/services/<kind>/<name>.svc.ts` =
  domain calls (thin clients onto Go endpoints). Each service ships a `types.ts`.
- Naming is consistent across Go and UI domains (a `warden` service in Go has a
  parallel `warden` svc in UI).

## 3. Token pipeline (the Figma-clone bridge)

Single well-authored theme, token-driven, mix-and-match:

```
tokens.json (DTCG)
  -> Style Dictionary build
       -> styles/tokens.css   (CSS custom properties: OKLCH color, rem space, clamp() type)
       -> tokens.web.json     (consumed by the Figma-clone so it renders ONLY from tokens)
  -> semantic.css [data-tone] variant engine (one component, every tone)
  -> @layer order: tokens -> base -> components -> utilities
```

- **Color = OKLCH**, themed by rotating hue / shifting L (e.g. coldlight = ‹on-ingest›).
- **Space = rem** scale. **Type = fluid `clamp(min, pref+vw, max)`**.
- **Layout** = Grid for page/section (2-D), Flex for in-component alignment (1-D).
- **Motion** = `@keyframes`/transitions, always gated by
  `@media (prefers-reduced-motion: reduce)`.
- Components carry **no hardcoded values** — only token references, so they
  recombine into any theme.

## 4. Data-plane / VM topology

| Plane | Where | Stores | Purpose |
|---|---|---|---|
| devpulse | local Rust (operator machine) | SurrealDB + Qdrant + embedder | local dev intelligence; runs local Surreal on `devpulse up` |
| Surreal Cloud | cloud | SurrealDB | source of truth **today**; being migrated off |
| Warden VM | dedicated | Postgres + SurrealDB | Postgres = operator/Warden truth; SurrealDB = established copy of cloud |
| Clyffy VM | dedicated (new) | Qdrant + SurrealDB | Clyffy intelligence layer (vectors + graph) |
| devstation | this VM (10.0.0.116) | — | dev/IDE only |

Identity/lang: **Clyffy = Go**, **devpulse = Rust**. SurrealDB structure of
record: `platform_devpulse` (ns/db — confirm on connect).

## 5. Migration plan (Surreal Cloud → self-hosted)

1. Connect via Infisical machine identity → resolve `SURREAL_*` + cloud endpoint.
2. Export cloud SurrealDB (incl. coldlight + `platform_devpulse`).
3. Establish SurrealDB on the **Warden VM**; import the cloud export.
4. Stand up the **Clyffy VM** with Qdrant + SurrealDB.
5. Cut intelligence reads/writes to the self-hosted planes; retire cloud.
6. Do not mark complete until self-hosted Surreal serves the data.

## 6. Open items (blocked on access)

- Ingest **coldlight** from devpulse/Surreal Cloud → populate `lib/design`
  (`tokens.json`, component inventory). Then **full convert** Warden HTML
  (`web/styles.css`, `web/wardenclyffedisk.html`) into coldlight-token components.
- Confirm whether `platform_devpulse` is the SurrealDB namespace or database.
- Confirm the canonical frontend app path/name.
