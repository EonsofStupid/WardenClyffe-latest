---
wardenclyffe_touchpoint:
  version: 1
  kind: catalog-agent-contract
  namespace: wardenclyffe.catalog
  owner: AGENTS.md
---

# WardenClyffe Catalog Agent Contract

This directory is the future dedicated `wardenclyffe-catalog` repository
boundary. It owns deployable templates and reusable edge/runtime scaffolds.

Warden consumes this catalog. Warden does not own the catalog content.

## What Belongs Here

- Docker Compose templates with `x-warden` metadata.
- reusable runtime scaffolds such as the standalone Caddy edge image/config.
- template schema and validation fixtures.
- non-secret examples.

## What Does Not Belong Here

- Warden application code.
- deployment records.
- generated `.env` files.
- certificates, keys, tokens, cookies, or live API responses.
- customer data.

## Authoring Rules

1. Read `SCHEMA.md` before editing templates.
2. Every template needs `x-warden.source` provenance.
3. Public exposure defaults to off unless the template is intentionally public.
4. Secrets are declared, never embedded.
5. Keep files runnable by plain `docker compose` where possible.
6. Validate templates before marking them ready for Warden.

## Warden Consumption

For local development:

```powershell
$env:WARDEN_CATALOG_DIR = "<repo>\\wardenclyffe-catalog\\compose"
```

For production, Warden should consume a pinned catalog version rather than a
mutable working tree.
