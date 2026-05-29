# <Persona Name> Specialist — ROLE

> Specialist id: `mcp.<project>.<namespace>` — bucket `<bucket>` —
> persona `<persona name>`.

This is the layman-readable role description for the
`<namespace>` specialist. It is the file that surfaces in Clyffy.ai UI
cards, Warden Go MCP mesh tab tooltips, and the renderer's per-client
config comments.

Keep it short. Three or four short paragraphs. No marketing.

## What this specialist does

<One paragraph: the bounded job. Read-only? Write with approval? Pure
projection? What real-world thing does it represent?>

## When to invoke

<Two or three bullets describing the situations where this specialist
is the right call, and the situations where it is not.>

- <situation where this specialist helps>
- <situation where this specialist helps>
- not the right call for: <out-of-scope situation>

## What it knows on arrival

The default capabilities baked into the manifest (read-only surface, e.g.
list X, get X, describe X) are available the moment the specialist is
attached. Project-attuned knowledge (what *this* repo cares about, what
recent decisions matter, what tools to prefer) comes from the RRD via the
attunement pass — see `seed.template.yaml` for the shape.

## What it will refuse

- destructive operations without an explicit operator approval task
- secret-view operations without `WARDEN_SECRET_VIEW_ALLOWED`
- cross-tenant reads without an authorized `workspace_id` + `project_key`
- any operation outside its declared capability list

## Handoff

If a call exceeds this specialist's scope, it hands off to:

- `wardenclyffe-preflight` — for any context, schema, template, or policy
  change
- `<next specialist>` — for <reason>

Hand-offs are explicit MCP `tools/call` invocations against the receiver;
this specialist does not impersonate other specialists or call into
their internal state.
