---
clyffy_touchpoint:
  version: 2
  workspace_id: clyffy.master
  project_key: clyffy-two-gateway-readiness-audit
  persona: clyffy-operator
  kind: readiness-audit
  owner: docs/CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - wardenclyffe/docs/decisions/0030-mcp-2026-baseline.md
    - wardenclyffe/docs/decisions/0031-workspace-identity.md
    - wardenclyffe/docs/decisions/0032-mcp-federation-three-layer.md
    - wardenclyffe/docs/decisions/0033-touchpoint-protocol.md
    - wardenclyffe/docs/specs/09-context-mesh-and-naming.md
    - wardenclyffe/docs/specs/14-mcp-federation-and-workspace.md
    - wardenclyffe/docs/runbooks/mcp-2026-alignment-checkpoint.md
    - wardenclyffe/.agents/templates/mcp-mesh-server/server-contract.yaml
    - wardenclyffe/.agents/templates/mcp-mesh-server/tools.yaml
    - wardenclyffe/.agents/templates/mcp-mesh-server/policy.yaml
    - wardenclyffe/.agents/templates/mcp-mesh-server/observability.yaml
    - wardenclyffe/.agents/templates/mcp-mesh-server/resources.yaml
    - wardenclyffe/.agents/templates/mcp-mesh-server/prompts.yaml
    - wardenclyffe/.agents/templates/mcp-mesh-server/readiness.yaml
    - wardenclyffe/.agents/templates/mcp-mesh-server/evals.yaml
    - wardenclyffe/.agents/templates/mcp-mesh-server/adapter-target.yaml
    - wardenclyffe/specialists/clyffy-authentik-specialist/auth_k/server.py
    - wardenclyffe/agent/clyffy-dean/clyffy_dean/envelope.py
    - scripts/foundation/validate-touchpoints.py
    - docs/CLYFFY_MCP_ORCHESTRATOR.md
    - docs/MCP_CLIENT_NORMALIZATION_SPEC.md
    - docs/ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md
    - docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md
    - templates/clyffy-specialists/README.md
  sync:
    qdrant: true
    surreal: true
---

# Clyffy Two-Gateway Readiness Audit

This document is the senior-dev readiness checkpoint between "specs written"
and "two L1 gateways instantiated and connected." It captures: (a) what was
read; (b) what is now known precisely about the contracts the gateways must
implement; (c) the **corrections** to my prior proposals that the reading
forced; (d) what remains unknown; (e) the open decision points the operator
must resolve before instantiation can proceed; (f) the recommended sequence.

It is read alongside `docs/MCP_CLIENT_NORMALIZATION_SPEC.md`,
`docs/ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md`, and
`docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md` — the last of which this
audit explicitly **revises** in Decision 1 below.

## Layman Overview

Before we instantiate the two front-door gateways (one for Clyffy.master,
one for WardenClyffe.infra) we needed to actually read the contracts that
govern how MCP gateways behave in this estate. Reading those contracts
forced two real corrections to what was on the table:

1. The proposed rename "drop the `workspace.` prefix from MCP ids" was
   wrong — that prefix is the load-bearing encoding of the federation
   layer model per an already-accepted ADR. We keep the prefix.
2. The two templates we'd be instantiating from (`L1 federation gateway`,
   `L0 workspace publish`) **don't exist yet** — they're a Phase 3
   deliverable in the alignment runbook. We have to author them before
   we can fill them in for the two gateways.

So the realistic order is: author the two missing templates → instantiate
the Clyffy.master gateway against the new templates → instantiate the
WardenClyffe.infra gateway as a parallel sibling → declare the two as
federation peers in the registry. This document fully specifies the
schemas, file lists, and decision points so the next turn can execute
without further reading.

There are six open decisions the operator must resolve before the
template authoring phase starts. They are listed in §8.

## 1. Reading Inventory

Sources read end-to-end during this checkpoint (in order):

| Source | Type | What I now hold |
|---|---|---|
| ADR 0030 — MCP May 2026 Baseline | accepted | Protocol capability bar, Server Card / Tasks / OAuth 2.1 + RFC 9728 / OTel semconv 1.40.0 / stateless sHTTP / deprecations |
| ADR 0031 — Workspace Identity | accepted | L0 entity model, slug grammar, SurrealDB-ns partial-equivalence rule, identity fields |
| ADR 0032 — MCP Federation Three-Layer | accepted | L0/L1/L2 contracts, scope resolution, naming patterns, gateway/leaf/workspace contracts |
| ADR 0033 — Touchpoint Protocol v2 | accepted | Required + recommended + optional frontmatter fields, inheritance, reading discipline, validator rules |
| Spec 09 — Context Mesh and Naming | active | Canonical naming grammar, scope tiers, server classes, the 14 composition rules, two-discovery-surface distinction |
| Spec 14 — MCP Federation and Workspace | active | L1 gateway contract §4, L0 workspace publish contract §5, workspace card JSON shape §6, cross-workspace auth flow §7, readiness gates per layer §9 |
| Runbook — MCP 2026 Alignment Checkpoint | RESOLVED | §1–§7: full phase plan, file-by-file gap matrix, the 10 resolved decisions D-1…D-10 |
| Template — `mcp-mesh-server/server-contract.yaml` | extant | L2 leaf identity contract |
| Template — `mcp-mesh-server/tools.yaml` | extant | L2 tool catalog shape with safety + schemas |
| Template — `mcp-mesh-server/policy.yaml` | extant | L2 auth/permissions/tenancy/rate-limit shape |
| Template — `mcp-mesh-server/observability.yaml` | extant | L2 health/events/metrics/tracing/dashboard shape (NOT YET on OTel semconv 1.40) |
| Template — `mcp-mesh-server/resources.yaml` | extant | L2 resource catalog shape |
| Template — `mcp-mesh-server/prompts.yaml` | extant | L2 prompt catalog shape |
| Template — `mcp-mesh-server/readiness.yaml` | extant | L2 activation gates + rollback rules |
| Template — `mcp-mesh-server/evals.yaml` | extant | L2 eval suites and quality bars |
| Template — `mcp-mesh-server/adapter-target.yaml` | extant | L2 per-wrapper-client target shape |
| Code — `auth_k/server.py` | live `formal-mcp` | FastMCP boot sequence, `@mcp.tool` decorator shape, `specialist_kit.bootstrap()` pattern, known drift (bare tool names, SSE support) |
| Code — `clyffy_dean/envelope.py` | live wire contract | The 12-field `OpEnvelope` dataclass — duplicated symmetrically in `homelab-agent`; change-one-change-both |
| Code — `scripts/foundation/validate-touchpoints.py` | live validator | What the validator actually checks today: `version`, `workspace_id`, `project_key`, `kind`, `owner`, body word counts. Does NOT yet check `surreal.plane_a` or `audit.event_prefix` |
| Doc — `CLYFFY_MCP_ORCHESTRATOR.md` | extant | Orchestrator boundaries, foundation service mapping, UI tabs vision |
| Doc — `MCP_CLIENT_NORMALIZATION_SPEC.md` | this-session | The 3-piece proposal (registry schema, renderer, bridge) |
| Doc — `HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md` | this-session | The RRD trajectory and phased path |
| Doc — `CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md` | this-session | The prior provisional rename proposal (labeled "ADR 0035" at the time — withdrawn) + 10 specialists + encoder/embedder picks |
| Templates — `templates/clyffy-specialists/_skeleton/*` | this-session | The product-layer skeleton I authored earlier |

Sources NOT yet read but identified for the next reading pass (Section 6
lists which are blocking for which deliverable):

- ADR 0017 — Namespace Federation + Mesh Sync (cross-workspace L0 plumbing)
- ADR 0025 — Plane A Namespace Topology (data plane catalog)
- ADR 0028 — SurrealDB Cloud Canonical Structure (ns/db catalog detail)
- Spec 08 — Specialist Capability Packs (specialist contract)
- Spec 10 — Preflight MCP Cluster Foundation (readiness overlay)
- Runbook — `warden-mcp-foundation-checkpoint.md` (Rust rmcp gap and target)
- Runbook — `specialist-capability-pack-authoring.md` (specialist authoring procedure)
- Runbook — `qdrant-surreal-projection.md` (intelligence-layer projection mechanic)
- Runbook — `surreal-ai-memory.md` (SurrealDB AI memory schema)
- Spec — `specialist-architecture.md` (specialist runtime + handoff model)
- Spec — `master-clyffy-architecture.md` (Clyffy.master architecture)
- Spec — `clyffy-authentik-specialist.md` (Auth-K spec — the one extant specialist)
- The remaining alignment runbook sections §8–§12 (out of scope; in/out scope, sources, change log)
- `wardenclyffe/.agents/templates/specialist-capability-pack/{policy,tools,evals,trace-schema,ROLE}` (capability-pack files I haven't sampled)
- `wardenclyffe/specialists/specialist_kit/specialist_kit/*.py` (the kit's bootstrap/audit/safety implementation)
- `wardenclyffe/registry/specialists.yaml` (specialists registry)
- The remaining 1488-line alignment runbook (currently read lines 1–829 of the planning content; the §10 change log and §11 research pass 2 are unread but were summarized into §6 phase plan and ADR 0030 respectively)

## 2. Corrections Forced By The Reading

These are explicit reversals or revisions to what I previously authored
this session. Each correction names the source document(s) that forced it.

### 2.1 — Withdraw the prior provisional "drop workspace prefix" proposal

> **Update (2026-05-28)**: The ADR number 0035 has since been allocated to
> a different decision — **ADR 0035 — Server Status Field Split** — per the
> naming-pattern hardening pass. The withdrawn proposal described below
> never became a file; its prior "provisional ADR 0035" label is now stale.
> See `wardenclyffe/docs/decisions/0035-server-status-field-split.md` for
> the actual ADR 0035.

**What I previously proposed** (in [docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md](CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md) Decision 1):

> Drop the `workspace.` scope segment. Replace `mcp.global.X` for
> WardenClyffe-owned servers with `mcp.wardenclyffe.X`. New shape:
> `mcp.<project>.<namespace>`.

**Why it was wrong**, citation-anchored:

- ADR 0032 §5 ("Naming conventions across layers") explicitly defines:
  - L2: `mcp.<scope>.<domain>` — example given: `mcp.workspace.clyffy-master.authentik`
  - L1: `mcp.<scope>.<workspace_slug>-gateway` — example given: `mcp.workspace.clyffy-master-gateway`
- Spec 09 §4 ("Scope Model") makes `workspace` one of the four scope-tier
  values resolved in the order `workspace → project → estate → global`,
  with `mcp.workspace.<slug>.<domain>` listed as the workspace-private leaf
  pattern.
- Spec 14 §2.1 ("Workspace addressing") restates the rule and adds the HA
  variant `mcp.workspace.<workspace_slug>-gateway-<instance>`.

The `workspace.` segment is therefore **load-bearing**. Removing it would
either (a) supersede §5 of an already-accepted 2026-05-22 ADR, plus §4 of
spec 09 and §2.1 of spec 14, or (b) silently break the federation model's
scope-resolution algorithm in `agent/mcp-cluster/`.

**Resolution**: the rename proposal (then labeled "ADR 0035") is
**withdrawn**. The naming stays as in ADR 0032 §5 / spec 09 §4 / spec
14 §2.1. The ADR 0035 number was later reused for the unrelated
Server Status Field Split decision.

The actionable corrections cascading from this withdrawal are listed in
§2.2 (the namespace-decisions doc revision) and §3.1 (the registry pass).

### 2.2 — Revise `CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md`

The doc still has value — the bucket separation (Clyffy.master / WardenClyffe / global), the 10 specialist roster, the default-capability/seed-value pattern, and the encoder/embedder picks are all still load-bearing. What needs to change:

- Decision 1 ("MCP ID Naming Simplification") must be **replaced** with a
  Decision 1 that ratifies the existing ADR 0032 §5 shape instead. The
  rename map table is wrong and must be removed.
- The prior "provisional ADR 0035 outline" subsection must be removed.
  There is no rename ADR proposed. (The ADR 0035 number has since been
  allocated to the unrelated Server Status Field Split decision.)
- The "rename map" table in §3 ("The Ten Specialist Namespaces") shows the
  wrong target IDs. The targets are the existing registry IDs unchanged.
- The registry update lines in the implementation notes
  (`add aliases`, `change id`) must be removed.
- The "managed_key_prefix_convention" reference to a deprecated rename
  must be removed.

These are surgical edits to one doc — listed as deliverable D-A-3 in §9.

### 2.3 — Re-evaluate which WardenClyffe leaves are truly `global` scope vs. `workspace.<wardenclyffe.infra>` scope

The current registry scopes all WardenClyffe infrastructure-control MCPs
(`proxmox`, `warden`, `dns`, `agent-runtime`, `deploy`, `opnsense`) as
`mcp.global.*`. Per spec 09 §4 ("When to use which scope"):

> `global` — leaf serves the whole estate; no per-tenant divergence
> `workspace.<slug>` — leaf serves one workspace and never crosses to peers

WardenClyffe's infra-control leaves are operator-facing and explicitly
**not** intended to cross to customer-workspace peers (the Clyffe Code
product surface must hide them per `docs/CLYFFE_CODE_TURNKEY_SERVICE_SPEC.md`).
By spec 09's own definition that puts them in `workspace.<wardenclyffe.infra>`
scope, not `global`.

**However** — moving them is a registry-rename pass that affects every
doc that references `mcp.global.proxmox` etc. This is bigger than the
withdrawn rename proposal. It deserves a deliberate decision rather than
a drive-by edit.

**Resolution**: Captured as open decision Q-B in §8. Default position
proposed: keep the current `mcp.global.*` IDs for the WardenClyffe-owned
leaves until peer workspaces actually exist (today there are none, so the
`global vs workspace-private` distinction is invisible). Re-scope only
when a peer workspace would otherwise discover them.

### 2.4 — The template directory rename is part of Phase 2

The current MCP-leaf template at
`wardenclyffe/.agents/templates/mcp-mesh-server/` is being renamed to
`wardenclyffe/.agents/templates/mcp/l2-leaf-server/` as part of Phase 2 of
the alignment runbook (§4.2 revised folder structure). All my prior docs
reference the old path. Anything I author or reference in the next phase
must use the new path.

**Resolution**: `docs/MCP_CLIENT_NORMALIZATION_SPEC.md` and
`templates/clyffy-specialists/_skeleton/README.md` reference the old path
in two places; sweep listed as deliverable D-A-4 in §9.

### 2.5 — `wardenclyffe/AGENTS.md` frontmatter is **still v1**

I cited ADR 0033 v2 throughout my new docs, but the actual root agent
context in `wardenclyffe/AGENTS.md` still carries `version: 1` and
`namespace_id:` (the old field name). ADR 0033 §10 describes the v1→v2
migration as Phase 8 work in the alignment runbook. The validator accepts
both during the deprecation window.

This is **not** an error in my prior docs (which used v2 correctly per ADR
0033), but it does mean the `wardenclyffe/AGENTS.md` frontmatter itself is
on the migration target list. Captured for awareness.

### 2.6 — Gateway slug encoding ambiguity

Spec 14 §2.2 names the L1 gateway ID pattern:

```
mcp.workspace.<workspace_slug>-gateway
```

ADR 0031 §2 defines the slug grammar:

```
<realm>.<role>[.<qualifier>]
```

with examples `clyffy.master` (dot-separated) and `effing.personal-site`
(dot-and-hyphen). The slug for the operator's master workspace is
`clyffy.master` (a string with a dot).

The existing registry entry in `wardenclyffe/registry/context-mesh.yaml`
uses `mcp.workspace.clyffy-master-gateway` — with the slug **hyphen-encoded**
(`clyffy-master` not `clyffy.master`).

A literal embed of the dotted slug into the gateway ID would produce
`mcp.workspace.clyffy.master-gateway`, with `clyffy.master` having a dot.
That collides ambiguously with the dotted scope-path notation
(`mcp.<scope>.<...>`).

This is an **actual ambiguity** in the existing canonical contracts.
Three possible resolutions:

| Option | Gateway ID for clyffy.master | Gateway ID for wardenclyffe.infra | Rationale |
|---|---|---|---|
| A — preserve dotted slug | `mcp.workspace.clyffy.master-gateway` | `mcp.workspace.wardenclyffe.infra-gateway` | Literal ADR 0032 §5 reading; relies on `-gateway` suffix to disambiguate |
| B — hyphen-encode the slug | `mcp.workspace.clyffy-master-gateway` | `mcp.workspace.wardenclyffe-infra-gateway` | Matches the existing registry entry; readable; needs a documented encoding rule |
| C — use workspace_uuid in id | `mcp.workspace.01HXXXX-gateway` | `mcp.workspace.01HYYYY-gateway` | UUID-stable; humanly opaque; conflicts with "human handle" intent of slugs |

**Resolution**: Captured as open decision Q-C in §8. Default position
proposed: Option B (hyphen-encode). It matches the existing registry,
preserves human readability, and only needs a one-line documented rule:
"when embedding `workspace_slug` into an MCP id, replace `.` with `-`."

## 3. What I Now Know Precisely

This section is the gateway-instantiation knowledge base, every claim
anchored to a source.

### 3.1 — Federation Layer Model (ADR 0032 §1–3, Spec 14 §1)

```
L0 — Workspace
  Identity: federation_workspace table; one row per Clyffy deployment
  Owns: workspace card; peer contract; capability-publish list; cross-workspace OIDC trust
  Template: .agents/templates/mcp/l0-workspace-publish/  (PHASE 3 SUB-PHASE B; not yet authored)

L1 — Federation Gateway (one or more per workspace)
  Owns: single endpoint per workspace; upstream discovery; scope-aware routing;
        single auth front door; transport translation; trace correlation;
        federation audit; circuit-breaker; rate-limits-per-(tenant, scope, tool);
        approval-required tool elevation at the gateway
  Must not own: tool authority, hidden identity claims, erased upstream attribution, tool definitions
  Template: .agents/templates/mcp/l1-federation-gateway/  (PHASE 3 SUB-PHASE A; not yet authored)

L2 — Leaf MCP Server (one or more per workspace, plus globals)
  Owns: domain tools, resources, prompts
  Server Card at /.well-known/mcp/server-card.json (MUST)
  Registered in registry/context-mesh.yaml
  Emits OTel semconv per ADR 0030 §3
  Accepts identity claims forwarded by L1
  Template: .agents/templates/mcp/l2-leaf-server/  (CURRENT — being renamed from mcp-mesh-server/ in Phase 2)
```

The L1→L2 wire shape (Spec 14 §4.5):

```
client  -- initialize -->  gateway
        <-- capabilities --
        -- tools/list -->  gateway  -- tools/list -->  leaf-A, leaf-B, leaf-C
        <-- merged catalog --      <-- per-leaf catalogs --
        -- tools/call X -->  gateway  -- (route) --> leaf serving X
        <-- result, structuredContent, traceparent --
```

The cross-workspace flow (Spec 14 §7):

```
1. Operator at workspace A authorizes federated access to workspace B
2. Workspace A's Authentik issues a token with aud=[workspace-B-slug] claim
3. Workspace B's gateway validates the token against the shared JWKS
4. Workspace B's gateway propagates the token to the relevant leaf
5. Leaf enforces per-tool policy with workspace-A actor identity in scope
6. Audit at both workspaces with shared request_id
```

### 3.2 — Scope Resolution (Spec 09 §4, ADR 0032 §4)

```
workspace → project → estate → global
```

Deny precedence: a deny at any scope blocks the call regardless of allows
at narrower scopes.

Scope tier examples:

| Scope | MCP server ID | Tool namespace |
|---|---|---|
| Global | `mcp.global.proxmox` | `tools.global.proxmox` |
| Estate | `mcp.estate.aiaas.proxmox` | `tools.estate.aiaas.proxmox` |
| Project | `mcp.project.wardenclyffe-core.proxmox` | `tools.project.wardenclyffe-core.proxmox` |
| Workspace-private | `mcp.workspace.clyffy-master.authentik` | `tools.workspace.clyffy-master.authentik` |

### 3.3 — Naming Grammar (Spec 09 §6)

| Element | Pattern | Example |
|---|---|---|
| Server ID L2 | `mcp.<scope>.<domain>` | `mcp.workspace.clyffy-master.authentik` |
| Server ID L1 | `mcp.<scope>.<workspace_slug>-gateway` (per Q-C resolution) | `mcp.workspace.clyffy-master-gateway` |
| Server ID L1 HA | `mcp.<scope>.<workspace_slug>-gateway-<instance>` | `mcp.workspace.clyffy-master-gateway-edge` |
| Tool ID | `<domain>.<verb>_<object>` | `proxmox.list_nodes`, `authentik.list_users` |
| Resource URI | `warden://<domain>/<resource>/<id>` | `warden://proxmox/node/server1` |
| Slug (binary/dir) | lowercase kebab | `warden-mcp`, `clyffy-dean` |

Approved verbs (Spec 09 §6.3, exhaustive):

```
list, get, search, read, inspect, validate, plan, apply, create, update,
delete, snapshot, restore, migrate, enqueue, replay, put_candidate
```

Rules:
- Read-only must use `list`, `get`, `search`, `read`, `inspect`, `validate`
- Planning uses `plan`
- Mutating uses explicit verbs **and** requires policy gates per
  `policy.yaml.permissions.mutate`
- No cute names in tool IDs
- Stable tool names are not churned without a migration alias

### 3.4 — The 14 Composition Rules (Spec 09 §10, condensed)

1. Prefer multiple focused MCP servers over one broad MCP server
2. One MCP client connection per MCP server when the host supports it
3. Use a gateway only for host limitations, policy aggregation, single auth front door, dashboard convenience
4. Permissions live at the leaf; the gateway propagates, never invents
5. Stable names through registry before exposing tools to models
6. Every tool belongs to one domain namespace
7. Cross-domain workflows are workflows, not mixed-domain tool names
8. Observability shows leaf-server attribution even through a gateway
9. Compatibility services labeled `mcp-shaped` until they pass formal MCP verification
10. Readiness overlays derive from the registry; never redefine ownership/tools/transport
11. Every formal MCP server MUST publish a Server Card at `/.well-known/mcp/server-card.json` per ADR 0030 §1
12. Every HTTP-exposed MCP server MUST implement OAuth 2.1 + RFC 9728 per ADR 0030 §5
13. Every formal MCP server MUST emit OpenTelemetry semconv per ADR 0030 §3
14. Sampling and SSE transport are DEPRECATED — MUST NOT be implemented in new servers

### 3.5 — May 2026 Protocol Baseline (ADR 0030)

| Capability | Posture | Affects gateways? |
|---|---|---|
| MCP spec revision | 2025-11-25 + named SEPs | yes — base wire format |
| Server Cards (SEP-1649/2127) | MUST | yes — gateway IS a server |
| Tasks (SEP-1686) | SHOULD for p95 > 5s | conditional — passes through upstream tasks |
| MCP Apps (SEP-1865) | MAY | optional |
| DPoP (SEP-1932) | SHOULD for non-LAN tokens | yes — gateway is the auth front door |
| WIF (SEP-1933) | SHOULD for k8s/SPIFFE; LXC = OAuth 2.1 bearer | LXC posture for now |
| Stateless Streamable HTTP | MUST default | yes — gateway must be stateless or declare external_store |
| Sampling (`sampling/createMessage`) | DEPRECATED — MUST NOT implement | yes — gateway does not relay sampling |
| SSE transport | DEPRECATED — MUST NOT offer | yes — only stdio + Streamable HTTP |
| SDK Tiers (SEP-1730) | MUST declare | yes |
| JSON Schema 2020-12 | default dialect | yes |
| OTel semconv 1.40.0 | MUST emit | yes + gateway-specific attrs |

### 3.6 — OpenTelemetry Semantic Conventions (ADR 0030 §3, Spec 14 §8)

Every span emitted by a gateway:

- Name: `{mcp.method.name} {target}` where target = tool/prompt name when low-cardinality; else `{mcp.method.name}` alone
- Required attribute: `mcp.method.name`
- Recommended attributes: `mcp.session.id`, `mcp.protocol.version`, `gen_ai.tool.name`, `gen_ai.operation.name`, `network.transport`, `server.address`, `client.address`, `jsonrpc.request.id`
- Gateway-specific attributes: `mcp.gateway.upstream_id`, `mcp.gateway.routing.decision`
- Federation attributes: `workspace.slug`, `workspace.peer_slug` (cross-workspace only)
- Trace context propagation: W3C `traceparent`, `tracestate`, `baggage` via JSON-RPC `params._meta`
- No double instrumentation: if outer GenAI instrumentation already traces `execute_tool`, MCP layer adds MCP attributes to that span rather than creating a new one

Four standard histograms (seconds):

- `mcp.client.operation.duration`
- `mcp.server.operation.duration`
- `mcp.client.session.duration`
- `mcp.server.session.duration`

Bucket boundaries: `[0.01, 0.02, 0.05, 0.1, 0.2, 0.5, 1, 2, 5, 10, 30, 60, 120, 300]`

### 3.7 — Authorization (ADR 0030 §5)

For any HTTP outside localhost:

- Serve a Protected Resource Metadata document per RFC 9728
- Include `authorization_servers` with ≥1 entry
- Return `WWW-Authenticate` on 401 with the resource-server metadata URL (RFC 9728 §5.1)
- Support RFC 7591 Dynamic Client Registration where the AS permits it

For stdio + localhost-only:

- `localhost_stdio.allowed: true` is acceptable per the template's `policy.yaml`

### 3.8 — Error Model (ADR 0030 §6)

Two distinct classes:

- **Protocol errors** — JSON-RPC `error` object (-32700/-32600/-32601/-32602/-32603). Invisible to the LLM. Use for malformed wire-protocol input.
- **Tool execution errors** — `result.isError = true` with content blocks. Visible to the LLM. Use for any business / data / external-API failure the LLM could plausibly recover from.

Rule: for any LLM-recoverable failure, return `isError: true` with actionable
content. Do not throw.

### 3.9 — Security Hardening (ADR 0030 §4)

- Treat every tool `description` as code. Declarative only — never imperative.
- Set `additionalProperties: false` at every nesting level of every input schema.
- Anchor every `pattern` regex (`^...$`).
- Enforce schema at runtime — declared-but-unenforced buys nothing.
- Validate both `Host` and `Origin` headers on Streamable HTTP.
- Bind `127.0.0.1` by default for stdio-adjacent dev; explicit allowlist for any non-loopback bind.
- Pin MCP Inspector to latest (CVE-2025-49596 RCE in old versions).

### 3.10 — Touchpoint Frontmatter (ADR 0033 §2–§4)

REQUIRED fields in **every** `clyffy_touchpoint:` frontmatter:

```yaml
clyffy_touchpoint:
  version: 2
  workspace_id: <workspace_slug>                # per ADR 0031 grammar
  project_key: <narrower scoping key>
  persona: <persona_definition row ref>
  surreal:
    plane_a:
      url: <surreal endpoint>
      ns: <one of: clyffy | wardenclyffe | projects | platform_devpulse | agents | global>
      db: <plane sub-concern>
  audit:
    event_prefix: <prefix string>
    enabled: true
```

RECOMMENDED v2 fields:

```yaml
  workspace_uuid: <UUID>
  capabilities:
    mcp_gateway:
      url: <gateway endpoint>
      protocol_version: 2025-11-25
      auth: oauth2.1+rfc9728
    mcp_servers:                                  # leaves reachable directly
      - url: <leaf endpoint>
        server_id: <mcp.workspace.X.Y>
    federation_peers: [<workspace_slug>, ...]
  observability:
    semconv_version: "1.40.0"
    trace_context_via_meta: true
```

OPTIONAL fields:

```yaml
  surreal.plane_a_star: {...}                     # operational plane writes
  scopes: [...]
  intel_hook.*
  specialist_id: <slug>                           # when the dir IS a specialist source
  designated_lxc: <vmid>                          # when the binary deploys to a specific LXC
```

Validator (current state per `scripts/foundation/validate-touchpoints.py`):
checks `version`, `workspace_id`, `project_key`, `kind`, `owner`, body
word counts. Does **not** yet check `surreal.plane_a` or
`audit.event_prefix` — those are part of the unwritten future validator
work tracked by ADR 0033 §9.

**Practical consequence**: a `docs/` markdown file can carry a lighter
touchpoint frontmatter (the validator-checked subset is enough). An
`AGENTS.md` file in the cascade should carry the full ADR 0033 §2 shape.

### 3.11 — Workspace Identity (ADR 0031 §2)

```
workspace_uuid        UUID         primary key (machine identifier)
workspace_slug        string       human handle, e.g. "clyffy.master"
workspace_name        string       display name, e.g. "Clyffy Master"
workspace_kind        enum         master | personal-site | customer-bundle | workshop
workspace_owner_id    UUID         FK to user identity (Authentik subject)
workspace_owner_email string       human contact; never the only identifier
workspace_tier        enum         free | pro | enterprise | operator
workspace_status      enum         active | draining | archived | pending
workspace_created_at  timestamp
parent_workspace_id   UUID         nullable; for bundle / sub-workspace relationships
```

Slug grammar (ADR 0017, retained):

```
<realm>.<role>[.<qualifier>]
```

Examples: `clyffy.master`, `effing.personal-site`, `clyffy.bundle.acme-pharma`,
`wardenclyffe.infra`.

The partial-equivalence rule (ADR 0031 §3):

- `workspace_slug` = **row-level** scoping value (appears in
  `audit_log.project_key`, `federation_workspace.workspace_slug`,
  `mcp.workspace.<slug>.<domain>` scope tier name, `clyffy_touchpoint.workspace_id`)
- SurrealDB `ns` = **data plane** name (one of `clyffy`, `wardenclyffe`,
  `projects`, `platform_devpulse`, `agents`, `global`)
- The two are different concepts. Name overlap (`clyffy` plane vs
  `clyffy.master` workspace) is historical coincidence, not structural binding.

### 3.12 — Workspace Card JSON Shape (Spec 14 §6)

Per spec 14 §6 — drafted for first peer-workspace integration; will be
locked by a future ADR. Minimal shape:

```json
{
  "$schema": "https://wardenclyffe.example/schemas/workspace-card/v1",
  "workspace_uuid": "01HXXXX...",
  "workspace_slug": "clyffy.master",
  "workspace_name": "Clyffy Master",
  "workspace_kind": "master",
  "workspace_owner_email": "operator@example.invalid",
  "endpoint": "https://master.clyffy.ai",
  "api_version": "v1",
  "capabilities": ["mcp-l1-gateway", "discord-surface", "persona-author"],
  "mcp": {
    "gateway_url": "https://master.clyffy.ai/mcp",
    "supported_protocol_versions": ["2025-11-25"],
    "auth_methods": ["oauth2.1", "dpop"],
    "published_servers": [
      {"server_id": "mcp.workspace.clyffy-master.authentik", "scope_advertised_as": "workspace", "tool_count": 9}
    ],
    "private_servers_count": 0
  },
  "federation": {
    "supports_protocols": ["clyffy.federation/v1", "a2a/v1"],
    "agntcy_oasf_record_url": null
  },
  "trust": {
    "oidc_jwks_url": "https://auth.clyffy.master/jwks.json",
    "accepted_audiences": ["clyffy.master"]
  },
  "_meta": {
    "schema_version": 1,
    "generated_at": "2026-05-22T00:00:00Z"
  }
}
```

Locations (both — same content):

- Served at `https://<workspace-endpoint>/.well-known/wardenclyffe/workspace-card.json`
- Mirrored as `workspace_card` field on the workspace's row in `federation_workspace`

### 3.13 — Server Card JSON Shape (ADR 0030 §1, SEP-1649/2127)

Minimum valid shape acceptable per ADR 0030 §1; field set tracks the WG
charter. Served at `/.well-known/mcp/server-card.json`. Distinct from
`server.json` (the optional MCP Registry publication record per Spec 09
§0.2). At minimum:

```json
{
  "name": "<mcp server id>",
  "protocol_versions": ["2025-11-25"],
  "transports": [
    {"type": "stdio"},
    {"type": "streamable-http", "url": "<endpoint>"}
  ],
  "auth": {
    "methods": ["oauth2.1"],
    "resource_metadata_url": "<RFC 9728 metadata url>"
  },
  "capabilities": {
    "tools": true,
    "resources": true,
    "prompts": false,
    "tasks": false,
    "mcp_apps": false
  },
  "_meta": {
    "workspace_slug": "<owning workspace>",
    "scope": "workspace.<slug> | global | estate.<estate> | project.<key>"
  }
}
```

The minimal-valid shape will tighten as SEP-1649/2127 lock; tracked as
parked.

### 3.14 — L2 Leaf Template (current `mcp-mesh-server/`, becoming `mcp/l2-leaf-server/`)

I have read all 9 files. The schemas are summarized here for direct
reference during gateway authoring (because the L1 gateway template will
reuse most of these shapes with gateway-specific deltas).

`server-contract.yaml` (current schema_version: 1):

- `template:` name
- `server:` `id`, `scope.{level,estate,project}`, `slug`, `class`, `status`, `owner`, `domain`, `summary`
- `implementation:` `current`, `target`
- `protocol:` `latest_researched`, `json_rpc`, `lifecycle_required`, `deterministic_lists`, `structured_content`
- `transports:` `stdio.{enabled,use_for}`, `streamable_http.{enabled,endpoint,bind_default,require_origin_validation,require_auth_when_not_localhost,require_protocol_version_header}`, `compatibility_http.{enabled,endpoint,retire_when}`
- `namespaces:` `tools`, `resources`, `prompts`, `events`, `policy`, `observability`
- `endpoint_env`, `endpoint_env_global`, `endpoint_env_estate`, `endpoint_env_project`
- `latency_class`
- `dependencies:` `upstream_services`, `secrets[].{name,required,source,redaction}`, `data_sources`
- `registration:` `context_mesh_file`, `cluster_overlay_file`, `specialist_pack`

**Phase 2 additions per alignment runbook §5 (T2.2)**: `server_card.{path,version,public}`, `tasks.{supported,store,expiry_seconds}`, `mcp_apps.{ui_resources}`, `state.{posture, external_store}`, `conformance.{tier,suite_pass}`, `workspace.{id,slug}`, `json_schema_dialect: 2020-12`.

`tools.yaml`: `defaults.{timeout_ms,retry_policy,audit_event,output_envelope,require_structured_content}`, `tools[].{name,title,owner,lifecycle,safety.{read_only,destructive,idempotent,open_world,approval_required},input_schema,output_schema,observability.{span_name,metrics,redact_fields},evals,rollout_rule}`.

**Phase 2 additions**: per-tool `tasks.{supported,expiry_seconds}`, `otel.{span_name_override,low_cardinality_target}`, `code_execution_module:` per D-7.

`policy.yaml`: `auth.{localhost_stdio.allowed,streamable_http.{bind_default,require_origin_validation,require_auth_when_not_localhost,allowed_origins}}`, `permissions.{read,plan,mutate}.{approval,roles,required_fields}`, `tenancy.{required_actor_fields,default_tenant}`, `rate_limits.{default_per_minute,mutating_per_minute}`, `timeouts.{default_ms,mutating_ms}`, `secrets.{never_log,source_priority}`, `audit.{append_only,event_namespace,redaction_required}`.

**Phase 2 additions**: `auth.dpop.{required,key_thumbprint_binding}`, `auth.wif.{required,audience,trust_domain}`, `auth.xaa.{enabled}`, `scopes:` catalog, RFC 9728 metadata location, tool-description hardening, workspace_scope_claim.

`observability.yaml`: `health.{endpoint,checks}`, `events.{ring_buffer,jsonl,required_fields}`, `metrics.{counters,histograms,gauges}`, `tracing.{propagate_w3c_trace_context,mcp_meta_trace_context,span_names}`, `dashboard.warden_popup_tabs`, `redaction.{required,forbidden_fields}`.

**Phase 2 additions**: replace local metric names with OTel semconv (`mcp.server.operation.duration` etc.), declare histogram bucket boundaries, required attribute list.

`resources.yaml`: `resources[].{uri,title,kind,read_only,cache_ttl_ms,contains_secrets,output_mime}`, `templates[].{uri_template,title,arguments,read_only,contains_secrets}`.

**Phase 2 additions**: `ui_resources:` block per SEP-1865 (MCP Apps).

`prompts.yaml`: `prompts[].{name,title,description,arguments,returns.{format,includes},safety.{may_request_sampling,must_not_include_secrets}}`. Sampling now MUST NOT — `may_request_sampling: false` is the only valid value going forward.

`readiness.yaml`: `cluster_overlay`, `activation_state`, `required_checks[].{id,description,command,severity}`, `activation_gates.{draft_to_stub,stub_to_formal_mcp,formal_mcp_to_active}`, `rollback.on_failure`.

**Phase 2 additions**: gate ids `server_card_published`, `otel_semconv_emit`, `stateless_proof_or_durable_store_declared`, `tasks_durability_smoke`, `conformance_suite_pass`.

`evals.yaml`: `suites[].{id,purpose,cases}`, `quality_bars.{max_p95_latency_ms,max_error_rate_percent,no_secret_leaks,deterministic_lists}`.

**Phase 2 additions**: suite `evals.<domain>.federation_readiness` (Server Card discovery, task expiry, DPoP replay rejection, stateless proof).

`adapter-target.yaml`: `targets.{claude,cursor,codex,gemini,grok,groq}.{surface,role}`, `aliases.{dotted_tool_names_preferred,fallback_format,alias_event}`, `do_not_generate[]`.

**Phase 2 additions**: `discovery_source: server_card`.

### 3.15 — Authentik FastMCP Implementation Pattern

From `auth_k/server.py` (the only `formal-mcp` we have today):

```python
from mcp.server.fastmcp import FastMCP
from specialist_kit import bootstrap as kit_bootstrap
from specialist_kit import config as kit_config

# 1. Load specialist config (env + secrets paths)
cfg = kit_config.load(
    specialist_id="<slug>",
    display_name="<Persona Name>",
    namespace_id="<workspace_slug>",          # NOTE: still uses old name; v2 = workspace_id
    designated_lxc=<vmid>,
    port=<port>,
)

# 2. Build the FastMCP server
mcp = FastMCP("<slug>", instructions="<one-paragraph>")

# 3. Tool descriptors (passed to kit.bootstrap so they land in mcp_tool_registry)
_TOOL_DESCRIPTORS = [
    {
        "name": "<bare or dotted name>",      # bare today; Phase 6 fixes to dotted
        "title": "<title>",
        "description": "<description>",
        "annotations": {"readOnlyHint": True, "destructiveHint": False, "idempotentHint": True, "openWorldHint": False},
        "required_scope": "<oidc scope>",
    },
    ...
]

# 4. Bootstrap mesh membership
ctx = kit_bootstrap.bootstrap(
    config=cfg,
    persona_toml_path=<path to persona.toml>,
    mcp_tools=_TOOL_DESCRIPTORS,
)

# 5. Tool registrations
@mcp.tool(name="<bare or dotted name>", title="<title>", description="<description>",
          annotations={"readOnlyHint": True, ...})
def my_tool(arg1: str, arg2: int) -> SomeOutputSchema:
    return my_module.do_thing(_client, _audit, ...)

# 6. Entry point
mcp.run("stdio") | mcp.run("streamable-http")
```

**Known drift in auth_k/server.py to NOT repeat in new code**:
- Tool names are bare (`list_users`) instead of dotted (`authentik.list_users`) — spec 09 §6.3 violation; Phase 6 fixes with alias for backcompat
- `argparse --transport` accepts `sse` as a choice — deprecated per ADR 0030 §1; Phase 6 removes
- `kit_config.load` uses `namespace_id` parameter — ADR 0031 renamed to `workspace_id`; pending kit update

### 3.16 — Op Envelope Wire Contract

From `clyffy_dean/envelope.py` (duplicated symmetrically in
`agent/homelab-agent/agents/envelope.py` — change-one-change-both):

```python
@dataclass
class OpEnvelope:
    ok: bool = True
    error: str | None = None
    intended_state: dict[str, Any] = {}
    observed_state: dict[str, Any] = {}
    drift: list[dict[str, Any]] = []
    side_effects: list[str] = []
    dry_run: bool = False
    idempotency_key: str | None = None
    plan_id: str | None = None
    audit_id: str = uuid4()                     # server-side bookkeeping
    started_at: str = iso_now()
    finished_at: str = ""

    def finish(self, *, ok: bool = True, error: str | None = None) -> "OpEnvelope": ...
    def to_dict(self) -> dict[str, Any]: ...
```

This is the wire contract the existing `mcp.global.proxmox` (Clyffy Dean)
returns from its `POST /tools/{tool_name}` HTTP endpoints. The
`http-tools-bridge` proposed in `docs/MCP_CLIENT_NORMALIZATION_SPEC.md`
Piece 3 must translate this into MCP tool-call results:

- `ok: true` → `result.isError = false` (or unset); content + structuredContent
- `ok: false` → `result.isError = true`; content carries `error` text
- `intended_state` / `observed_state` / `drift` / `side_effects` → preserved
  in `structuredContent` per MCP 2025-11-25 to remain agent-readable
- `dry_run` / `idempotency_key` / `plan_id` → preserved in
  `structuredContent` for caller correlation
- `audit_id` / `started_at` / `finished_at` → forwarded into the gateway's
  audit event; not surfaced to the LLM unless the upstream declares the
  field as `read_only` and operator-relevant

### 3.17 — Validator State

From `scripts/foundation/validate-touchpoints.py`:

- Detects `clyffy_touchpoint:` or deprecated `wardenclyffe_touchpoint:`
- Required fields checked: `version`, `workspace_id`, `project_key`, `kind`, `owner`
- Optional: `module`
- Sync flags: `qdrant`, `surreal`
- Body word thresholds: `1200` for sync-enabled, `2500` for any
- Warns on v1 shape
- `--json` emits a structured inventory
- `--strict` exits non-zero on warnings

The validator does NOT yet check the full ADR 0033 §2 required set
(specifically `surreal.plane_a` and `audit.event_prefix` are unenforced).
Tracked as ADR 0033 §9 work and Phase 8 in the alignment runbook.

## 4. The Two Templates That Don't Yet Exist

Per alignment runbook §6 Phase 3 and Spec 14 §4–§5, two templates must
be authored before either gateway can be instantiated.

### 4.1 — `.agents/templates/mcp/l1-federation-gateway/`

Authoring contract — 10 files. The shape derived from Spec 14 §4 plus the
L2 leaf template shapes (because the gateway is itself an MCP server and
reuses most of those concerns with deltas).

| File | Purpose | Schema sketch |
|---|---|---|
| `README.md` | When to deploy a gateway, relationship to L0 / L2, pointer to spec 14 + ADR 0032 | Markdown, no schema |
| `gateway-contract.yaml` | Gateway identity (scope, slug, `class: gateway`), upstream resolution order, workspace ownership, conformance tier, transport posture, single auth front door | Same shape as `server-contract.yaml` with `class: gateway` plus a `gateway:` block: `upstreams_source` (registry filename), `scope_resolution_order`, `deny_precedence`, `single_auth_front_door`, `transport_translation_enabled` |
| `routing.yaml` | Namespace prefixing on collision, deny/allow precedence, circuit-breaker config, per-upstream timeout, retry budget, fallback rules | `prefix_on_collision_only: true`, `collision_prefix_pattern`, `denies[]`, `allows[]`, `circuit_breaker.{open_after_failures,half_open_after_ms,close_after_successes}`, `upstream_timeouts.{default_ms,per_upstream:{}}`, `retry_budget.{max_attempts,total_budget_ms}`, `fallback_rules[]` |
| `discovery.yaml` | Static list from registry; Server Card crawl interval; mDNS on LAN; Redis-backed peer state for multi-instance gateways | `static_source: registry/context-mesh.yaml`, `server_card_crawl.{enabled,interval_seconds,failure_threshold}`, `mdns.{enabled,domain}`, `peer_state.{store: redis|memory, url_env, sync_interval_seconds}` |
| `auth.yaml` | OAuth 2.1 + RFC 9728 (mandatory), DPoP, WIF, XAA, per-upstream credential propagation, scope-down rules, workspace identity claim mapping | `oauth2.{enabled: true, authorization_servers:[], resource_metadata_path: "/.well-known/oauth-protected-resource"}`, `dpop.{required:false}`, `wif.{required:false,audience,trust_domain}`, `propagation.{strategy: passthrough|scope_down, scope_down_rules:[]}`, `claim_mapping.{workspace_uuid,oidc_subject,scopes,actor}` |
| `observability.yaml` | OTel semconv 1.40.0 attributes (mandatory), gateway-specific attrs (`mcp.gateway.upstream_id`, `mcp.gateway.routing.decision`), federation audit shape, cross-server trace correlation | Same shape as L2 `observability.yaml` plus `gateway_attrs.{mcp_gateway_upstream_id: true, mcp_gateway_routing_decision: true, workspace_slug: true, workspace_peer_slug: true}` |
| `policy.yaml` | Rate limits per (tenant=workspace, scope, tool), approval-required tool elevation at gateway, redaction | Same shape as L2 `policy.yaml` plus `rate_limits.per_tenant_workspace_qpm`, `rate_limits.per_tool_qpm`, `approval_gates_consolidated: true`, `approval_gates.{policy_namespace, escalation_paths}` |
| `readiness.yaml` | Gateway preflight: upstream Server Cards parsed, auth flow proves out, OTel emitting, federation trace correlation visible end-to-end | Gate ids: `upstream_server_cards_parsed`, `oauth_flow_proves_out`, `otel_emitting`, `traceparent_propagation_verified`, `stateless_or_external_store_declared`, `circuit_breaker_proven` |
| `evals.yaml` | Federation evals: prefix collision rejection, upstream-down failover, scope deny precedence at gateway, traceparent end-to-end correlation | Suites: `evals.<workspace>.federation.{prefix_collision,upstream_down_failover,scope_deny_precedence,traceparent_correlation,tasks_passthrough}` |
| `topology.example.yaml` | Worked example: gateway upstreaming all leaves in scope with declared collisions and policy | YAML showing the gateway id, workspace, list of upstreams, prefix-on-collision examples |

### 4.2 — `.agents/templates/mcp/l0-workspace-publish/`

Authoring contract — 7 files. Shape derived from Spec 14 §5–§6 and the
workspace card JSON schema (§3.12 above).

| File | Purpose | Schema sketch |
|---|---|---|
| `README.md` | How a workspace advertises to ADR-0017 peers; relationship to L1 | Markdown |
| `workspace-card.yaml` | The workspace's identity + capability summary; mirrors §6.1 of spec 14; serialized to JSON for the well-known URL | Mirror of spec 14 §6.1 JSON shape in YAML |
| `peer-contract.yaml` | What peer workspaces may consume; per-peer scopes; trust relationships | `peers[].{peer_workspace_slug, peer_workspace_uuid, accepted_scopes:[], rejected_scopes:[], audit_lineage_required: true}` |
| `capability-publish.yaml` | Which L1-aggregated tools are advertised upward; redaction + scope-down rules | `published[].{server_id, advertised_as_scope, included_tools:[], excluded_tools:[], redaction_rules:[]}`, `private_servers_count_disclosed: true` |
| `auth.yaml` | Cross-workspace OIDC realm trust; DPoP/WIF/XAA at workspace boundary | `cross_workspace.{oidc_jwks_url, accepted_audiences:[], trust_bundle_url}`, `dpop.cross_workspace_required: false`, `wif.cross_workspace_required: false` |
| `observability.yaml` | Cross-workspace audit + trace correlation; federation lineage in audit events | `federation_audit.{enabled, write_to_both_sides: true, shared_request_id: true}`, `trace_correlation.{traceparent_forwarded: true, lineage_attrs:[workspace.slug, workspace.peer_slug]}` |
| `readiness.yaml` | Gates: federation_workspace row registered, OIDC realm provisioned, peer trust list reviewed, workspace card served at well-known | Gate ids: `federation_workspace_row_registered`, `oidc_realm_provisioned`, `peer_trust_list_reviewed`, `workspace_card_served_at_well_known`, `workspace_card_mirrored_in_federation_workspace_row` |

### 4.3 — `.agents/templates/mcp/shared/`

Per alignment runbook §6 Phase 2 setup, cross-cutting shared schemas:

| File | Purpose |
|---|---|
| `server-card.example.json` | Minimal valid `/.well-known/mcp/server-card.json` per ADR 0030 §1 — see §3.13 above |
| `server.json.example` | Optional MCP Registry publication record per Spec 09 §0.2 (deferred per D-8 but reference belongs here) |
| `otel-semconv.py` | Copy-paste FastMCP OTel setup (span names, attribute set, four histograms with bucket boundaries) |
| `otel-semconv.ts` | Same for TypeScript SDK |
| `error-design.md` | Protocol vs `result.isError` discipline per ADR 0030 §6 |
| `README.md` | What lives here and why |

## 5. The Two Gateway Instances — Schemas For Each File

These are the concrete fills the operator (or I, on the next turn) will
populate once the templates exist. Captured here so the file is precisely
specified before any keystrokes happen.

### 5.1 — `mcp.workspace.clyffy-master-gateway`

Resolving the gateway slug per Q-C (see §8) with the default
**hyphen-encode** rule: `workspace_slug = clyffy.master` →
gateway id = `mcp.workspace.clyffy-master-gateway`.

`gateway-contract.yaml`:

```yaml
schema_version: 1
template: l1-federation-gateway
server:
  id: mcp.workspace.clyffy-master-gateway
  scope:
    level: workspace
    workspace_slug: clyffy.master
    workspace_uuid: <backfill on federation_workspace heartbeat>
  slug: clyffy-master-gateway
  class: gateway
  # Status split per ADR 0035 — three independent fields:
  lifecycle: planned                              # → stub once template exists, → active when readiness gates pass
  conformance: formal-mcp                         # gateways MUST be formal-mcp per ADR 0030 §1 + Spec 14 §4
  provenance: wardenclyffe
  owner: wardenclyffe
  domain: clyffy-master
  summary: L1 federation gateway for the clyffy.master workspace. Single front door for AI orchestration minions (Auth-K, Bifrost, Observatory).
  implementation:
    target: TBD                                  # decided in Phase 3 sub-phase A (per ADR 0032 §"Open follow-ups")
  protocol:
    latest_researched: 2025-11-25
    json_schema_dialect: 2020-12                  # per ADR 0030 §2 / SEP-1613
    json_rpc: true
    lifecycle_required: true
    deterministic_lists: true
    structured_content: true
  transports:
    stdio:
      enabled: true
      use_for: [local_ide, codex, claude_desktop, claude_code]
    streamable_http:
      enabled: true
      endpoint: /mcp
      bind_default: 127.0.0.1                    # internal network only until edge-published
      require_origin_validation: true
      require_auth_when_not_localhost: true
      require_protocol_version_header: true
  state:
    posture: stateless                            # ADR 0030 §1 default
    external_store: null
  conformance:
    tier: 0                                       # per SEP-1730; bump as evals land
    suite_pass: false
  server_card:
    path: /.well-known/mcp/server-card.json
    version: 1
    public: true                                  # served once the workspace is reachable
  workspace:
    id: <workspace_uuid>
    slug: clyffy.master
  gateway:
    upstreams_source: wardenclyffe/registry/context-mesh.yaml
    scope_resolution_order: [workspace, project, estate, global]
    deny_precedence: true
    single_auth_front_door: true
    transport_translation_enabled: true
  namespaces:
    tools: tools.workspace.clyffy-master.gateway   # gateway aggregates; no leaf-domain here
    resources: resources.workspace.clyffy-master.gateway
    prompts: prompts.workspace.clyffy-master.gateway
    events: events.mcp.workspace.clyffy-master.gateway
    policy: policy.workspace.clyffy-master.gateway
    observability: observability.mcp.workspace.clyffy-master.gateway
  endpoint_env: CLYFFY_MASTER_GATEWAY_URL
  latency_class: lan
```

`routing.yaml`:

```yaml
schema_version: 1
server_id: mcp.workspace.clyffy-master-gateway
upstreams:
  # populated from registry/context-mesh.yaml at resolve time, listed here as
  # the planned aggregate for clarity
  - mcp.workspace.clyffy-master.authentik           # formal-mcp today (with drift)
  - mcp.workspace.clyffy-master.bifrost             # planned
  - mcp.workspace.clyffy-master.observatory         # planned
  - mcp.global.rykv                                 # external-existing
  - mcp.global.proxmox                              # mcp-shaped → bridged or formal
  - mcp.global.warden                               # planned (Rust rmcp)
  - mcp.global.dns                                  # planned
  - mcp.global.opnsense                             # planned
  - mcp.global.agent-runtime                        # planned
  - mcp.global.deploy                               # planned
prefix_on_collision_only: true
collision_prefix_pattern: "<upstream_slug>."        # e.g. "legacy.list_users" if a second list_users appears
denies: []
allows: []                                          # implicit: all-permitted unless denied
circuit_breaker:
  open_after_failures: 5
  half_open_after_ms: 30000
  close_after_successes: 3
upstream_timeouts:
  default_ms: 30000
  per_upstream: {}
retry_budget:
  max_attempts: 2
  total_budget_ms: 60000
fallback_rules:
  - on: upstream_degraded
    action: return_isError_with_actionable_content
```

`auth.yaml`:

```yaml
schema_version: 1
server_id: mcp.workspace.clyffy-master-gateway
oauth2:
  enabled: true
  resource_metadata_path: /.well-known/oauth-protected-resource
  authorization_servers:
    - issuer: https://auth.clyffy.master           # Authentik realm per ADR 0014 §4
      jwks_url: https://auth.clyffy.master/jwks.json
  accepted_audiences: [clyffy.master]
  dynamic_client_registration: true                # per RFC 7591 when AS permits
dpop:
  required: false                                  # SHOULD when tokens leave LAN; today gateway is internal
  key_thumbprint_binding: false
wif:
  required: false                                  # LXC deployment; not k8s/SPIFFE today
  audience: null
  trust_domain: null
xaa:
  enabled: false                                   # WATCH per ADR 0030 §1
propagation:
  strategy: scope_down                              # gateway is single front door; propagate scoped tokens downstream
  scope_down_rules:
    - upstream_class: leaf
      strategy: mint_short_lived_token
      lifetime_seconds: 300
claim_mapping:
  workspace_uuid: clyffy_touchpoint.workspace_uuid
  workspace_slug: clyffy_touchpoint.workspace_id
  oidc_subject: sub
  scopes: scope
  actor: act
```

`observability.yaml`:

```yaml
schema_version: 1
server_id: mcp.workspace.clyffy-master-gateway
observability_namespace: observability.mcp.workspace.clyffy-master.gateway
otel:
  semconv_version: "1.40.0"
  required_attributes: [mcp.method.name]
  recommended_attributes:
    - mcp.session.id
    - mcp.protocol.version
    - gen_ai.tool.name
    - gen_ai.operation.name
    - network.transport
    - server.address
    - client.address
    - jsonrpc.request.id
  gateway_attributes:
    - mcp.gateway.upstream_id
    - mcp.gateway.routing.decision
    - workspace.slug
    - workspace.peer_slug
  histograms:
    - mcp.client.operation.duration
    - mcp.server.operation.duration
    - mcp.client.session.duration
    - mcp.server.session.duration
  histogram_buckets_seconds: [0.01, 0.02, 0.05, 0.1, 0.2, 0.5, 1, 2, 5, 10, 30, 60, 120, 300]
  span_name_template: "{mcp.method.name} {target}"
  trace_context_via_meta: true                     # traceparent / tracestate / baggage in params._meta
health:
  endpoint: /healthz
  checks:
    - upstream_server_cards_parsed
    - oauth_metadata_served
    - traceparent_propagation
    - tool_registry_loaded
events:
  ring_buffer: { enabled: true, capacity: 4000 }   # gateway sees more traffic than a leaf
  jsonl: { enabled: false, env_dir: WARDEN_MCP_STATE_DIR }
  required_fields:
    - ts
    - level
    - component
    - mcp_method
    - tool_name
    - request_id
    - trace_id
    - duration_ms
    - status
    - mcp_gateway_upstream_id
    - mcp_gateway_routing_decision
dashboard:
  warden_popup_tabs:
    - overview
    - upstreams
    - tool_catalog
    - federation_routing
    - load
    - chokepoints
    - logs
    - traces
redaction:
  required: true
  forbidden_fields: [token, password, secret, api_key, authorization, cookie]
```

`policy.yaml`:

```yaml
schema_version: 1
server_id: mcp.workspace.clyffy-master-gateway
policy_namespace: policy.workspace.clyffy-master.gateway
auth:
  localhost_stdio:
    allowed: true                                  # operator devstation/capsule stdio
  streamable_http:
    bind_default: 127.0.0.1                        # internal until edge-published
    require_origin_validation: true
    require_auth_when_not_localhost: true
    allowed_origins: []                            # populated when public edge lands
permissions:
  read:
    approval: none
    roles: [operator, specialist.clyffy-master]
  plan:
    approval: none
    roles: [operator, specialist.clyffy-master]
  mutate:
    approval: human_required                       # consolidated at the gateway per ADR 0032 §3
    roles: [operator]
    required_fields: [dry_run, idempotency_key, approval_token]
tenancy:
  required_actor_fields: [principal, workspace_uuid]
  default_tenant: clyffy.master
rate_limits:
  per_caller_qps: 20
  per_tenant_workspace_qpm: 600
  per_tool_qpm: 120
  burst: 40
timeouts:
  default_ms: 30000
  mutating_ms: 120000
secrets:
  never_log: [token, password, secret, api_key, authorization, cookie]
  source_priority: [environment, infisical, local_secrets_dir]
audit:
  append_only: true
  event_namespace: events.mcp.workspace.clyffy-master.gateway
  redaction_required: true
  workspace_lineage: true                          # every event records workspace_uuid + workspace_slug
approval_gates_consolidated: true
approval_gates:
  policy_namespace: policy.workspace.clyffy-master.gateway.mutate
  escalation_paths:
    - operator_chat
    - warden_ui_approval_task
```

`readiness.yaml`:

```yaml
schema_version: 1
server_id: mcp.workspace.clyffy-master-gateway
cluster_overlay: wardenclyffe/agent/mcp-cluster/registry/domains/clyffy-master-gateway.yaml
activation_state: draft
required_checks:
  - id: registry_entry
    description: Gateway is present in registry/context-mesh.yaml gateways: block.
    severity: error
  - id: server_card_served
    description: /.well-known/mcp/server-card.json returns a valid SEP-1649/2127 document.
    severity: error
  - id: oauth_resource_metadata_served
    description: /.well-known/oauth-protected-resource returns RFC 9728 metadata.
    severity: error
  - id: upstream_server_cards_parsed
    description: Each registered upstream's Server Card is fetched and parsed at boot.
    severity: error
  - id: otel_semconv_emit
    description: Spans emit per ADR 0030 §3; four histograms present; bucket boundaries match.
    severity: error
  - id: traceparent_propagation
    description: Client traceparent propagates through gateway into upstream via params._meta.
    severity: error
  - id: stateless_proof
    description: Two parallel client sessions do not share state at gateway level.
    severity: error
  - id: prefix_collision_correctness
    description: Two upstreams publishing identical tool name route correctly per collision rule.
    severity: warning
  - id: scope_deny_precedence
    description: Deny at any scope blocks call regardless of allows at narrower scopes.
    severity: error
  - id: federation_eval_suite_pass
    description: evals.workspace.clyffy-master.gateway.federation passes.
    severity: error
activation_gates:
  draft_to_stub: [registry_entry, server_card_served]
  stub_to_formal_mcp: [oauth_resource_metadata_served, otel_semconv_emit, traceparent_propagation, stateless_proof]
  formal_mcp_to_active: [upstream_server_cards_parsed, scope_deny_precedence, federation_eval_suite_pass]
rollback:
  on_failure:
    - mark status degraded in registry/context-mesh.yaml
    - circuit-break all upstreams
    - serve cached tool catalog where available
    - return isError with actionable content for new tool calls
```

`evals.yaml`:

```yaml
schema_version: 1
server_id: mcp.workspace.clyffy-master-gateway
evals_namespace: evals.workspace.clyffy-master.gateway
suites:
  - id: evals.workspace.clyffy-master.gateway.protocol
    purpose: MCP lifecycle and transport compliance for the gateway as an MCP server.
    cases:
      - initialize negotiates 2025-11-25 protocol version
      - tools/list returns merged catalog from all healthy upstreams
      - tools/call routes per scope resolution order
      - stdout is JSON-RPC clean on stdio
      - Streamable HTTP validates Host AND Origin headers
  - id: evals.workspace.clyffy-master.gateway.federation
    purpose: Federation routing correctness.
    cases:
      - prefix_collision_rejection — two upstreams with same tool name resolve per collision rule
      - upstream_down_failover — degraded upstream returns isError with actionable content
      - scope_deny_precedence — deny at any scope blocks call
      - traceparent_correlation — single trace ID spans client → gateway → upstream
      - tasks_passthrough — long-running tool task IDs preserved across hops
  - id: evals.workspace.clyffy-master.gateway.security
    purpose: Auth and hardening.
    cases:
      - oauth_metadata_served — RFC 9728 document served
      - www_authenticate_on_401 — header carries resource metadata URL
      - tool_description_declarative_only — no imperative tool descriptions present
      - additional_properties_false — every input schema closed
      - cross_workspace_token_audience_enforced — token with wrong aud rejected
quality_bars:
  max_p95_latency_ms: 1500                         # gateway has additional hop latency
  max_error_rate_percent: 1
  no_secret_leaks: true
  deterministic_lists: true
  upstream_health_visibility: true
```

`discovery.yaml`:

```yaml
schema_version: 1
server_id: mcp.workspace.clyffy-master-gateway
static_source: wardenclyffe/registry/context-mesh.yaml
server_card_crawl:
  enabled: true
  interval_seconds: 300
  failure_threshold: 3
mdns:
  enabled: false                                   # not needed on internal LAN with static registry
  domain: null
peer_state:
  store: memory                                    # single-gateway today; switch to redis when HA lands
  url_env: null
  sync_interval_seconds: null
```

`topology.example.yaml`:

```yaml
schema_version: 1
workspace_slug: clyffy.master
gateway: mcp.workspace.clyffy-master-gateway
upstreams:
  workspace_private:
    - id: mcp.workspace.clyffy-master.authentik
      tool_count_advertised: 9
      collision_with: []
    - id: mcp.workspace.clyffy-master.bifrost
      tool_count_advertised: 4
      collision_with: []
    - id: mcp.workspace.clyffy-master.observatory
      tool_count_advertised: 4
      collision_with: []
  global:
    - id: mcp.global.rykv
      tool_count_advertised: 6
      collision_with: []
    - id: mcp.global.proxmox
      tool_count_advertised: 7
      collision_with: []
    # remaining global leaves listed as they come online
collision_rule_examples:
  - upstream_a: mcp.workspace.clyffy-master.authentik
    upstream_b: hypothetical.legacy-authentik
    collided_tool: list_users
    resolution: prefix_upstream_b_with_legacy-authentik
```

### 5.2 — `mcp.workspace.wardenclyffe-infra-gateway`

(Defaulting to hyphen-encode per Q-C: `workspace_slug = wardenclyffe.infra` →
gateway id = `mcp.workspace.wardenclyffe-infra-gateway`.)

Same nine YAML files. The deltas vs. the Clyffy.master gateway:

- `server.id`: `mcp.workspace.wardenclyffe-infra-gateway`
- `server.scope.workspace_slug`: `wardenclyffe.infra`
- `server.workspace.slug`: `wardenclyffe.infra`
- `server.summary`: "L1 federation gateway for the wardenclyffe.infra workspace. Operator-facing infrastructure control plane minions (Warden, Dean/Proxmox, DNS, OPNsense, agent-runtime, deploy)."
- `gateway.upstreams_source`: same file but routing scope is workspace + global only
- `auth.oauth2.authorization_servers`: a separate Authentik realm (`wardenclyffe-infra`) if D-Q-D resolves to two-realm; else shared with `clyffy.master`
- `auth.oauth2.accepted_audiences`: `[wardenclyffe.infra]`
- `endpoint_env`: `WARDENCLYFFE_INFRA_GATEWAY_URL`
- `routing.upstreams`: list excludes `mcp.workspace.clyffy-master.*` (those are workspace-private to Clyffy); includes the WardenClyffe-owned global leaves and rykv

The `routing.upstreams` for wardenclyffe-infra-gateway:

```yaml
upstreams:
  workspace_private: []                            # no wardenclyffe-private leaves declared today
  global:
    - mcp.global.rykv
    - mcp.global.proxmox
    - mcp.global.warden
    - mcp.global.dns
    - mcp.global.opnsense
    - mcp.global.agent-runtime
    - mcp.global.deploy
```

If Q-B resolves "WardenClyffe-owned globals move to
workspace.wardenclyffe.infra scope," then the `workspace_private` and
`global` arrays swap their content for those leaves.

### 5.3 — Workspace cards (L0 publish)

`workspace-card.yaml` (in the L0 publish instance for `clyffy.master`):

```yaml
schema_version: 1
workspace_uuid: <UUID>                              # backfilled on federation_workspace heartbeat
workspace_slug: clyffy.master
workspace_name: Clyffy Master
workspace_kind: master
workspace_owner_email: <operator-contact>
endpoint: https://master.clyffy.ai
api_version: v1
capabilities: [mcp-l1-gateway, persona-author]
mcp:
  gateway_url: https://master.clyffy.ai/mcp
  supported_protocol_versions: [2025-11-25]
  auth_methods: [oauth2.1]
  published_servers:
    - server_id: mcp.workspace.clyffy-master.authentik
      scope_advertised_as: workspace
      tool_count: 9
  private_servers_count: 0
federation:
  supports_protocols: [clyffy.federation/v1]
  agntcy_oasf_record_url: null
trust:
  oidc_jwks_url: https://auth.clyffy.master/jwks.json
  accepted_audiences: [clyffy.master]
_meta:
  schema_version: 1
  generated_at: <iso8601 at first publish>
```

`peer-contract.yaml`:

```yaml
schema_version: 1
workspace_slug: clyffy.master
peers:
  - peer_workspace_slug: wardenclyffe.infra
    peer_workspace_uuid: null                       # backfill
    accepted_scopes: [clyffy.federation:read]
    rejected_scopes: [clyffy.federation:write]
    audit_lineage_required: true
  # future:
  # - peer_workspace_slug: effing.personal-site
  #   ...
```

`capability-publish.yaml`:

```yaml
schema_version: 1
workspace_slug: clyffy.master
published:
  - server_id: mcp.workspace.clyffy-master.authentik
    advertised_as_scope: workspace                  # peers see it as scope=workspace
    included_tools: [authentik.list_users, authentik.list_brands, authentik.list_providers]
    excluded_tools: [authentik.user_userinfo]       # never published — bearer token surface
    redaction_rules: []
private_servers_count_disclosed: true
```

Same shape mirrored for `wardenclyffe.infra` with appropriate values.

## 6. What I Still Don't Know Precisely

Each item names the source that would resolve it, the deliverable it
blocks, and a default position if the gap remains unresolved.

| # | Gap | Source that resolves | Blocks | Default if unresolved |
|---|---|---|---|---|
| G1 | The exact ADR 0017 federation_workspace table fields beyond what ADR 0031 already names | ADR 0017 (unread) | Workspace card generation; federation directory writes | Use the ADR 0031 §7 superset; assume ADR 0017's `endpoint`, `api_version`, `capabilities`, `postgres_dsn`, `supabase_ref`, `qdrant_collections`, `federation_policy`, `status`, `last_seen`, `metadata` fields are correct |
| G2 | The data-plane catalog beyond what ADR 0031 §3 lists | ADR 0025 (unread), ADR 0028 (unread) | Touchpoint `surreal.plane_a.ns` validation; future RRD work | Use the six planes named in ADR 0031 §3 (`clyffy`, `wardenclyffe`, `projects`, `platform_devpulse`, `agents`, `global`) |
| G3 | The specialist capability pack contract beyond what manifest.yaml declares | Spec 08 (unread); `specialist-capability-pack` template files (5 unread) | Specialist instantiation from `templates/clyffy-specialists/_skeleton/` | The current `wardenclyffe/.agents/templates/specialist-capability-pack/manifest.yaml` is the minimum shape; deeper contracts likely add policy.yaml + tools.yaml + evals.yaml + trace-schema.json + ROLE.md |
| G4 | The mcp-cluster readiness overlay schema beyond what `cluster.yaml` shows | Spec 10 (unread); existing cluster.yaml + domains/*.yaml + templates/mcp-domain-template.yaml | Activation gates for the two gateways once instantiated | Default: same readiness check ids as the L2 leaf template's `readiness.yaml` with gateway-specific additions per §5.1 above |
| G5 | Rust `rmcp` API surface for the gateway implementation | `warden-mcp-foundation-checkpoint.md` (unread) | Picking the gateway implementation language | Default proposal: Python FastMCP for v0 gateway (matches Authentik kit); migrate to Rust `rmcp` when warden-mcp lands |
| G6 | `specialist_kit` bootstrap contract beyond what `auth_k/server.py` shows | `specialists/specialist_kit/` sources (unread) | Reusing the kit's bootstrap/heartbeat/audit pattern in a gateway | Default: read the kit before authoring the gateway implementation; `kit_bootstrap.bootstrap(config, persona_toml_path, mcp_tools)` signature is the only verified entry |
| G7 | The `specialists.yaml` registry shape | `wardenclyffe/registry/specialists.yaml` (unread); root AGENTS.md references it | Specialist registration after instantiation | Default: skim the file once it matters; not blocking for gateway authoring |
| G8 | The exact `wardenclyffe/AGENTS.md` v1→v2 migration path beyond ADR 0033 §10 | Alignment runbook §6 Phase 8 (read); existing v1 frontmatter | Cascading touchpoint updates | Default: Phase 8 work; not blocking for gateway authoring |
| G9 | Per-client config schemas for Codex / Gemini beyond what `docs/MCP_CLIENT_NORMALIZATION_SPEC.md` proposed | Each CLI's docs; verified against installed CLIs | Renderer M3 deliverable | Default: defer; the gateways are independent of which clients consume them |
| G10 | The exact persona.toml schema and `clyffy.persona_definition` row shape | `auth_k/persona.toml` + `clyffy.ai_memory` schema | Gateway persona declaration | Default: skim Authentik's `persona.toml` before authoring gateway personas |
| G11 | Workspace card cryptographic signing / trust anchor | Future ADR per Spec 14 §10 (parked) | Cross-workspace publish freezing | Default: serve the card unsigned for now; the workspace-card schema lock-via-ADR will add signing fields later |

## 7. Mapping Of Decisions To Documents That Already Captured Them

To prevent re-deciding what's already decided, here's the resolution
record for the 10 alignment-runbook decisions (D-1…D-10) — all resolved
2026-05-22 per the runbook §12 (which I read).

| Decision | Resolution | Where captured | Affects gateway work? |
|---|---|---|---|
| D-1 — Workspace vs namespace | ACCEPT | ADR 0031 | Yes — drives all `workspace.<slug>` shape decisions |
| D-2 — ADR 0033 lock-or-revise | REVISE | ADR 0033 v2 | Yes — drives all touchpoint frontmatter |
| D-3 — mcp-builder skill location | BOTH | mcp-builder skill (project-local + global) | Indirectly |
| D-4 — Federation scope | L0+L1+L2 | ADR 0032, Spec 14 | Yes — drives the three-template requirement |
| D-5 — Skill rewrite depth | FULL | mcp-builder skill | Indirectly |
| D-6 — Phase ordering | STRICT SERIAL | alignment runbook §4.2/§6 | Yes — Phase 3 (gateway template) blocked by Phase 1 (specs) and unblocks Phase 6 (server migrations) |
| D-7 — Code execution with MCP | YES (opt-in per leaf) | ADR 0030 §7.1, leaf template Phase 2 addition | Indirectly — leaf concern, not gateway concern |
| D-8 — MCP Registry publish | DEFER | ADR 0030 §8 | No — gateway is operator-private |
| D-9 — MCPB packaging | DEFER | ADR 0030 §8 | No |
| D-10 — AGNTCY/OASF interop | PLAN ONLY | Spec 14 §10, workspace-card field hook | Indirectly — workspace-card carries `agntcy_oasf_record_url` placeholder |

## 8. Open Decision Points The Operator Must Resolve

These are the new questions raised by the audit. Each has a proposed
default. The operator either accepts the defaults wholesale, accepts
selectively, or replaces.

### Q-A — Does Phase 3 of the alignment runbook get authored before any gateway instantiation?

**Context**: §6 of the alignment runbook resolved D-6 as STRICT SERIAL.
Phase 3 (L1 federation gateway template + L0 workspace publish template +
shared schemas) is unstarted. Strictly-serial means Phase 3 must complete
before any gateway gets an instance.

The user's directive "We will see a mesh network already getting
established for Clyffy and then WardenClyffe" implies gateway
instantiation. STRICT SERIAL says: author the templates first, then
instantiate.

**Proposed default**: ACCEPT — author the L1, L0, and shared templates
first. Phase 3 lands as one well-bounded PR. Then instantiation is two
template fills.

Alternative: author ad-hoc gateway YAMLs in `templates/clyffy-specialists/`
without the L1/L0 templates existing. Faster; violates D-6; pollutes the
specialist-skeleton tree with gateway-specific content. Not recommended.

### Q-B — Are WardenClyffe-owned global leaves rescoped to `workspace.wardenclyffe.infra`?

**Context**: Per §2.3 above. Today the registry has
`mcp.global.proxmox`, `mcp.global.warden`, etc. Spec 09 §4 reserves
`global` for cross-estate leaves; WardenClyffe infra is workspace-private.

**Proposed default**: DEFER. Keep current `mcp.global.*` IDs for
WardenClyffe-owned leaves until a peer workspace exists that would
otherwise discover them. Re-scope as a single coordinated PR when the
first peer-workspace integration happens.

Alternative: Rescope now. Larger sweep, more docs touched. Justified only
if a peer integration is imminent.

### Q-C — Gateway slug encoding when workspace_slug contains dots

**Context**: Per §2.6 above. Spec 14 §2.2 says
`mcp.workspace.<workspace_slug>-gateway`. Slug `clyffy.master` (with dot)
would produce `mcp.workspace.clyffy.master-gateway`. Existing registry
uses hyphen-encode (`clyffy-master-gateway`). The encoding rule isn't
documented anywhere.

**Proposed default**: ACCEPT hyphen-encode (Option B). Document the rule
as a one-line addition to Spec 14 §2.2 in the Phase 3 PR: "when embedding
`workspace_slug` into an MCP id, replace `.` with `-`."

Alternative A: preserve dotted slug (`mcp.workspace.clyffy.master-gateway`).
Cleaner ADR-literal reading; collides ambiguously with scope dotted-path.

Alternative C: UUID embed (`mcp.workspace.01HXXX-gateway`). Stable; humanly
opaque.

### Q-D — Do the two workspaces share one Authentik realm, or have separate realms?

**Context**: ADR 0014 §1 (extended by ADR 0024 §4 and ADR 0031 §6) sets
per-workspace-per-realm as the default: `effing-personal`, `clyffy-master`,
`clyffy-bundle-<customer>`. Naming for `wardenclyffe.infra` would be
`wardenclyffe-infra`.

**Proposed default**: ACCEPT per-workspace realm. Authentik LXC 103
provisions both realms. Cross-workspace OIDC tokens carry
`aud=[<peer-slug>]` per ADR 0014 §4. The operator authorizes the trust
direction explicitly.

### Q-E — `templates/clyffy-specialists/` location relative to the L1 + L0 templates

**Context**: I authored `templates/clyffy-specialists/_skeleton/` at the
repo root as a *product-layer* shell distinct from the MCP-wire shell at
`wardenclyffe/.agents/templates/mcp/`. The audit confirms this separation
is sound — the product layer is the operator-facing roster; the MCP-wire
layer is the implementation contract. But the skeleton's `manifest.yaml`
points at `mcp-mesh-server/` (about to be renamed to `mcp/l2-leaf-server/`).
That reference must be swept.

**Proposed default**: ACCEPT the two-layer split. Sweep the path
references in `templates/clyffy-specialists/_skeleton/README.md` and
`manifest.yaml` from `mcp-mesh-server/` to `mcp/l2-leaf-server/` as part
of Phase 2.

### Q-F — Initial gateway implementation language: Python FastMCP or Rust rmcp?

**Context**: G5 above. The Rust `agent/warden-mcp/` is planned but unbuilt
per `warden-mcp-foundation-checkpoint.md` (unread). FastMCP works today
in Auth-K.

**Proposed default**: Python FastMCP for v0 gateway. Mirrors Auth-K's
proven path. Reuses `specialist_kit.bootstrap()`. Easier to evolve while
the contract beds in. Migrate to Rust `rmcp` when the warden-mcp work
lands and the gateway contract has stabilized.

## 9. Deliverable Plan — Phase 3 Of The Alignment Runbook

The work that closes this audit and gets two gateways instantiated, in
strict serial order per D-6.

### A — Revise prior docs (small)

- **D-A-1**: Edit [docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md](CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md) — withdraw the rename ADR proposal (then labeled "ADR 0035"; that number was later reused for the unrelated Server Status Field Split), remove the rename map, update the 10-specialist table to use the existing registry IDs. Surgical.
- **D-A-2**: Edit [wardenclyffe/registry/context-mesh.yaml](../wardenclyffe/registry/context-mesh.yaml) `client_wrapper_normalization:` block I added — remove the managed_key_prefix line (irrelevant to a no-rename world). Surgical.
- **D-A-3**: Edit [templates/clyffy-specialists/_skeleton/README.md](../templates/clyffy-specialists/_skeleton/README.md) and [templates/clyffy-specialists/_skeleton/manifest.yaml](../templates/clyffy-specialists/_skeleton/manifest.yaml) — sweep `mcp-mesh-server/` → `mcp/l2-leaf-server/`. Surgical.
- **D-A-4**: Edit [docs/MCP_CLIENT_NORMALIZATION_SPEC.md](MCP_CLIENT_NORMALIZATION_SPEC.md) — sweep `.agents/templates/mcp-mesh-server/` references → `.agents/templates/mcp/l2-leaf-server/`. Surgical.

### B — Author Phase 3 templates

- **D-B-1**: `.agents/templates/mcp/shared/` directory and its 6 files (server-card.example.json, server.json.example, otel-semconv.{py,ts}, error-design.md, README.md).
- **D-B-2**: `.agents/templates/mcp/l1-federation-gateway/` and its 10 files per §4.1 above.
- **D-B-3**: `.agents/templates/mcp/l0-workspace-publish/` and its 7 files per §4.2 above.

### C — Instantiate the two gateways and the two workspace-publish surfaces

- **D-C-1**: `templates/clyffy-master-gateway/` (product-layer touchpoint) plus the L1 fill at `wardenclyffe/specialists/clyffy-master-gateway/` (or wherever the operator decides — Q-F implementation language drives the location).
- **D-C-2**: Same for `wardenclyffe-infra-gateway`.
- **D-C-3**: `wardenclyffe/specialists/clyffy-master-workspace-publish/` and `wardenclyffe/specialists/wardenclyffe-infra-workspace-publish/` carrying the L0 fill (workspace card, peer contract, capability publish).

### D — Update the registry

- **D-D-1**: Add the `mcp.workspace.wardenclyffe-infra-gateway` entry to `gateways:` block.
- **D-D-2**: Populate `workspace_publish:` for both workspaces with `workspace_card_url` and seed `federation_peers` (each lists the other).
- **D-D-3**: Add `aliases:` / new fields if Q-B resolves to rescope global leaves (likely deferred per default).

### E — Stand the readiness up

- **D-E-1**: `wardenclyffe/agent/mcp-cluster/registry/domains/clyffy-master-gateway.yaml` readiness file.
- **D-E-2**: Same for `wardenclyffe-infra-gateway.yaml`.
- **D-E-3**: Run the readiness eval suites once any implementation exists.

## 10. Don't Do (audit-specific)

- Do not start the L1 + L0 template authoring before §9 D-A-1…D-A-4 land. The legacy `mcp-mesh-server/` path and the withdrawn rename ADR must be cleaned up first or Phase 3 carries the inconsistency forward.
- Do not author a gateway-specific specialist-capability-pack at `wardenclyffe/specialists/clyffy-master-gateway/` until the L1 template exists. The gateway-specialist contract derives from that template; building an instance against an unwritten template is exactly the "parallel building" hazard ADR 0033 was written to prevent.
- Do not copy Auth-K's `auth_k/server.py` as a template. It carries known drift (bare tool names, SSE support, `namespace_id` arg name). Use the alignment-runbook Phase 6 corrections as guard.
- Do not embed secrets in any of the YAML files above. Every secret is an env-var reference; every key path is a slot, not a value.
- Do not declare `state.posture: stateless` while keeping any in-process tool catalog state. Either prove statelessness or declare `external_store:` honestly.
- Do not create a `wardenclyffe-infra-gateway` if Q-A resolves "single gateway for the estate." The two-gateway shape is the user's stated directive but the operator can still collapse it after seeing this audit.

## 11. References (audit-anchored)

All references inline above. Index for navigation:

- ADRs: [0030](../wardenclyffe/docs/decisions/0030-mcp-2026-baseline.md), [0031](../wardenclyffe/docs/decisions/0031-workspace-identity.md), [0032](../wardenclyffe/docs/decisions/0032-mcp-federation-three-layer.md), [0033](../wardenclyffe/docs/decisions/0033-touchpoint-protocol.md)
- Specs: [09](../wardenclyffe/docs/specs/09-context-mesh-and-naming.md), [14](../wardenclyffe/docs/specs/14-mcp-federation-and-workspace.md)
- Runbook: [alignment checkpoint](../wardenclyffe/docs/runbooks/mcp-2026-alignment-checkpoint.md)
- Templates: [mcp-mesh-server/](../wardenclyffe/.agents/templates/mcp-mesh-server/) (current name; renames to `mcp/l2-leaf-server/` in Phase 2)
- Live formal MCP: [Authentik server.py](../wardenclyffe/specialists/clyffy-authentik-specialist/auth_k/server.py)
- Wire contract: [op envelope](../wardenclyffe/agent/clyffy-dean/clyffy_dean/envelope.py)
- Validator: [validate-touchpoints.py](../scripts/foundation/validate-touchpoints.py)
- Prior session deliverables: [MCP_CLIENT_NORMALIZATION_SPEC.md](MCP_CLIENT_NORMALIZATION_SPEC.md), [HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md](ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md), [CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md](CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md)
