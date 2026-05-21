---
wardenclyffe_touchpoint:
  version: 1
  kind: subtree-agent-contract
  namespace: wardenclyffe.web
  reads:
    - ../AGENTS.md
    - ../docs/WARDEN_CLYFFE_ARCHITECTURE.md
---

# Web Agent Contract

The repo-root `AGENTS.md` applies here.

The PHP web tree is migration source material. Keep filenames and copy aligned
with WardenClyffe, Warden, and Clyffe. The future public docs and knowledge
base target is Astro served statically by Caddy or Nginx.

Warden pages are operator-facing. Clyffe pages are customer-facing and must
not expose direct Proxmox control.

