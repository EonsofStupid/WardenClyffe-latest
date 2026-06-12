# cortex-intelligence

Direct, **read-only** connection to the self-hosted intelligence plane:

- `surreal.query` (SELECT/INFO only — mutations are blocked; writes belong to
  the sync worker) and `surreal.list_tables` against SurrealDB LXC `104`
  (the self-hosted target of the Surreal-Cloud migration).
- `qdrant.list_collections`, `qdrant.collection_info`, `qdrant.scroll`
  against Qdrant LXC `106`.
- Resources route to the intelligence contract docs (modernization,
  projection v2, sync pattern).

Credentials are refs only (`requires.secrets` in `plugin.json`) — brokered by
Infisical to `/run/warden-secrets`; the plugin degrades to "credential-gated"
messages when absent, never embeds values.

Connect: pick your tool's snippet in `connect/`. Verify: `surreal.list_tables`
returns the DB info (or a clean credential-gated message).
