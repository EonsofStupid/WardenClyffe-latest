---
wardenclyffe_touchpoint:
  version: 1
  kind: clyffy-dynamic-content
  namespace: wardenclyffe.clyffy.dynamic-content
  owner: modules/clyffe/bounded-contexts/dynamic-content
  module: module-02-clyffe
  reads:
    - docs/CLYFFY_DYNAMIC_UI_SPEC.md
    - docs/ai/TOUCHPOINT_SYNC_PATTERN.md
---

# Dynamic Content

Dynamic content controls how Clyffy and Clyffe UI surfaces populate cards,
navigation, empty states, source status, knowledge links, and contextual
assistant prompts.

The UI should ask APIs for content slots and render them. It should not hardcode
operational truth in page components.

## First Slot Families

- `clyffy.home.current-focus`
- `clyffy.home.workspaces`
- `clyffy.home.infrastructure`
- `clyffy.home.knowledge`
- `clyffy.home.assistant`
- `clyffy.workspace.editor-actions`
- `clyffy.workspace.resource-tier`
- `clyffy.workspace.preview-routes`
- `clyffy.node-graph.fleet`
- `clyffy.node-graph.mesh`

Source status should include Warden inventory, Postgres, Qdrant, SurrealDB,
and Markdown touchpoint sync freshness.
