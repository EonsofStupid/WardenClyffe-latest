---
wardenclyffe_touchpoint:
  version: 1
  kind: ai-runtime-workspace
  namespace: wardenclyffe.huggingface
  owner: docs/ai/HUGGINGFACE_WORKSPACE_TOUCHPOINT.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  agents:
    - codex
    - claude
    - cursor
  reads:
    - AGENTS.md
    - docs/ai/WARDENCLYFFE_BASE_SKILL.md
    - docs/ai/INTELLIGENCE_TOUCHPOINTS.md
---

# Hugging Face Workspace Touchpoint

This file routes agents that need Hugging Face context for WardenClyffe,
Clyffy, embeddings, evaluation, Spaces, Jobs, and model or dataset repos.

## Current Role

Hugging Face is a working external AI runtime and artifact plane, not the
Warden infrastructure source of truth.

Use it for:

- hosted demos and early Clyffy AI workspaces,
- short-lived Jobs for batch experiments and inventory checks,
- model and dataset artifacts that are safe to store on the Hub,
- future fallback embedders or evaluators when local Harrier/TEI is unavailable.

Do not use it for:

- Proxmox control,
- Warden product truth,
- raw secrets,
- customer-private data without an explicit data-classification decision.

## Observed State

Codex connector inventory on 2026-05-21:

- Authenticated Hugging Face user: `justsayit`.
- Running Hugging Face Jobs: none found.
- Visible model repos by `justsayit`: `justsayit/wiredFRONT` (private).
- Visible datasets by `justsayit`: none found.
- Clyffy/WardenClyffe-named Spaces: none found by connector search.

Local CLI posture on 2026-05-21:

- `hf` exists at `C:\Users\jessa\.conda\envs\llm-tuning\Scripts\hf.exe`.
- Local CLI auth was not visible to this Codex process.
- The installed CLI appears older than the current `hf` command surface and
  lacks some newer groups such as `hf spaces` and `hf endpoints`.

## Target Workspace Shape

Use a small set of predictable Hub repositories:

| Hub repo | Type | Purpose |
|---|---|---|
| `justsayit/clyffy-ai-lab` | Space | Operator-facing Gradio or lightweight demo surface. |
| `justsayit/clyffy-rro-lab` | Space | Reason-ready object pipeline demo and inspection UI. |
| `justsayit/wardenclyffe-evals` | Dataset | Prompt/eval cases, redacted traces, golden outputs. |
| `justsayit/clyffy-kb-seed` | Dataset | Sanitized knowledge-base seed corpus. |
| `justsayit/clyffy-embedder-bakeoff` | Dataset | Embedding/reranking benchmark inputs and results. |
| `justsayit/clyffy-runtime-notebooks` | Model or Space | Runtime experiments, only if artifacts are model-shaped. |

Keep names boring and searchable. Add private repos first; open-source only
after a separate review.

## Agent Rules

- Prefer the Codex Hugging Face connector when it is authenticated.
- Prefer the `hf` CLI only when local auth is visible or `HF_TOKEN` is provided
  through a secret manager.
- Never print tokens, repo secrets, private dataset rows, or customer data.
- Use Hugging Face Jobs for disposable compute, not long-running Warden control.
- Store durable Warden/Clyffe decisions in repo docs or SurrealDB governance
  tables; Hugging Face stores artifacts, demos, and experiments.
- For Jobs that need Hub write access, pass `HF_TOKEN` as a secret, not as a
  plain environment variable.

## First Establishment Steps

1. Confirm the `justsayit` namespace is the intended WardenClyffe HF namespace.
2. Upgrade or refresh local `hf` CLI auth so scripts can inventory private repos.
3. Create private repos for the target workspace shape.
4. Add a small Gradio Space for `clyffy-ai-lab`.
5. Add a dataset repo for `wardenclyffe-evals`.
6. Wire Warden/Clyffe preflight to report Hugging Face workspace status without
   requiring it for Proxmox work.

## Verification

From this repo root:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/huggingface-workspace-inventory.ps1 -Author justsayit
```

That script reports metadata only. It must not print token values.

To create the initial private workspace repos after local Hugging Face auth is
available:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/setup-huggingface-cli-auth.ps1
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/huggingface-bootstrap-workspaces.ps1 -Author justsayit
powershell -NoProfile -ExecutionPolicy Bypass -File scripts/huggingface-bootstrap-workspaces.ps1 -Author justsayit -Apply
```

The first command prompts locally for a Hugging Face token and stores it through
the Hugging Face SDK. The second command is a dry run. The third command creates
private repos with `hf repo create --private --exist-ok`.
