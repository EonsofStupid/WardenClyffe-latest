---
wardenclyffe_touchpoint:
  version: 1
  kind: bounded-context
  namespace: wardenclyffe.warden.mesh
  owner: modules/warden/bounded-contexts/mesh/README.md
  module: module-01-warden
---

# Warden Mesh Context

Owns the Warden view of Context Mesh and MCP health.

Responsibilities:

- registry loading and validation.
- mesh graph nodes.
- tool/resource/prompt observability.
- Qdrant touchpoint vector sync status.
- SurrealDB graph projection status.
- stale touchpoint reporting.

Source registry:

- `wardenclyffe/registry/context-mesh.yaml`

