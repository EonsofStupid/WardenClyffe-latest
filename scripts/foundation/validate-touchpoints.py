#!/usr/bin/env python3
"""Validate and inventory WardenClyffe Markdown touchpoints.

This is intentionally dependency-free. It checks enough frontmatter shape to
support Warden UI status and future Qdrant/SurrealDB sync jobs without turning
Markdown into a second database.
"""

from __future__ import annotations

import argparse
import json
import os
import re
import sys
from dataclasses import dataclass, asdict
from pathlib import Path
from typing import Iterable


SKIP_DIRS = {
    ".git",
    ".idea",
    ".vscode",
    "node_modules",
    "target",
    "dist",
    "build",
    ".next",
    ".turbo",
}


@dataclass
class Touchpoint:
    path: str
    version: int | None
    key: str
    workspace_id: str | None
    project_key: str | None
    kind: str | None
    owner: str | None
    module: str | None
    sync_qdrant: bool
    sync_surreal: bool
    warnings: list[str]


def iter_markdown(root: Path) -> Iterable[Path]:
    for current_root, dirs, files in os.walk(root):
        dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
        for name in files:
            if name.endswith(".md"):
                yield Path(current_root) / name


def read_frontmatter(path: Path) -> str | None:
    text = path.read_text(encoding="utf-8", errors="replace")
    if not text.startswith("---\n") and not text.startswith("---\r\n"):
        return None
    normalized = text.replace("\r\n", "\n")
    parts = normalized.split("---\n", 2)
    if len(parts) < 3:
        return None
    return parts[1]


def scalar(frontmatter: str, name: str) -> str | None:
    match = re.search(rf"(?m)^\s{{2}}{re.escape(name)}:\s*(.+?)\s*$", frontmatter)
    if not match:
        return None
    value = match.group(1).strip()
    if value in {"null", "None", ""}:
        return None
    return value.strip('"')


def bool_scalar(frontmatter: str, name: str) -> bool:
    match = re.search(rf"(?m)^\s{{4}}{re.escape(name)}:\s*(true|false)\s*$", frontmatter, re.I)
    return bool(match and match.group(1).lower() == "true")


def parse_touchpoint(root: Path, path: Path) -> Touchpoint | None:
    fm = read_frontmatter(path)
    if fm is None:
        return None

    if re.search(r"(?m)^clyffy_touchpoint:\s*$", fm):
        key = "clyffy_touchpoint"
    elif re.search(r"(?m)^wardenclyffe_touchpoint:\s*$", fm):
        key = "wardenclyffe_touchpoint"
    else:
        return None

    warnings: list[str] = []
    version_raw = scalar(fm, "version")
    try:
        version = int(version_raw) if version_raw is not None else None
    except ValueError:
        version = None
        warnings.append("version is not an integer")

    workspace_id = scalar(fm, "workspace_id")
    project_key = scalar(fm, "project_key")
    kind = scalar(fm, "kind")
    owner = scalar(fm, "owner")
    module = scalar(fm, "module")
    sync_qdrant = bool_scalar(fm, "qdrant")
    sync_surreal = bool_scalar(fm, "surreal")

    if key == "wardenclyffe_touchpoint":
        warnings.append("v1 touchpoint shape is deprecated; migrate to clyffy_touchpoint v2")
    if key == "clyffy_touchpoint":
        if version != 2:
            warnings.append("clyffy_touchpoint should use version 2")
        if not workspace_id:
            warnings.append("missing workspace_id")
        if not project_key:
            warnings.append("missing project_key")
    if not kind:
        warnings.append("missing kind")
    if not owner:
        warnings.append("missing owner")

    return Touchpoint(
        path=str(path.relative_to(root)).replace("\\", "/"),
        version=version,
        key=key,
        workspace_id=workspace_id,
        project_key=project_key,
        kind=kind,
        owner=owner,
        module=module,
        sync_qdrant=sync_qdrant,
        sync_surreal=sync_surreal,
        warnings=warnings,
    )


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--root", default=".", help="repository root")
    parser.add_argument("--json", action="store_true", help="emit JSON inventory")
    parser.add_argument("--strict", action="store_true", help="exit non-zero when warnings are present")
    args = parser.parse_args()

    root = Path(args.root).resolve()
    touchpoints = [
        item
        for path in iter_markdown(root)
        if (item := parse_touchpoint(root, path)) is not None
    ]
    touchpoints.sort(key=lambda item: item.path)

    if args.json:
        print(json.dumps([asdict(item) for item in touchpoints], indent=2))
        return 0

    v2 = sum(1 for item in touchpoints if item.key == "clyffy_touchpoint" and item.version == 2)
    v1 = sum(1 for item in touchpoints if item.key == "wardenclyffe_touchpoint")
    qdrant = sum(1 for item in touchpoints if item.sync_qdrant)
    surreal = sum(1 for item in touchpoints if item.sync_surreal)
    warnings = [(item.path, warning) for item in touchpoints for warning in item.warnings]

    print(f"touchpoints={len(touchpoints)} v2={v2} v1_deprecated={v1} qdrant_sync={qdrant} surreal_sync={surreal}")
    for path, warning in warnings:
        print(f"WARN {path}: {warning}", file=sys.stderr)

    return 1 if args.strict and warnings else 0


if __name__ == "__main__":
    raise SystemExit(main())
