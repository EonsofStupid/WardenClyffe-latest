#!/usr/bin/env python3
"""Manage a parking-lot agenda: the ordered decision items for a session.

Stage 1 + Stage 2/3 ledger for docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md. Each
agenda is a v2 clyffy_touchpoint at docs/ai/parking-lot/agenda/<project_key>.md.
This is the deterministic state ledger the deliberation loop reads and mutates;
the agent never edits item state by hand.

Dependency-free, mirroring scripts/foundation/parking-lot-capture.py. Does NOT
write to Qdrant or SurrealDB; the sync worker projects the touchpoint.

Subcommands:
  init   --focus --boundary                 create the agenda for a boundary
  add    --agenda --question --boundary      append an `open` decision item
  round  --agenda --item                     +1 deliberation round (cap 12)
  lock   --agenda --item                      mark an item `locked`
  park   --agenda --item --reason            defer an item with a reason
  status --agenda                            print progress (N/M locked, K parked)
"""

from __future__ import annotations

import argparse
import datetime as _dt
import re
import sys
from pathlib import Path

DEFAULT_WORKSPACE = "wardenclyffe.intelligence"
AGENDA_DIR = Path("docs/ai/parking-lot/agenda")
SOURCE_SKILL = "docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md"
MESH_REGISTRY = "wardenclyffe/registry/context-mesh.yaml"
ROUND_CAP = 12

START = "<!-- agenda:start -->"
END = "<!-- agenda:end -->"
ITEM_HEAD = re.compile(r"^- \[(\d+)\] (\w+) rounds=(\d+)$")


def slug(text: str) -> str:
    return re.sub(r"[^a-z0-9]+", "-", text.lower()).strip("-") or "x"


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


def identify_domain(root: Path, boundary: str) -> tuple[str, str | None, str]:
    rel = (root / boundary).resolve()
    module = None
    status = "existing" if rel.exists() else "new"
    for parent in [rel, *rel.parents]:
        if root not in parent.parents and parent != root:
            continue
        if re.match(r"module-\d+-[a-z0-9-]+", parent.name):
            module = parent.name
            break
    probe = rel if rel.is_dir() else rel.parent
    workspace = None
    while True:
        ws = read_frontmatter_scalar(probe / "AGENTS.md", "workspace_id")
        if ws:
            workspace = ws
            break
        if probe == root or root not in probe.parents:
            break
        probe = probe.parent
    return workspace or DEFAULT_WORKSPACE, module, status


def project_key_for(boundary: str, workspace: str) -> str:
    if boundary in (".", ""):
        return slug(workspace)
    return slug(boundary.replace("/", "-"))


def build_frontmatter(meta: dict) -> str:
    module = meta["module"] or "null"
    return (
        "---\n"
        "clyffy_touchpoint:\n"
        "  version: 2\n"
        f"  workspace_id: {meta['workspace_id']}\n"
        f"  project_key: {meta['project_key']}\n"
        "  persona: clyffy-operator\n"
        "  kind: agenda\n"
        f"  owner: {meta['owner']}\n"
        f"  module: {module}\n"
        f"  focus_feature: \"{meta['focus']}\"\n"
        f"  boundary: {meta['boundary']}\n"
        f"  domain_status: {meta['domain_status']}\n"
        f"  source_skill: {SOURCE_SKILL}\n"
        f"  mesh_registry: {MESH_REGISTRY}\n"
        "  sync:\n"
        "    qdrant: true\n"
        "    surreal: true\n"
        "---\n"
    )


def fmt_item(item: dict) -> str:
    base = f"- [{item['id']}] {item['state']} rounds={item['rounds']}"
    parts = [base, f"q={item['q']}", f"boundary={item['boundary']}"]
    if item.get("reason"):
        parts.append(f"reason={item['reason']}")
    return " || ".join(parts)


def parse_items(inner: str) -> list[dict]:
    items: list[dict] = []
    for raw in inner.splitlines():
        line = raw.strip()
        if not line.startswith("- ["):
            continue
        parts = [p.strip() for p in line.split(" || ")]
        m = ITEM_HEAD.match(parts[0])
        if not m:
            continue
        item = {
            "id": int(m.group(1)),
            "state": m.group(2),
            "rounds": int(m.group(3)),
            "q": "",
            "boundary": "",
            "reason": "",
        }
        for kv in parts[1:]:
            if "=" in kv:
                k, v = kv.split("=", 1)
                if k in item:
                    item[k] = v
        items.append(item)
    return items


def agenda_path(root: Path, project_key: str) -> Path:
    return root / AGENDA_DIR / f"{project_key}.md"


def load(root: Path, project_key: str) -> tuple[str, list[dict], str]:
    """Return (pre, items, post) where the items block sits between markers."""
    path = agenda_path(root, project_key)
    if not path.exists():
        sys.exit(f"error: agenda '{project_key}' not found at {path.as_posix()}")
    text = path.read_text(encoding="utf-8")
    if START in text and END in text:
        pre, mid = text.split(START, 1)
        inner, post = mid.split(END, 1)
    else:
        pre, inner, post = text, "", "\n"
    return pre, parse_items(inner), post


def write(root: Path, project_key: str, pre: str, items: list[dict], post: str) -> Path:
    path = agenda_path(root, project_key)
    body = "\n".join(fmt_item(i) for i in items)
    path.write_text(f"{pre}{START}\n{body}\n{END}{post}", encoding="utf-8")
    return path


def find_item(items: list[dict], item_id: int) -> dict:
    for it in items:
        if it["id"] == item_id:
            return it
    sys.exit(f"error: item {item_id} not in agenda")


def print_status(project_key: str, items: list[dict]) -> None:
    locked = sum(1 for i in items if i["state"] == "locked")
    parked = sum(1 for i in items if i["state"] == "parked")
    total = len(items)
    print(f"agenda {project_key}: {locked}/{total} locked, {parked} parked")
    for it in items:
        tail = f" (reason: {it['reason']})" if it.get("reason") else ""
        print(f"  [{it['id']}] {it['state']:<6} r{it['rounds']:<2} {it['q']}{tail}")


def main() -> int:
    ap = argparse.ArgumentParser(description=__doc__)
    sub = ap.add_subparsers(dest="cmd", required=True)

    p_init = sub.add_parser("init")
    p_init.add_argument("--focus", default="Building WardenClyffe")
    p_init.add_argument("--boundary", required=True)

    p_add = sub.add_parser("add")
    p_add.add_argument("--agenda", required=True)
    p_add.add_argument("--question", required=True)
    p_add.add_argument("--boundary", required=True)

    for name in ("round", "lock"):
        p = sub.add_parser(name)
        p.add_argument("--agenda", required=True)
        p.add_argument("--item", required=True, type=int)

    p_park = sub.add_parser("park")
    p_park.add_argument("--agenda", required=True)
    p_park.add_argument("--item", required=True, type=int)
    p_park.add_argument("--reason", required=True)

    p_status = sub.add_parser("status")
    p_status.add_argument("--agenda", required=True)

    ap.add_argument("--root", default=None)
    args = ap.parse_args()
    root = Path(args.root).resolve() if args.root else find_repo_root(Path.cwd())

    if args.cmd == "init":
        workspace, module, status = identify_domain(root, args.boundary)
        project_key = project_key_for(args.boundary, workspace)
        out_dir = root / AGENDA_DIR
        out_dir.mkdir(parents=True, exist_ok=True)
        path = agenda_path(root, project_key)
        owner = f"{AGENDA_DIR.as_posix()}/{project_key}.md"
        meta = {
            "workspace_id": workspace, "project_key": project_key, "owner": owner,
            "module": module, "focus": args.focus, "boundary": args.boundary,
            "domain_status": status,
        }
        if path.exists():
            print(f"agenda '{project_key}' already exists -> {owner}")
            return 0
        stamp = _dt.date.today().isoformat()
        header = (
            f"\n# Parking-Lot Agenda — {project_key}\n\n"
            f"Focus: **{args.focus}**. Boundary: `{args.boundary}`. Opened {stamp}.\n\n"
            "Ordered decision items. State ∈ open|locked|parked; `rounds` capped "
            f"at {ROUND_CAP}. Managed by `scripts/foundation/parking-lot-agenda.py` "
            "per `docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md`. Decisions are captured "
            "by `parking-lot-capture.py`; the spec is emitted by `parking-lot-spec.py`.\n\n"
        )
        path.write_text(build_frontmatter(meta) + header + f"{START}\n{END}\n", encoding="utf-8")
        print(f"created agenda '{project_key}' -> {owner} (workspace_id={workspace})")
        if status == "new":
            print("  NOTE: new domain — run boundary-guard before creating its folder.")
        return 0

    pre, items, post = load(root, args.agenda)

    if args.cmd == "add":
        next_id = (max((i["id"] for i in items), default=0)) + 1
        items.append({
            "id": next_id, "state": "open", "rounds": 0,
            "q": args.question, "boundary": args.boundary, "reason": "",
        })
        write(root, args.agenda, pre, items, post)
        print(f"added item [{next_id}] to agenda '{args.agenda}': {args.question}")
        return 0

    if args.cmd == "round":
        it = find_item(items, args.item)
        if it["state"] != "open":
            print(f"item [{it['id']}] is {it['state']} — no more rounds")
            return 0
        if it["rounds"] >= ROUND_CAP:
            print(f"item [{it['id']}] at round cap {ROUND_CAP} — ESCALATE: "
                  "operator force-decides (lock) or park it. Do not loop.")
            return 0
        it["rounds"] += 1
        write(root, args.agenda, pre, items, post)
        left = ROUND_CAP - it["rounds"]
        note = "  -> at cap next: ESCALATE (lock or park)" if left == 0 else ""
        print(f"item [{it['id']}] round {it['rounds']}/{ROUND_CAP} ({left} left){note}")
        return 0

    if args.cmd == "lock":
        it = find_item(items, args.item)
        it["state"] = "locked"
        write(root, args.agenda, pre, items, post)
        print(f"locked item [{it['id']}] after {it['rounds']} round(s): {it['q']}")
        print("  remember: capture the decision with parking-lot-capture.py")
        print_status(args.agenda, items)
        return 0

    if args.cmd == "park":
        it = find_item(items, args.item)
        it["state"] = "parked"
        it["reason"] = args.reason.replace(" || ", " / ")
        write(root, args.agenda, pre, items, post)
        print(f"parked item [{it['id']}]: {args.reason}")
        print_status(args.agenda, items)
        return 0

    if args.cmd == "status":
        print_status(args.agenda, items)
        return 0

    return 0


if __name__ == "__main__":
    sys.exit(main())
