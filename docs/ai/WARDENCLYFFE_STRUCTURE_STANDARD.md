---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: structure-standard
  persona: clyffy-operator
  kind: doc
  owner: docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md
    - docs/WARDENCLYFFE_NAMING_CONVENTIONS.md
  sync:
    qdrant: true
    surreal: true
---

# WardenClyffe Structure Standard

One DDD, two stacks, **parallel domains**. Go drives; React is the dumb view.
A context that exists in Go exists in React **under the same name**. The
canonical context registry is `modules/<domain>/bounded-contexts/<context>/`.
Boring, predictable, kebab-case dirs, no synonyms (see naming conventions).

## Domains & canonical contexts

Backend (Go `internal/<context>` + `modules/`):

| Domain | Contexts (canonical) |
|---|---|
| `warden` (operator) | `fleet`, `automation`, `audit`, `proxmox`, `mesh`, `identity`, `data`, `storage` |
| `clyffe` (customer) | `account`, `services`, `support`, `knowledge-base`, `storage` |
| `clyffy` (assistant) | `overview` (orchestrator surface only — never a schema/service authority) |
| `shared` | `contracts`, `primitives`, `observability` |

Frontend (`src/domains/`, locked 2026-06-11) is exactly four domains:

| Domain | Surface |
|---|---|
| `landing` | public site — sections load dynamically (see React layout) |
| `warden` | operator/infrastructure views |
| `clyffy` | assistant surface (chat boundary: overlay + pop-out) |
| `admin` | authenticated shell + role gate; customer plane folded here for now |

Context names are identical across `modules/`, Go `internal/<context>`, and React
`domains/<domain>/<context>`. Adding a context = add it in all three.

## Go layout (strict)

```text
services/<service>/                 kebab; one Go module
  cmd/<service>/main.go             wiring only: config -> stores -> handlers -> server
  internal/
    platform/                       shared infra: config.go, db.go, http.go
    <context>/
      <context>.go                  domain types + Store (data access)
      service.go                    application/use-cases (only when logic warrants)
      handler.go                    interface: Handler + Routes(r) under /api/<domain>/<context>
```

Rules:
- One package per context; package name == context (lower, no underscores).
- File names are fixed: `<context>.go`, `service.go`, `handler.go`. No variants.
- Imports inward: `handler -> service -> <context>(domain+store)`; domain imports
  no transport. `platform` is leaf infra, imported by any context.
- HTTP routes: `/api/<domain>/<context>[/...]`. Clyffe routes expose only
  customer-safe data.

## React layout (strict — revised 2026-06-11: root `src/`, TanStack Start)

The web app lives at repo-root `src/` (NOT `apps/`). Framework = TanStack Start
(file-based routes + SSR); design = ColdLight (OKLCH/rem) + React Aria Components.

```text
src/
  routes/                           TanStack Start file routes — thin URL -> view
    __root.tsx                      providers + outlet (imports lib/design css)
  router.tsx                        router instance (routeTree.gen.ts is generated)
  lib/                              cross-cutting: design/ (coldlight), api client
  domains/<domain>/<context>/
    types.ts                        domain types (mirror Go contract)
    <context>.svc.ts               typed client onto /api/<domain>/<context>
    components/<Name>.tsx (+ .css)  presentational, built from lib/design only
    views/<Name>View.tsx (+ .css)   screen; composes components + svc
    index.ts                        barrel
```

Dynamic landing convention (locked 2026-06-11): a landing section is a folder
`src/domains/landing/sections/<NN>-<slug>/` containing `section.meta.ts`
(exports `meta: { slug, navLabel, order, enabled }`), `section.tsx` (exports
`Section`), optional `section.css`. `landing.registry.ts` discovers them via
`import.meta.glob`, filters `enabled`, sorts by `order`; `LandingView` renders
them and builds nav. Add a section = drop a folder. No manual wiring.

Rules:
- Components are `PascalCase.tsx`; screens are `PascalCaseView.tsx`. Services are
  `<context>.svc.ts`; types are `types.ts`. Always colocated with the context.
- Views/components import **only** from `lib/design` (coldlight) for UI —
  OKLCH/rem/clamp tokens, RAC, CSS Grid (2-D) + Flexbox (1-D), `ui-*` classes.
- No business logic in React; data comes from `<context>.svc.ts` calling Go.

## Self-describing folders

Per `docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md`: every module + bounded context
carries `README.md` (purpose + boundary), `AGENTS.md` when local rules differ,
`context.toml` once code lands. No folder requires guessing what it owns.

## Where truth lives

- Domain/context vocabulary: `modules/<domain>/bounded-contexts/<context>/`.
- Runnable Go: `services/<service>/internal/<context>/`.
- React surface: `apps/<app>/src/domains/<domain>/<context>/`.
- DB schemas: `warden_*`, `clyffe_*`, `ai_bridge` (naming conventions).

## Safe for agents / approval

- Safe: add a context in all three homes with the fixed shapes; add views/
  components from `lib/design`.
- Approval: renaming a canonical context, cross-domain imports, moving truth
  between domains.
