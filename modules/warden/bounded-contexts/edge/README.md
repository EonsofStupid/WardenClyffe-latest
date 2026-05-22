---
wardenclyffe_touchpoint:
  version: 1
  kind: bounded-context
  namespace: wardenclyffe.warden.edge
  owner: modules/warden/bounded-contexts/edge/README.md
  module: module-01-warden
---

# Warden Edge Context

Owns public ingress for WardenClyffe.

Responsibilities:

- public IP inventory.
- domain route registry.
- Caddy/Porter or future edge runtime config.
- TLS certificate visibility and renewal state.
- public exposure audit.
- health checks for public routes.
- rollback records for route changes.
- migration from temporary edge hosts to durable Warden-owned edge nodes.

Source material:

- `docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md`
- `wardenclyffe/docs/specs/04-edge-and-routing.md`
- `wardenclyffe/docs/infra-state.md`
- `wardenclyffe/registry/domains.yaml`
- `wardenclyffe/registry/services.yaml`

