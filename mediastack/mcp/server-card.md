---
wardenclyffe_touchpoint:
  version: 1
  kind: mcp-server-card-stub
  namespace: wardenclyffe.mediastack.mcp
  owner: hades
---

# Mediastack MCP — Server Card (stub)

Forward declaration for the `mcp.project.homelab-mediastack.mediastack` server
card. Populate on first deploy (mirrors the server-card fields used elsewhere in
the mesh registry).

```yaml
server_card:
  id: mcp.project.homelab-mediastack.mediastack
  slug: hades-mediastack-mcp
  owner: hades
  estate: mediastack
  project: homelab-mediastack
  published: false              # set true on first deploy
  url: null                     # e.g. http://<mediastack-vm>:<port>/.well-known/mcp/server-card.json
  transport:
    stdio: true
    streamable_http: true
  auth:
    methods: []                 # planned: [oauth2.1, rfc9728]
    rfc9728_metadata_url: null
  otel:
    semconv_version: null       # planned: "1.40.0"
    emits: false
  state:
    posture: stateless
```
