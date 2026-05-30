# Wardenclyffe Compose Templates

Curated `docker-compose.yml` templates that work standalone AND inside Warden's
deploy module.

**Status:** v0.1 — schema locked, three canonical references (Authentik,
Infisical, Zitadel). Bulk import of more services is parking-lot work.

## What this is

Each template is a single `docker-compose.yml` that:

- Runs a real, working self-hosted service via `docker compose up`
- Includes an `x-warden:` extension key with metadata Warden parses
- Uses `WARDEN_*` env vars for things Warden injects (FQDN, secrets)
- Exposes user-configurable knobs via the `configurable:` schema field

The `x-warden:` block is silently ignored by Docker Compose — it's metadata
for tooling, not runtime config. Plain `docker compose up` works without
Warden at all.

## What this is NOT

- Not a Coolify replacement (Coolify is a runtime + UI + database)
- Not a Helm replacement (Compose, not Kubernetes)
- Not vendor lock-in (templates are standard compose, work anywhere)

## Layout

```
templates/
├── README.md            ← this file
├── SCHEMA.md            ← contract every template follows (read this)
└── compose/
    ├── authentik.yml
    ├── infisical.yml
    └── zitadel.yml
```

## Standalone use (without Warden)

1. Copy the template
2. Generate values for everything in `x-warden.secrets`
3. Set `x-warden.configurable` overrides if needed
4. Set `WARDEN_FQDN` to the public hostname
5. `docker compose up -d`
6. Hit `x-warden.bootstrap.initial_setup_path` to finish setup

## Warden use (when deploy module ships)

User picks template → Warden parses `x-warden:` → fills the deploy form →
Warden creates LXC + secrets + Caddy route → health-check loop → bootstrap
URL surfaced in UI.

## Authoring rules (short version)

- Filename = `x-warden.name` + `.yml`
- Always include `source` block (where you got it, what you changed)
- Use `WARDEN_*` for vars Warden injects, keep upstream names for everything else
- Test with plain `docker compose up` before claiming it works
- Bump `x-warden.version` major on breaking changes

Full schema in `SCHEMA.md`.

## Why x-warden specifically

`x-` extension fields are Docker's official mechanism for metadata. As of
May 2026, the trending convention across Docker Compose Bridge, Portainer,
OneUptime, and Docker Desktop Extensions is to use `x-` keys for tooling
metadata. We're not inventing a pattern — we're following the convergence.
