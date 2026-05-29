---
# CLYFFY TOUCHPOINT v2 — intelligence-layer routing (ADR 0033)
clyffy_touchpoint:
  version: 2
  workspace_id: clyffy.master                    # inherits from Master-Clyffy/AGENTS.md
  workspace_uuid: null
  project_key: clyffy-huggingface                # narrower scoping than root
  persona: clyffy-operator
  kind: ai-runtime-workspace
  owner: Master-Clyffy/docs/ai/HUGGINGFACE_WORKSPACE_TOUCHPOINT.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml

  agents:
    - codex
    - claude
    - cursor

  reads:
    - Master-Clyffy/AGENTS.md
    - Master-Clyffy/docs/CLYFFY_MCP_ORCHESTRATOR.md
    - ../wardenclyffe/docs/ai/INTELLIGENCE_TOUCHPOINTS.md

  audit:
    event_prefix: huggingface
    enabled: true

  scopes:
    - clyffy:operate

  observability:
    semconv_version: "1.40.0"
    trace_context_via_meta: true

  intel_hook:
    capture_chats: true
---

# Hugging Face Workspace Touchpoint — Master Clyffy

Routes agents that need Hugging Face context for the Master Clyffy
ecosystem: embeddings, evaluations, Spaces, Jobs, and model / dataset
repos.

> **Supersedes** the v1 touchpoint at
> `../wardenclyffe/docs/ai/HUGGINGFACE_WORKSPACE_TOUCHPOINT.md` per
> operator direction 2026-05-29: _"Hugging Face is supposed to be properly
> configured first for Clyffy and then WardenClyffe."_ The v1 file is
> retained until Phase 8 (Task #10) of the v1 → v2 migration closes; its
> substance is fully captured here.

---

## Current role

Hugging Face is a working external AI runtime and artifact plane. It is
**not** the WardenClyffe infrastructure source of truth.

**Use HF for:**

- hosted demos and Clyffy AI workspaces
- short-lived Jobs for batch experiments and inventory checks
- model and dataset artifacts that are safe to store on the Hub
- fallback embedders or evaluators when local Harrier/TEI is unavailable

**Do not use HF for:**

- Proxmox control
- Warden product truth
- raw secrets (token, cookies, keys)
- customer-private data without an explicit data-classification decision

---

## Target workspace shape — 6 private repos under `justsayit/`

Authoritative plan, locked 2026-05-29 (Sub-batch C, operator-approved):

| Hub repo | Type | SDK | Purpose |
|---|---|---|---|
| `justsayit/clyffy-ai-lab` | Space | gradio | Operator-facing demo surface for Clyffy AI features |
| `justsayit/clyffy-rro-lab` | Space | gradio | Reason-ready object pipeline demo + inspection UI |
| `justsayit/wardenclyffe-evals` | Dataset | — | Prompt/eval cases, redacted traces, golden outputs |
| `justsayit/clyffy-kb-seed` | Dataset | — | Sanitized knowledge-base seed corpus |
| `justsayit/clyffy-embedder-bakeoff` | Dataset | — | Embedding / reranking benchmark inputs + results |
| `justsayit/clyffy-runtime-notebooks` | Space (default) → Model when ready | gradio | Runtime experiments; switch repo-type to Model when artifacts are model-shaped |

All repos: **private** by default, created with `hf repo create --private --exist-ok`.
Open-source promotion only after a separate data-classification review.
Names kept boring and searchable.

The earlier 3-workspace plan (`clyffy-master`, `wardenclyffe-clyffy`,
`wardenclyffe-evals`) from prior bootstrap script versions is **superseded**.
Only `wardenclyffe-evals` carries forward to the 6-repo plan.

---

## Operational state (2026-05-29)

- **Authenticated HF user:** `justsayit` — token last verified successful
  on 2026-05-21 18:06:25 (per setup-huggingface-cli-auth log, token
  `hf_****zomO` masked, profile `clyffy`)
- **Workspaces NOT YET CREATED.** All prior `huggingface-bootstrap-workspaces.ps1`
  runs were `apply=False` (dry-run). The 6-repo plan above must be created
  with `-Apply` per the establishment steps below.
- **`hf` CLI present** at `C:\Users\jessa\AppData\Roaming\Python\Python313\Scripts\hf.exe`
  per latest auth log.

---

## Agent rules

- Prefer the **Codex HuggingFace connector** when authenticated.
- Use the `hf` **CLI only** when local auth is visible or `HF_TOKEN` is
  injected from a secret manager.
- **Never print** tokens, repo secrets, private dataset rows, or customer
  data. Touchpoint validator + intel-hook redaction must catch any leak.
- Use **HF Jobs** for disposable compute only, not long-running Warden
  control or anything that must survive a process restart.
- Store durable decisions in repo docs or SurrealDB governance tables.
  HF stores **artifacts, demos, experiments** — never product truth.
- For Jobs that need Hub write access, pass `HF_TOKEN` as a Job **secret**,
  not as a plain environment variable in code.

---

## First establishment steps — the actual run path

From the repo root (Master-Clyffy/):

```powershell
# 1. Inventory existing repos (no token write; lists current state)
powershell -NoProfile -ExecutionPolicy Bypass `
  -File scripts/huggingface-workspace-inventory.ps1 -Author justsayit
```

```powershell
# 2. Refresh / set CLI auth (interactive — token prompted, never logged)
powershell -NoProfile -ExecutionPolicy Bypass `
  -File scripts/setup-huggingface-cli-auth.ps1
```

```powershell
# 3. Dry-run the bootstrap to see exactly what commands will run
powershell -NoProfile -ExecutionPolicy Bypass `
  -File scripts/huggingface-bootstrap-workspaces.ps1 -Author justsayit
```

```powershell
# 4. Apply — actually creates the 6 private repos
powershell -NoProfile -ExecutionPolicy Bypass `
  -File scripts/huggingface-bootstrap-workspaces.ps1 -Author justsayit -Apply
```

```powershell
# 5. Verify each repo now exists
powershell -NoProfile -ExecutionPolicy Bypass `
  -File scripts/huggingface-workspace-inventory.ps1 -Author justsayit
```

Scripts log to `Master-Clyffy/logs/huggingface/` (gitignored — runtime
state, not source of truth).

> **Pending Sub-batch C2:** the `huggingface-bootstrap-workspaces.ps1` script
> still encodes the older 3-repo plan in its `New-WorkspaceSpec` function.
> Sub-batch C2 rewrites that function to the 6-repo plan above before
> step 3 can produce the right dry-run output. Until C2 ships, step 3
> output will not match this touchpoint — that's the expected gap.

---

## Verification

After step 5 you should see, under `justsayit/`:

- 3 private Spaces (gradio): `clyffy-ai-lab`, `clyffy-rro-lab`,
  `clyffy-runtime-notebooks`
- 3 private Datasets: `wardenclyffe-evals`, `clyffy-kb-seed`,
  `clyffy-embedder-bakeoff`

The `huggingface-workspace-inventory.ps1` script reports each repo's
visibility, last-modified, and (for Spaces) SDK. The script reports
metadata only and must never print token values.

---

## References

- `../AGENTS.md` — Master Clyffy root cascade
- `../docs/CLYFFY_MCP_ORCHESTRATOR.md` — orchestrator that will consume
  these HF workspaces for embedding / eval / KB-seed pipelines
- `../../wardenclyffe/docs/decisions/0033-touchpoint-protocol.md` — v2 frontmatter spec
- `../../wardenclyffe/docs/decisions/0030-mcp-2026-baseline.md` — observability fields
- `../../wardenclyffe/docs/decisions/0031-workspace-identity.md` — workspace_id grammar
- `../../wardenclyffe/docs/ai/INTELLIGENCE_TOUCHPOINTS.md` — sibling intelligence-layer touchpoints
