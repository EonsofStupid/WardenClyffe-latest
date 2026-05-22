---
wardenclyffe_touchpoint:
  version: 1
  kind: bounded-context
  namespace: wardenclyffe.warden.fleet
  owner: modules/warden/bounded-contexts/fleet/README.md
  module: module-01-warden
---

# Warden Fleet Context

Owns remote host registration and health.

Responsibilities:

- host descriptors.
- region and estate mapping.
- Proxmox host onboarding.
- Cisco UCS C240 M5 and future host registration.
- inventory refresh scheduling.
- Warden graph source data.

See `docs/HOST_FLEET_AND_ONBOARDING.md`.

