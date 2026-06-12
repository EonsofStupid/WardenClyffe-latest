---
wardenclyffe_touchpoint:
  version: 1
  kind: catalog-subset
  namespace: wardenclyffe.mediastack.catalog
  owner: hades
---

# Mediastack Catalog

> Owner: `hades` · `category: media` · private by default

Media compose templates for the Mediastack VM live in `compose/`. They follow
the global catalog schema in
[`../../wardenclyffe-catalog/SCHEMA.md`](../../wardenclyffe-catalog/SCHEMA.md),
with the stricter **media boundary** that schema requires:

- `category: media`
- `estate: mediastack` (required)
- `default_visibility: internal` (required)
- `storage_classes:` (required)
- every `ports[].public` defaults to `false`

These templates are runnable with plain `docker compose up`, and Warden reads
the `x-warden` extension key to manage them — but nothing here is publicly
exposed unless the owner adds an explicit deploy-time override.

## Layout

```
catalog/
├── README.md
└── compose/
    └── <service>.yml   ← one x-warden media template per service
```

Add templates as the stack is built out (e.g. library server, request/indexer,
download client). Keep filenames kebab-case and matching `x-warden.name`.
