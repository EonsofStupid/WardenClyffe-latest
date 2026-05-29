---
clyffy_touchpoint:
  version: 2
  workspace_id: clyffy.master
  project_key: clyffy-specialist-namespaces
  persona: clyffy-operator
  kind: namespace-decisions
  owner: docs/CLYFFY_SPECIALIST_NAMESPACE_DECISIONS.md
  module: module-01-warden
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/CLYFFY_MCP_ORCHESTRATOR.md
    - docs/MCP_CLIENT_NORMALIZATION_SPEC.md
    - docs/ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md
    - docs/ai/INTELLIGENCE_LAYER_MODERNIZATION.md
    - wardenclyffe/registry/context-mesh.yaml
    - wardenclyffe/docs/decisions/0030-mcp-2026-baseline.md
    - wardenclyffe/docs/decisions/0031-workspace-identity.md
    - wardenclyffe/docs/decisions/0032-mcp-federation-three-layer.md
    - wardenclyffe/.agents/templates/mcp-mesh-server/README.md   # renamed to mcp/l2-leaf-server/ per alignment runbook Phase 2
    - wardenclyffe/.agents/templates/specialist-capability-pack/manifest.yaml
  sync:
    qdrant: true
    surreal: true
---

# Clyffy Specialist Namespace Decisions

This document locks in four connected decisions that have been sitting
ambiguous in the registry and the orchestrator doc:

1. The MCP server ID shape — **ratified as-is per ADR 0032 §5, spec 09
   §4, spec 14 §2** (the `workspace.` scope segment stays; see
   [docs/CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md](CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md)
   §2.1 for the audit that forced this correction).
2. The product-level split between Clyffy.master (AI orchestration minions)
   and WardenClyffe (infrastructure control plane minions), with `global`
   reserved for truly cross-cutting utilities.
3. The set of ten specialist namespaces and their canonical IDs (using
   the existing registry shape, not a rename).
4. The default-capabilities-plus-seed-values pattern each specialist
   instantiates, including how project attunement works through the RRD
   (per `docs/ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md`).
5. The encoder/embedder/reranker model picks for May 2026, with the
   load-bearing principle that **it is the code and state, not the model
   weights, that determine whether a transformer acts as an embedder, a
   generator, or a reranker**.

This doc does not modify the registry. The prior version of this doc
proposed a rename ADR labeled "provisional ADR 0035" at the time; that proposal was
**withdrawn** after reading ADR 0032 §5, spec 09 §4, and spec 14 §2 —
see the readiness audit referenced above. The `workspace.` scope segment
is load-bearing in the federation model and not redundant.

## Layman Overview

The registry has tool-server names that look long but are precise:
`mcp.workspace.clyffy-master.authentik`,
`mcp.workspace.clyffy-master-gateway`, `mcp.global.proxmox`. Those
prefixes (`workspace.`, `global.`, `estate.`, `project.`) encode the
federation scope of each server — they are NOT redundant; they are how
the federation gateway routes calls per ADR 0032 §4 (resolution order
`workspace → project → estate → global`, deny precedence). The ratified
shape stays.

The product-level split is the part that does matter to operators:
**Clyffy.master** owns AI orchestration minions; **WardenClyffe.infra**
owns infrastructure control plane minions; **global** holds utilities
that legitimately sit outside both, like the hot-path memory cache. These
are workspace boundaries per ADR 0031, surfaced today as either
`mcp.workspace.<slug>.<domain>` (workspace-private) or
`mcp.global.<domain>` (cross-estate).

There are ten specialist namespaces today. Each has a small template
("skeleton") that captures what it knows the moment it boots, plus a seed
file that teaches it about the current project by indexing the project's
own files. The default capabilities are what every Authentik specialist
knows; the seed values are what *this* Authentik specialist knows about
*this* project. Both live in version control; the project-specific layer is
regenerated from the project's artifacts whenever they change.

For the embedding model: BGE-M3 is the right pick for May 2026 because
it runs on CPU, gives us dense, sparse, and ColBERT vectors from one
model, and the homelab has no GPU yet. NV-Embed-v2 is the upgrade target
when a GPU arrives. We do not use the same model for embedding and
generation — they have different optimization targets and lifecycles, even
though the underlying transformer architecture can technically serve both.

## Decision 1 — MCP ID Naming Ratified Per ADR 0032 §5

### The ratified shape

The MCP server ID shape stays exactly as ADR 0032 §5, spec 09 §4, and
spec 14 §2 already define it. **No rename.** The prior version of this
document proposed dropping the `workspace.` scope segment; that proposal
was withdrawn after reading the federation-model contracts (see
[CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md](CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md)
§2.1 for the audit that forced this correction). The `workspace.`,
`global.`, `estate.`, `project.` scope prefixes are how the federation
gateway routes calls per ADR 0032 §4 — they are not redundant data.

### Canonical patterns (for reference; no change)

| Layer | Pattern | Example |
|---|---|---|
| L2 leaf, workspace-private | `mcp.workspace.<workspace_slug>.<domain>` | `mcp.workspace.clyffy-master.authentik` |
| L2 leaf, global scope | `mcp.global.<domain>` | `mcp.global.proxmox`, `mcp.global.rykv` |
| L2 leaf, estate scope | `mcp.estate.<estate>.<domain>` | `mcp.estate.aiaas.proxmox` |
| L2 leaf, project scope | `mcp.project.<project>.<domain>` | `mcp.project.wardenclyffe-core.proxmox` |
| L1 federation gateway | `mcp.workspace.<workspace_slug>-gateway` | `mcp.workspace.clyffy-master-gateway` |
| L1 HA instance | `mcp.workspace.<workspace_slug>-gateway-<instance>` | `mcp.workspace.clyffy-master-gateway-edge` |
| Tool ID | `<domain>.<verb>_<object>` | `proxmox.list_nodes`, `authentik.list_users` |

Slug grammar per ADR 0031 §2 / ADR 0017 §1: `<realm>.<role>[.<qualifier>]`,
lowercase kebab-case (`clyffy.master`, `effing.personal-site`,
`wardenclyffe.infra`, `clyffy.bundle.acme-pharma`).

### The dot-vs-hyphen encoding rule (informally adopted)

Spec 14 §2.2 names the L1 gateway pattern as
`mcp.workspace.<workspace_slug>-gateway`. The slug `clyffy.master`
contains a dot, which would literally produce
`mcp.workspace.clyffy.master-gateway` — ambiguous with the dotted
scope-path. The existing registry entry uses hyphen-encode:
`mcp.workspace.clyffy-master-gateway`.

The encoding rule we follow (proposed addition to spec 14 §2.2 as a
one-line clarification): **when embedding `workspace_slug` into an MCP
id, replace `.` with `-`.**

Consequence for the two gateways being instantiated:

| Workspace | Slug | Gateway ID |
|---|---|---|
| Clyffy Master | `clyffy.master` | `mcp.workspace.clyffy-master-gateway` |
| WardenClyffe Infra | `wardenclyffe.infra` | `mcp.workspace.wardenclyffe-infra-gateway` |

### What this means for the registry — no rename, deferred rescope

No registry rename. The existing entries stay. The `mcp.global.*` IDs
held by WardenClyffe-owned infrastructure leaves (proxmox, warden, dns,
opnsense, agent-runtime, deploy) **may** be rescoped to
`mcp.workspace.wardenclyffe-infra.<domain>` in the future per spec 09
§4 ("workspace-scoped: leaf serves one workspace and never crosses to
peers"), but that move is deferred until a peer workspace would
otherwise discover them. See readiness audit §2.3 and open decision Q-B.

### Withdrawn

Below was the prior (now-withdrawn) content of this decision. Retained
as a one-line note so the record of the correction is visible in git
history. Do not re-introduce.

- ~~Drop `workspace.` segment; rename `mcp.global.*` for WardenClyffe-owned~~ — **WITHDRAWN** per ADR 0032 §5.

## Decision 2 — Project Separation: Clyffy.master vs WardenClyffe vs Global

The orchestrator doc already names the architectural split
(`docs/CLYFFY_MCP_ORCHESTRATOR.md`): "Clyffy orchestrates context and
tools. Warden executes infrastructure authority. Clyffe exposes only
customer-safe outcomes." This decision moves that split out of prose and
into the namespace itself.

### The three project buckets (per ADR 0032 §4 scope tiers)

| Bucket pattern | Who lives here | Customer-visible? | Surface |
|---|---|---|---|
| `mcp.workspace.clyffy-master.*` | Clyffy's team of AI minions — identity, LLM bridge, observability, future memory/research/UX specialists | Indirectly (through Clyffy.ai UI) | Clyffy.ai web UI; Clyffy Master VM runtime |
| `mcp.workspace.wardenclyffe-infra.*` *(deferred — see below)* or `mcp.global.*` *(today)* | Infrastructure control plane minions — Proxmox, Warden, DNS, OPNsense, deploy, agent-runtime | Never directly (operator-only) | Warden Go UI on warden.rrflow.ai |
| `mcp.global.*` | Genuinely cross-cutting utilities that all buckets call | Indirectly | None — invoked, not browsed |

The current registry scopes the WardenClyffe-owned infrastructure leaves as
`mcp.global.*` (`mcp.global.proxmox`, `mcp.global.warden`,
`mcp.global.dns`, `mcp.global.opnsense`, `mcp.global.agent-runtime`,
`mcp.global.deploy`). Per spec 09 §4 these are properly
*workspace-private* to `wardenclyffe.infra` since they are operator-only
and never cross to peer workspaces. Rescoping them to
`mcp.workspace.wardenclyffe-infra.*` is the strictly-correct move, but
deferred until a peer workspace would otherwise discover them. See
[CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md](CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md)
§2.3 and open decision Q-B.

`mcp.global.rykv` is the one truly cross-cutting utility today (zero-ms
in-process hot-path memory cache; per spec 09 §8 reserved as
`internal-hotpath` class).

### Clyffy.ai vs Warden UI implication

The user-facing implication: **Clyffy.ai** is the UI for the
`mcp.workspace.clyffy-master.*` bucket — Clyffy and his team of
specialist minions, attuned to the user's project. **Warden Go UI**
(warden.rrflow.ai) is the operator-facing UI for the WardenClyffe-owned
leaves (today `mcp.global.*` subset; tomorrow
`mcp.workspace.wardenclyffe-infra.*` after Q-B resolves) — infrastructure
control. Same registry, two front doors, no overlap.

Concrete consequence for the future UI work (out of scope for this doc
beyond the contract):

- Clyffy.ai surfaces: minion roster, current project attunement, RRD
  health, recent reasoning, conversation threads, the dynamic UI per
  `docs/CLYFFY_DYNAMIC_UI_SPEC.md`.
- Warden Go UI surfaces: foundation status, MCP mesh health for the
  WardenClyffe-owned leaves, audit, tasks, approvals, devstation/capsule
  streams.
- Each UI consumes the SAME registry but filters by `workspace:` field /
  scope tier.
- A specialist that needs to appear in both UIs (e.g. observability)
  lives at `mcp.workspace.clyffy-master.observatory` and is *quoted* by
  the Warden UI through the gateway, not duplicated.

## Decision 3 — The Ten Specialist Namespaces

These are the ten specialists implied by the orchestrator doc and already
present in the registry. Each has a canonical ID, a target bucket, a
status as of 2026-05-27, and a one-line capability summary that the
skeleton expands.

### Clyffy.master bucket — AI orchestration minions

| Canonical ID (per ADR 0032 §5) | Persona name | Status today | Default capability |
|---|---|---|---|
| `mcp.workspace.clyffy-master-gateway` | Clyffy (the orchestrator) | planned | L1 federation gateway; routes every client call to the right specialist; carries workspace/project context as ambient state |
| `mcp.workspace.clyffy-master.authentik` | Auth-K | formal-mcp | Identity read/admin — OIDC clients, users, flows, stages, brands, providers |
| `mcp.workspace.clyffy-master.bifrost` | Bifrost | planned | LLM provider routing, minion handoff, policy-aware bridge |
| `mcp.workspace.clyffy-master.observatory` | Observer | planned | AI traces, usage, spend, latency, provider health, prompt quality |

### WardenClyffe.infra bucket — infrastructure control plane minions

| Canonical ID today | Canonical ID after Q-B rescope (deferred) | Persona name | Status today | Default capability |
|---|---|---|---|---|
| `mcp.global.warden` | `mcp.workspace.wardenclyffe-infra.warden` | Warden (the engine, separate from Warden Go) | planned | Formal Rust `rmcp` gateway/facade for Warden state and approved actions |
| `mcp.global.proxmox` | `mcp.workspace.wardenclyffe-infra.proxmox` | Dean (clyffy-dean) | mcp-shaped | Proxmox API read surface — nodes, LXCs, VMs, storage, cluster, replication, tasks |
| `mcp.global.dns` | `mcp.workspace.wardenclyffe-infra.dns` | DNS-warden | planned | Cloudflare + PowerDNS read/plan/apply with Warden approval gates |
| `mcp.global.opnsense` | `mcp.workspace.wardenclyffe-infra.opnsense` | Net-warden | planned | OPNsense firewall rules, aliases, WireGuard peers |
| `mcp.global.agent-runtime` | `mcp.workspace.wardenclyffe-infra.agent-runtime` | Stream-warden | planned | Devstation/capsule agent stream control — list, attach, stop, open, rotate token |
| `mcp.global.deploy` | `mcp.workspace.wardenclyffe-infra.deploy` | Deploy-warden | planned | Deploy pipeline per spec 07 — list templates, plan, apply, status, destroy |

In addition, a parallel L1 federation gateway exists for the
WardenClyffe.infra workspace: `mcp.workspace.wardenclyffe-infra-gateway`.
Status: planned. Default capability: L1 federation gateway for the
WardenClyffe.infra operator-facing minions; aggregates the six leaves
above (plus rykv) behind a single OAuth 2.1 + RFC 9728 front door.

### Global bucket — cross-cutting

| Canonical ID | Persona name | Status today | Default capability |
|---|---|---|---|
| `mcp.global.rykv` | RykV | external-existing | Zero-ms in-process key-value memory cache; get/put/search/list/inspect/invalidate |

That is ten specialists plus two L1 gateways (one per workspace).
The orchestrator doc references three more leaves the registry has not
yet declared (`mcp.global.qdrant`, `mcp.global.surreal`,
`mcp.global.postgres`, all expected to be `mcp.workspace.wardenclyffe-infra.*`
after Q-B); those are downstream-of-RRD MCPs whose shape depends on
Phase B/C of the hybrid intelligence layer northstar and are tracked
there, not here.

## Decision 4 — Default Capabilities And Seed Values Pattern

Each specialist has two layers of knowledge:

- **Default capabilities** — what every Authentik specialist knows on
  arrival, independent of project. Lives in version control as part of
  the specialist template. Updates land via PR.
- **Seed values** — what *this* Authentik specialist knows about *this*
  project after a project-attunement pass. Lives partly in version
  control (the seed file template, with deliberately-empty fields) and
  partly in the RRD (the projections generated by indexing the project's
  artifacts).

### The skeleton structure

Every specialist instantiates from a single skeleton at
`templates/clyffy-specialists/_skeleton/`. The skeleton has these files:

| File | Authored or generated | Owns |
|---|---|---|
| `manifest.yaml` | authored | Specialist identity, version, wrapper targets, dataset targets — same shape as the existing nested `specialist-capability-pack` template |
| `ROLE.md` | authored | Layman-readable description of what the specialist does and when to invoke it |
| `capabilities.yaml` | authored | Default tools the specialist exposes regardless of project |
| `seed.yaml` | authored, project-attuned at instantiation | Project-specific overrides, indexing hints, attunement targets |
| `policy.yaml` | authored | Auth, approval, secrets, tenancy, rate limits — derived from ADR 0030 baseline |
| `touchpoint.md` | authored | v2 `clyffy_touchpoint` frontmatter so the specialist is discoverable through the same touchpoint system everything else uses |
| `adapter-target.yaml` | authored | Per-client wrapper hints (Claude Desktop, Cursor, Codex, Gemini) — feeds the renderer in `docs/MCP_CLIENT_NORMALIZATION_SPEC.md` |

The skeleton lives at root (`templates/clyffy-specialists/_skeleton/`) so
it sits next to the product-level specialist roster, not buried in
`wardenclyffe/.agents/`. The deeper template at
`wardenclyffe/.agents/templates/specialist-capability-pack/` remains the
L2-leaf MCP-server contract template and is referenced from the skeleton's
`manifest.yaml` — the new root-level template is the *product* shell; the
nested template is the *MCP wire* shell.

### Project attunement mechanic

When a new specialist instance lands in a project (e.g. when the user
runs `wc-specialist attach authentik` in a project workspace), the
attunement pass does:

1. Read the project's `AGENTS.md`, `CLAUDE.md`, `package.json` /
   `Cargo.toml` / `pyproject.toml`, `docs/` index, and any
   `clyffy_touchpoint` frontmatter.
2. Index those into the RRD under the project's `workspace_id` and
   `project_key`.
3. Generate the specialist's `seed.yaml` overrides from the indexed
   projections — defaults the specialist will pull at every `tools/list`
   call.
4. Emit a Warden task `specialist.attune.<id>` recording the seed hash
   and indexing job id.

The specialist itself queries the RRD at runtime for project context; it
does not re-read the project files on every call. This is the "passive
intelligence" principle from `docs/ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md`
applied to specialist onboarding.

### Per-specialist seed shape

Every `seed.yaml` carries this shape, filled differently per namespace:

```yaml
schema_version: 1
specialist_id: mcp.workspace.clyffy-master.authentik
attuned_to:
  workspace_id: <workspace slug>
  project_key: <project slug>
  attuned_at: <iso-8601 timestamp>
  source_commit: <git hash>

# What the specialist should know about the project on first call.
# Indexing keys these into RRD projections.
project_context:
  description: <one-paragraph project summary, derived from the project AGENTS.md>
  primary_owners: [<name>, <name>]
  primary_runtimes: [<go|python|rust|node>, ...]
  primary_docs: [<repo-relative paths to high-value docs>]

# Capabilities the specialist exposes that should be visible by default.
# Subset or addition over manifest.yaml's full capability list.
visible_capabilities: [<cap-id>, <cap-id>, ...]

# Capabilities the specialist exposes but should hide by default for this
# project. Operator override.
hidden_capabilities: [<cap-id>, ...]

# Project-specific tuning that the specialist needs to behave correctly.
# Free-form per specialist; namespaced by capability id.
capability_overrides:
  <cap-id>:
    <key>: <value>

# RRD indexing hints — which projections the specialist queries.
indexing:
  qdrant_collections: [<collection name>, ...]
  surreal_graph_views: [<view name>, ...]
  refresh_policy: <on-source-change | scheduled | manual>
```

The defaults for each of the ten specialists live in
`templates/clyffy-specialists/<namespace>/seed.template.yaml` as the
unattuned starting point. The attunement pass replaces the placeholders.

## Decision 5 — Encoder, Embedder, Reranker Picks (May 2026)

### Principle first: it's the code and state, not the LLM

A transformer's *architecture* (encoder-only, decoder-only, encoder-
decoder) and its *fine-tuning objective* (contrastive, causal LM,
instruction-tuned, RLHF) determine what it is *good* at. The *serving
code* (which head you attach, which pooling you apply, whether you
normalize, whether you batch) and the *state* (prompt, conversation,
retrieval pool) determine what role it plays for a given call.

Concrete consequences:

- The same BERT-base weights can serve as a classifier (classification
  head), a token tagger (token-classification head), or an embedder
  (mean-pooling plus L2 norm). The model file is identical; the wiring
  differs.
- The Mistral-7B weight family is a generator by default, but NVIDIA's
  NV-Embed-v2 fine-tuned Mistral-7B with contrastive loss and a different
  head, and the result is one of the best embedders on the MTEB
  leaderboard. Same underlying transformer, different code+state.
- Asking "should we use the same LLM for embedding and generation"
  conflates two different roles. The answer is *yes you technically can*
  and *no you should not for production*. Reasoning below.

### What we choose for May 2026

| Role | Pick (CPU-feasible default) | Pick (GPU-upgrade target) | Why |
|---|---|---|---|
| Embedder | **BAAI/bge-m3** | NVIDIA **NV-Embed-v2** | bge-m3 emits dense+sparse+ColBERT vectors from one pass; multilingual; ~570M params CPU-runnable via TEI; MIT license. NV-Embed-v2 is the top-of-MTEB upgrade when a GPU lands (Mistral-7B base, ~7B params) |
| Reranker | **BAAI/bge-reranker-v2-m3** | NV-RerankQA-Mistral-4B | Same family as bge-m3; cross-encoder; pairs cleanly with dense+sparse retrieval. Add when measurement shows a rerank quality gap |
| Encoder (for non-retrieval embedding tasks, e.g. classification) | **BAAI/bge-m3** (same model, different head) | NV-Embed-v2 | We do not need a separate encoder; bge-m3 covers it |
| Generator | **Whatever Bifrost routes to** (Claude / GPT / Gemini / hosted Nemotron / etc.) | Same | Generation stays hosted-via-Bifrost; we do not run local generation until a GPU lands |
| In-DB inference (Phase E only) | Not yet | NV-Embed or BGE via SurrealDB function call | Contingent on GPU; out of scope until Phase D ratifies |

The default stack for Phase C of the RRD northstar is therefore:

```text
text in
  -> TEI sidecar serving bge-m3 (one LXC, CPU-bound, ~2-4 GiB RAM)
  -> dense + sparse + ColBERT vectors stored in SurrealDB
  -> retrieval = SurrealDB hybrid query
  -> rerank = bge-reranker-v2-m3 (add when needed)
  -> result with provenance to the caller
```

### Why we do not use the same model for embedding and generation

| Reason | Detail |
|---|---|
| Optimization target divergence | A model fine-tuned via contrastive InfoNCE (the embedder path) does not stay good at next-token causal prediction (the generator path) without significant joint training. Using one weight file for both means one role is worse than necessary |
| Memory pressure | A 7B model is ~14 GB in fp16. Running it as both embedder and generator keeps it hot for both call types; we cannot swap. Two small models (33M-570M embedder + Bifrost-hosted generator) use less total local memory |
| Latency budget | Embedding is bulk batch work at index time. Generation is interactive single-shot. Different SLOs benefit from different serving stacks (TEI for embed; vLLM/hosted for gen) |
| Lifecycle decoupling | Embedders are re-trained on slower cycles than generators. Coupling them locks one to the other's release cadence and turns every generator upgrade into a re-embed campaign |

### On Nemotron specifically

NVIDIA's Nemotron family is a generator line (Nemotron-4 340B, Nemotron-
Mini 4B, derivatives). NVIDIA's *embedding* line is separate and named
**NV-Embed** (NV-Embed-v2 currently leads on MTEB) and **NV-RerankQA**.
"Nemotron-Embed" is not a model NVIDIA has released as of January 2026; if
that changes mid-year we should re-evaluate.

Choosing the Nemotron family for generation does not imply using Nemotron
for embedding. Pick NV-Embed-v2 if we are committed to the NVIDIA stack
and a GPU is available. Otherwise bge-m3 wins on price-to-performance for
a CPU-bound homelab.

### On Harrison

I do not recognize a model called "Harrison" in the embedding/encoder
space as of January 2026. Possible interpretations the user may have
meant:

- **Harrison Chase** — founder of LangChain; not a model
- **Helix / H2O** — H2O.ai publishes open LLMs (Helix, H2O-Danube). They
  are generators, not embedders, as of cutoff
- **Hyena** — long-context state-space architecture; not productionized
  as an embedder we should pick today
- **Hugging Face / HF models in general** — could be a name conflation
- **GTE / Jina v3** — sometimes co-mentioned with "harrison" in tutorial
  content because Harrison Chase has video tutorials that use them

Best-fit closest matches if the user was thinking of a recent embedder
that warranted consideration:

- **jina-embeddings-v3** (Jina AI) — long context, multilingual, May 2024
  release; competitive with bge-m3 on retrieval benchmarks
- **mxbai-embed-large-v1** (Mixedbread AI) — strong English embedder,
  Apache-2.0
- **gte-Qwen2-7B-instruct** — top-of-MTEB but GPU-only

Flag for the user: please clarify which "Harrison" you meant; this doc
holds bge-m3 as the May 2026 default until that is resolved.

### Sparse, hybrid, and rerank shape

bge-m3 is unusual in that one forward pass emits three vector kinds:

- **Dense** — single embedding vector, the standard semantic search input.
- **Sparse** — token-id-weighted sparse vector, BM25-like but learned;
  better than BM25 on out-of-vocabulary terms.
- **ColBERT** — late-interaction multi-vector, very strong precision but
  expensive to store.

For Phase B–C of the RRD northstar:

- Always store dense in SurrealDB (the warm-tier retrieval surface).
- Store sparse alongside dense; SurrealDB's FTS handles BM25-style as a
  fallback if sparse is not yet wired.
- Store ColBERT vectors only for high-precision corpora (decisions, ADRs,
  approvals). Skip ColBERT on bulk doc/code corpora until corpus growth
  forces the issue.

## Implementation Notes For The Renderer

The MCP client renderer (`docs/MCP_CLIENT_NORMALIZATION_SPEC.md` §"Renderer")
must:

- Read entries by `id:` and emit configs keyed by `slug:` plus the
  workspace prefix on collision (per ADR 0032 §3).
- Surface the project bucket (clyffy-master / wardenclyffe-infra / global)
  as a render-time grouping label so client configs can render comments
  or sections grouped by bucket. Cosmetic, but matters for operator
  comprehension.

## Open Questions Flagged For Operator Decision

The audit document
[CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md](CLYFFY_TWO_GATEWAY_READINESS_AUDIT.md)
§8 carries the **canonical** open-decision list for the gateway
instantiation work (Q-A through Q-F). The questions below are
specifically for this document's scope (encoder/embedder picks +
specialist-bucket nuances) and do not overlap.

| # | Question | Recommendation, awaiting confirmation |
|---|---|---|
| Q1 | "Harrison" — which model is this? | Clarify or accept bge-m3 default |
| Q2 | When does a GPU land on server1 or server2? | Plan as if not until Q3 2026; revisit if hardware budget changes |
| Q3 | Does Bifrost route generation calls through hosted Nemotron when available, or stay on Claude/GPT? | Bifrost policy decision; not blocking |
| Q4 | Do we want the customer Clyffe Code surface to expose specialists from the `mcp.workspace.clyffy-master.*` bucket at all, or only WardenClyffe-derived customer-safe projections? | Clyffe Code spec says customer-safe outcomes only — keep `mcp.workspace.clyffy-master.*` operator-side. Confirm |
| Q5 | Do we deprecate `mcp.global.*` entirely once `rykv` is the only entry, or keep the bucket for future cross-cutting utilities? | Keep — future could add e.g. observability fan-out |

## Don't Do

- Do not duplicate the `mcp-mesh-server` template content (renamed to
  `.agents/templates/mcp/l2-leaf-server/` in alignment runbook Phase 2)
  into the root-level skeleton. The root skeleton is the *product* shell;
  the nested template is the *MCP wire* shell. They reference each other.
- Do not let a specialist's `seed.yaml` accumulate raw documents,
  transcripts, or credentials. Seed values are *hints and overrides*;
  indexed content lives in the RRD.
- Do not assume the embedder choice is reversible without cost.
  Re-embedding the entire corpus is hours of compute and a window of
  reduced retrieval quality. Choose deliberately and version every
  vector with the encoder fingerprint.
- Do not call any specialist's `tools/list` "Clyffy's intelligence" until
  the RRD attunement is real. Today it is a stub returning seed values;
  honest naming matters.

## References

Inherits everything cited in `docs/MCP_CLIENT_NORMALIZATION_SPEC.md` and
`docs/ai/HYBRID_INTELLIGENCE_LAYER_NORTHSTAR.md`, plus:

- BAAI/bge-m3 model card: https://huggingface.co/BAAI/bge-m3
- BAAI/bge-reranker-v2-m3: https://huggingface.co/BAAI/bge-reranker-v2-m3
- NVIDIA NV-Embed-v2: https://huggingface.co/nvidia/NV-Embed-v2
- NVIDIA NV-RerankQA-Mistral-4B: https://huggingface.co/nvidia/nv-rerankqa-mistral-4b-v3
- jina-embeddings-v3: https://huggingface.co/jinaai/jina-embeddings-v3
- mxbai-embed-large-v1: https://huggingface.co/mixedbread-ai/mxbai-embed-large-v1
- MTEB leaderboard: https://huggingface.co/spaces/mteb/leaderboard
- Hugging Face Text Embeddings Inference: https://github.com/huggingface/text-embeddings-inference
- ColBERT late interaction: https://github.com/stanford-futuredata/ColBERT
- NV-Embed paper (decoder-as-embedder methodology): https://arxiv.org/abs/2405.17428
