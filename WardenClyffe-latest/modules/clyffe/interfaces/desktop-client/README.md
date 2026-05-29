---
wardenclyffe_touchpoint:
  version: 1
  kind: clyffe-desktop-client
  namespace: wardenclyffe.clyffe.desktop-client
  owner: modules/clyffe/interfaces/desktop-client
  module: module-02-clyffe
  reads:
    - docs/CLYFFY_DYNAMIC_UI_SPEC.md
    - modules/clyffe/bounded-contexts/code-workspaces/README.md
---

# Clyffe Desktop Client

The future desktop client should make remote devstations feel local.

Working product name:

```text
Clyffe Connect
```

First responsibilities:

- sign in;
- list assigned workspaces;
- establish private access;
- open VS Code locally;
- open Cursor locally;
- open browser IDE fallback;
- show workspace health and resource tier;
- request upgrades;
- stop/start workspace through Warden tasks.

The first implementation may be a thin native wrapper or launcher. It should
not embed secrets or bypass Warden/Clyffe APIs.
