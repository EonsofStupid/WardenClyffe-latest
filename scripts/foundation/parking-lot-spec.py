#!/usr/bin/env python3
"""Emit the granular build spec from a parking-lot agenda + its decisions.

Stage 4 of docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md. Compiles the agenda items
(docs/ai/parking-lot/agenda/<key>.md) and the captured decisions
(docs/ai/parking-lot/decisions/<key>.md) into a scaffolded build spec at
docs/ai/parking-lot/specs/<key>.spec.md carrying the Build Spec Contract
sections. The script guarantees the skeleton, the v2 frontmatter, and that no
locked decision is dropped; the agent fills the prose.

Dependency-free, mirroring scripts/foundation/parking-lot-capture.py.

Subcommand:
  emit --agenda <project_key>
"""

from __future__ import annotations

import argparse
import datetime as _dt
import re
import sys
from pathlib import Path

DEFAULT_WORKSPACE = "wardenclyffe.intelligence"
AGENDA_DIR = Path("docs/ai/parking-lot/agenda")
DECISIONS_DIR = Path("docs/ai/parking-lot/decisions")
SPECS_DIR = Path("docs/ai/parking-lot/specs")
SOURCE_SKILL = "docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md"
MESH_REGISTRY = "wardenclyffe/registry/context-mesh.yaml"
ITEM_HEAD = re.compile(r"^- \[(\d+)\] (\w+) rounds=(\d+)$")
TODO = "_TODO — fill from the deliberation; do not leave blank._"


def find_repo_root(start: Path) -> Path:
    cur = start.resolve()
    for parent in [cur, *cur.parents]:
        if (parent / "AGENTS.md").exists() and (parent / "docs").is_dir():
            return parent
    return Path.cwd()


def read_frontmatter_scalar(path: Path, name: str) -> str | None:
    try:
        text = path.read_text(encoding="utf-8", errors="replace")
    except OSError:
        return None
    if not text.startswith("---"):
        return None
    fm = text.split("---", 2)[1] if text.count("---") >= 2 else ""
    m = re.search(rf"(?m)^\s*{re.escape(name)}:\s*(.+?)\s*$", fm)
    if not m:
        return None
    value = m.group(1).strip().strip('"')
    return None if value in {"null", "None", ""} else value


def parse_items(path: Path) -> list[dict]:
    if not path.exists():
        sys.exit(f"error: agenda not found at {path.as_posix()}")
    text = path.read_text(encoding="utf-8")
    items: list[dict] = []
    for raw in text.splitlines():
        line = raw.strip()
        if not line.startswith("- ["):
            continue
        parts = [p.strip() for p in line.split(" || ")]
        m = ITEM_HEAD.match(parts[0])
        if not m:
            continue
        item = {"id": int(m.group(1)), "state": m.group(2), "rounds": int(m.group(3)),
                "q": "", "boundary": "", "reason": ""}
        for kv in parts[1:]:
            if "=" in kv:
                k, v = kv.split("=", 1)
                if k in item:
                    item[k] = v
        items.append(item)
    return items


def parse_decisions(path: Path) -> list[str]:
    if not path.exists():
        return []
    text = path.read_text(encoding="utf-8")
    return [ln.strip() for ln in text.splitlines() if ln.strip().startswith("- **[")]


def build_frontmatter(meta: dict) -> str:
    module = meta["module"] or "null"
    return (
        "---\n"
        "clyffy_touchpoint:\n"
        "  version: 2\n"
        f"  workspace_id: {meta['workspace_id']}\n"
        f"  project_key: {meta['project_key']}\n"
        "  persona: clyffy-operator\n"
        "  kind: buildspec\n"
        f"  owner: {meta['owner']}\n"
        f"  module: {module}\n"
        f"  focus_feature: \"{meta['focus']}\"\n"
        f"  boundary: {meta['boundary']}\n"
        f"  source_skill: {SOURCE_SKILL}\n"
        f"  mesh_registry: {MESH_REGISTRY}\n"
        "  sync:\n"
        "    qdrant: true\n"
        "    surreal: true\n"
        "---\n"
    )


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    sub = ap.add_subparsers(dest="cmd", required=True)
    p_emit = sub.add_parser("emit")
    p_emit.add_argument("--agenda", required=True, help="project_key of the agenda")
    ap.add_argument("--root", default=None)
    args = ap.parse_args()
    root = Path(args.root).resolve() if args.root else find_repo_root(Path.cwd())
    key = args.agenda

    agenda_path = root / AGENDA_DIR / f"{key}.md"
    items = parse_items(agenda_path)
    decisions = parse_decisions(root / DECISIONS_DIR / f"{key}.md")

    open_items = [i for i in items if i["state"] == "open"]
    if open_items:
        ids = ", ".join(str(i["id"]) for i in open_items)
        print(f"WARNING: {len(open_items)} item(s) still open ([{ids}]). "
              "Lock or park them before the spec is build-ready.")

    workspace = read_frontmatter_scalar(agenda_path, "workspace_id") or DEFAULT_WORKSPACE
    focus = read_frontmatter_scalar(agenda_path, "focus_feature") or "Building WardenClyffe"
    boundary = read_frontmatter_scalar(agenda_path, "boundary") or "."
    module = read_frontmatter_scalar(agenda_path, "module")

    locked = [i for i in items if i["state"] == "locked"]
    parked = [i for i in items if i["state"] == "parked"]
    stamp = _dt.date.today().isoformat()

    def numbered(prefix: str, rows: list[dict]) -> str:
        return "\n".join(f"- **{prefix}{i['id']}** (`{i['boundary']}`): {i['q']}" for i in rows) or "_none_"

    out_dir = root / SPECS_DIR
    out_dir.mkdir(parents=True, exist_ok=True)
    out_path = out_dir / f"{key}.spec.md"
    owner = f"{SPECS_DIR.as_posix()}/{key}.spec.md"
    meta = {"workspace_id": workspace, "project_key": key, "owner": owner,
            "module": module, "focus": focus, "boundary": boundary}

    decisions_block = "\n".join(decisions) if decisions else "_no captured decisions yet — run parking-lot-capture.py per lock_"
    parked_block = "\n".join(
        f"- **D{i['id']}** (`{i['boundary']}`): {i['q']} — _parked: {i['reason']}_" for i in parked
    ) or "_none_"

    body = f"""
# Build Spec — {key}

Focus: **{focus}**. Boundary: `{boundary}`. Emitted {stamp} from agenda
`{AGENDA_DIR.as_posix()}/{key}.md` ({len(locked)} locked, {len(parked)} parked).
Scaffolded by `scripts/foundation/parking-lot-spec.py`; fill each section from the
deliberation. Contract: `docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md` §Build Spec Contract.

## 1. Overview
{TODO}

**Non-goals (deliberate, until a later trigger):** {TODO}

## 2. Golden patterns
Obeys: `docs/WARDENCLYFFE_NAMING_CONVENTIONS.md`,
`docs/HYPERMODULAR_DDD_FOLDER_STRUCTURE.md`,
`docs/ai/WARDENCLYFFE_STRUCTURE_STANDARD.md`. {TODO}

## 3. Locked decisions
{decisions_block}

Agenda items locked:
{numbered("D", locked)}

## 4. Minimal requirements (10x foresight)
Per item: the smallest thing that ships *complete*, plus what to configure now
because it is 10x costlier to retrofit.
{numbered("D", locked)}

{TODO}

## 5. Established patterns / formulas
{TODO}

## 6. File-creation guidance (notes vs. code)
Which deliverables are **touchpoints/Markdown** (routing, decisions, runbooks)
and which are **code** (boundary, language, colocated companions). What must
never be created (defer duplicates to boundary-guard).
{TODO}

## 7. LLM dynamic-flow within control-layer boundaries
How Clyffy and the coding agents operate against this spec at runtime: the
designation rules (connector vs plugin, ai-identity), what they may read, and
what must never enter an agent's context (the brokered secret boundary).
{TODO}

## 8. Verify
Per locked item, the explicit check that proves it done.
{numbered("D", locked)}

{TODO}

## Parked / deferred
{parked_block}
"""

    out_path.write_text(build_frontmatter(meta) + body, encoding="utf-8")
    print(f"emitted build spec -> {owner}")
    print(f"  {len(locked)} locked, {len(parked)} parked, {len(open_items)} open")
    print("  next: fill the TODO sections, then validate-touchpoints.py --json")
    return 0


if __name__ == "__main__":
    sys.exit(main())
