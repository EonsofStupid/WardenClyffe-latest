---
wardenclyffe_touchpoint:
  version: 1
  kind: devstation-service-registry
  namespace: wardenclyffe.warden.devstation.services
  owner: modules/warden/infrastructure/devstation/services
  module: module-01-warden
  reads:
    - docs/WARDEN_DEVSTATION_AND_CLYFFE_CODE.md
    - modules/warden/infrastructure/devstation/README.md
---

# Devstation Services

This folder holds service descriptors for Warden-managed private development
workstations.

The current descriptor is:

- `warden-devstation-code.yaml` for the private code-server browser IDE on
  VM `116`.

These descriptors are planning contracts for the future Warden UI and API.
They should name the host, service runtime, bind posture, access model,
health probe, and lifecycle controls without storing secrets.
