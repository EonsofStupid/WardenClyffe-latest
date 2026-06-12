# WardenClyffe web — `src/`

Root web app. **TanStack Start + React + ColdLight (OKLCH / rem).** No `apps/`
nesting — the app lives at repo-root `src/`.

## Layout

```
src/
  routes/            TanStack Start file routes (thin: URL -> domain view)
    __root.tsx         providers + outlet (imports lib/design css)
    index.tsx          "/"        -> landing
    admin.tsx          "/admin"   -> admin shell + role gate
    warden.tsx         "/warden"  -> operator / infrastructure
    clyffy.tsx         "/clyffy"  -> assistant surface
  router.tsx         router instance (routeTree.gen.ts is plugin-GENERATED)
  domains/
    landing/         public site — sections load dynamically (see below)
    warden/          operator/infra: data, fleet, identity, mesh, overview
    clyffy/          assistant / orchestrator surface
    admin/           authenticated shell + role gate; customer folded as admin/storage
  lib/
    design/          ColdLight: OKLCH/rem tokens + React-Aria-Components widgets
    api.ts           typed client (per-context *.svc.ts is the target shape)
```

Each domain context keeps the Structure Standard shape:
`types.ts · <ctx>.svc.ts · components/ · views/ · index.ts`, named identically
to its Go counterpart.

## Dynamic landing convention

A landing section is a folder `src/domains/landing/sections/<NN>-<slug>/` with:

- `section.meta.ts` — exports `meta: { slug, navLabel, order, enabled }`
- `section.tsx` — exports `Section` (the component)
- `section.css` — optional ColdLight-token styles

`landing.registry.ts` globs `sections/*/section.meta.ts` + `section.tsx`
(`import.meta.glob`, eager), filters `enabled`, sorts by `order`, and
`LandingView` renders them and builds the nav. **Add a section = drop a
`<NN>-<slug>/` folder. No route or import wiring.**

## Running

```
npm install      # pins TanStack Start + Router (versions are VERSION-SENSITIVE)
npm run dev      # plugin generates src/routeTree.gen.ts, serves on :5173 (proxies /api -> warden-api :8081)
```
