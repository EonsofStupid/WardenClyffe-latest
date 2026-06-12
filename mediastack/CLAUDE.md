# CLAUDE.md — Mediastack

Guidance for Claude Code working inside the `mediastack/` boundary.

## What this is

Mediastack is a **Hades-owned**, invite-only media VM on its own isolated
network. It serves premium Discord community members and personally invited
friends. It is a top-tier boundary within WardenClyffe, deliberately separate
from the AIaaS customer estate.

Read [`OWNERSHIP.md`](OWNERSHIP.md) and [`AGENTS.md`](AGENTS.md) first — they
are authoritative for ownership and boundary rules.

## Hard rules

- `owner: hades` everywhere. Do not change ownership labels.
- Private by default — no public routes/DNS/edge unless the owner explicitly
  overrides at deploy time.
- Invite-only — no open signup.
- Warden executes infrastructure; this boundary governs the estate.

## Layout

- `estate.toml` — machine-readable manifest (keep in sync with the registry).
- `docs/` — architecture, access policy, network boundary.
- `mcp/` — the `mediastack` sub-MCP surface (tools + policy).
- `catalog/compose/` — `category: media` templates, private by default.
- `ops/` — runbook pointers.

## Source of truth

- MCP roots & server entry: `../wardenclyffe/registry/context-mesh.yaml`
- Media template rules: `../wardenclyffe-catalog/SCHEMA.md`
