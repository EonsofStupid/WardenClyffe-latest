---
wardenclyffe_touchpoint:
  version: 1
  kind: deployment-catalog-repo-boundary
  namespace: wardenclyffe.catalog.boundary
  owner: docs/WARDENCLYFFE_CATALOG_REPO_BOUNDARY.md
  module: module-01-warden
  reads:
    - wardenclyffe/templates/README.md
    - wardenclyffe/templates/SCHEMA.md
    - wardenclyffe/docs/agent-roles/catalog-template-author.md
    - wardenclyffe/warden/catalog.go
---

# WardenClyffe Deployment Catalog Repo Boundary

Warden needs a dedicated deployment catalog repository instead of letting
deployable service templates sprawl through the Warden application repo.

## Target Repository

Target repo name:

```text
wardenclyffe-catalog
```

Workspace scaffold:

```text
wardenclyffe-catalog/
```

Purpose:

- own deployable templates Warden can offer.
- keep template schema, provenance, validation, and examples together.
- allow Warden to consume a pinned catalog version.
- keep Warden application code separate from deployable app content.

## Boundary

| Concern | Owner |
|---|---|
| Warden application, APIs, Proxmox inventory, approvals, task polling | Warden repo |
| Deployable compose templates and metadata | `wardenclyffe-catalog` |
| Template parser and catalog UI/API | Warden repo |
| Template schema contract | catalog repo, mirrored/read by Warden |
| Live deployment records | Warden Postgres |
| Secrets generated for deployments | Infisical/keyring/runtime, not catalog |
| Customer-safe service catalog projection | Clyffe via Warden API |

## Initial Catalog Layout

```text
wardenclyffe-catalog/
  AGENTS.md
  README.md
  SCHEMA.md
  catalog.toml
  compose/
    authentik.yml
    harrier-embedder.yml
    infisical.yml
    qdrant.yml
    surrealdb.yml
  edge/
    caddy/
      Caddy.Dockerfile
      compose.yaml
      Caddyfile
  examples/
    env/
      authentik.env.example
  tests/
    catalog-parse-fixtures/
```

## Current Source Material

Move or mirror from the nested Go Warden repo:

| Current path | Target path |
|---|---|
| `wardenclyffe/templates/README.md` | `README.md` |
| `wardenclyffe/templates/SCHEMA.md` | `SCHEMA.md` |
| `wardenclyffe/templates/AGENTS.md` | `AGENTS.md` |
| `wardenclyffe/templates/compose/*.yml` | `compose/*.yml` |
| `wardenclyffe/edge/caddy/*` | `edge/caddy/*` |
| `wardenclyffe/docs/agent-roles/catalog-template-author.md` | keep in Warden docs, optionally mirror as `docs/catalog-template-author.md` |

Do not move `wardenclyffe/warden/catalog.go` into the catalog repo. That code
belongs to Warden because it is the parser, cache, API, and UI.

## Consumption Contract

Warden should support:

```text
WARDEN_CATALOG_DIR=<path-to-wardenclyffe-catalog/compose>
```

Then later:

```text
WARDEN_CATALOG_REF=<git tag or commit>
WARDEN_CATALOG_SOURCE=<repo URL>
```

For the pilot, local filesystem catalog consumption is enough. Git pinning can
come after Warden inventory and deployment records are stable.

## Rules

1. Catalog templates are standard Docker Compose files with `x-warden`.
2. Every template must have provenance in `x-warden.source`.
3. No template may contain secret values.
4. Public exposure defaults to off unless the service is intentionally public.
5. Caddy route intent belongs in Warden; Caddy runtime templates can live in the
   catalog.
6. Warden records deployments and generated secrets; the catalog describes what
   can be deployed.
7. Clyffe sees only customer-safe catalog projections through Warden APIs.

## Immediate Migration Steps

1. Promote the `wardenclyffe-catalog/` workspace scaffold to its own repo or
   submodule.
2. Add a parser validation script that Warden can run in CI.
3. Point Warden dev to the catalog via `WARDEN_CATALOG_DIR`.
4. Keep the old nested paths as temporary mirrors until Warden reads the new
   repo cleanly.
