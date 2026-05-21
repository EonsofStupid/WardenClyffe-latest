---
wardenclyffe_touchpoint:
  version: 1
  kind: template
  namespace: wardenclyffe.touchpoint.template
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
---

# Touchpoint Template

Copy this structure when adding a new Markdown touchpoint:

```markdown
---
wardenclyffe_touchpoint:
  version: 1
  kind: subsystem
  namespace: wardenclyffe.<domain>
  owner: <path-or-team>
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  agents:
    - codex
    - claude
    - cursor
  reads:
    - AGENTS.md
    - docs/ai/WARDENCLYFFE_BASE_SKILL.md
---

# <Subsystem> Touchpoint

Purpose:

- What this subsystem owns.
- Which files are source of truth.
- Which actions are safe for agents.
- Which actions need operator approval.
```

