# Warden Web Interface

Operator UI for hosts, Proxmox, infrastructure graph, actions, audit, and mesh
observability.

First UI target:

1. host fleet list.
2. Proxmox inventory dashboard.
3. infrastructure graph.
4. task timeline.
5. mesh drilldown.

## Headless Scale Tabs

Warden UI should expose the Clyffy MCP orchestrator as a first-class operator
surface, not as scattered links:

| Tab | Primary questions |
|---|---|
| Foundation | Are OPNsense, Authentik, Warden, Bifrost, Clyffy Master, Observatory, Postgres, Qdrant, and SurrealDB healthy? |
| Network | What is public, private, split-DNS, VPN/WardenNet, or blocked by OPNsense policy? |
| Identity | What does Authentik own, which apps are gated, and where does Better Auth own app-local sessions? |
| Clyffy Master | Is the dedicated assistant runtime healthy, current, synced, and connected to its model/embedder workspace? |
| MCP Mesh | Which gateway/leaves exist, which Server Cards are reachable, and which tools are allowed? |
| Intelligence | Which touchpoints are indexed in Qdrant, projected in SurrealDB, stale, duplicated, or missing owners? |
| Bifrost | Which provider/minion bridge calls are healthy, rate-limited, or policy-blocked? |
| AI Observatory | What are current traces, provider health, usage, spend, latency, and quality signals? |
| Tasks/Audit | What did Warden, Clyffy, agents, MCP tools, and operators do recently? |

The tab data should come from Warden APIs generated from the registry,
touchpoint inventory, and service descriptors. Do not hardcode these tabs as a
static marketing page.
