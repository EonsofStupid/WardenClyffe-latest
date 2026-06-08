---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.infra
  project_key: warden-edge
  persona: clyffy-operator
  kind: runbook
  owner: modules/warden/infrastructure/edge/README.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  sync:
    qdrant: true
    surreal: true
---

# Warden Edge — warden.rrflow.ai

Serves the operator console publicly. Boring static SPA + API proxy via Caddy.

## Bring-up (in order)

1. **Build the console** (vite static output):
   ```bash
   cd apps/console && npm ci && npm run build      # -> apps/console/dist
   sudo mkdir -p /srv/warden-console
   sudo rsync -a --delete apps/console/dist/ /srv/warden-console/
   ```
2. **Run warden-api** on the edge host (or proxy to it): `:8081` (identity +
   data). Operator credential comes from Infisical (`WARDEN_OPERATOR_PASS`); the
   dev default `warden-dev` must NOT ship to production.
3. **Install Caddy** + this `Caddyfile`:
   ```bash
   sudo cp modules/warden/infrastructure/edge/Caddyfile /etc/caddy/Caddyfile
   sudo systemctl reload caddy
   ```
4. **DNS (operator action — needs rrflow.ai DNS access):**
   `warden.rrflow.ai  A  <edge public IP>` (proxied or DNS-only per your
   Cloudflare posture). The devstation itself is private (`10.0.0.116`) and must
   not be the public target — front it with the edge host.

## Notes

- TLS: ACME HTTP-01 by default; switch to the Cloudflare DNS plugin if behind
  Cloudflare for `rrflow.ai`.
- `/api/*` → warden-api; everything else → the SPA (`try_files … /index.html`).
- This is operator infrastructure; never expose Proxmox tokens or raw errors.
