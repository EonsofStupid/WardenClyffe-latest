---
wardenclyffe_touchpoint:
  version: 1
  kind: bounded-context
  namespace: wardenclyffe.warden.dns
  owner: modules/warden/bounded-contexts/dns/README.md
  module: module-01-warden
---

# Warden DNS Context

Owns domain and resolver integration for WardenClyffe.

Responsibilities:

- public DNS provider records.
- internal split-horizon DNS intent.
- PowerDNS and Cloudflare synchronization contracts.
- OPNsense Unbound integration notes.
- service-name and customer-domain policies.
- DNS health checks.
- DNS change audit and rollback.

Source material:

- `docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md`
- `wardenclyffe/docs/decisions/0013-dns-authority-split-horizon.md`
- `wardenclyffe/registry/domains.yaml`

