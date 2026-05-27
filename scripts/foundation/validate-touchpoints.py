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

WORD_RE = re.compile(r"[A-Za-z0-9][A-Za-z0-9_'.-]*")


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
    body_words: int
    heavy_sync: bool
    heavy_touchpoint: bool
    warnings: list[str]


def iter_markdown(root: Path) -> Iterable[Path]:
    for current_root, dirs, files in os.walk(root):
        dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
        for name in files:
            if name.endswith(".md"):
                yield Path(current_root) / name


def read_markdown_parts(path: Path) -> tuple[str, str] | None:
    text = path.read_text(encoding="utf-8", errors="replace")
    if not text.startswith("---\n") and not text.startswith("---\r\n"):
        return None
    normalized = text.replace("\r\n", "\n")
    parts = normalized.split("---\n", 2)
    if len(parts) < 3:
        return None
    return parts[1], parts[2]


def count_words(text: str) -> int:
    return len(WORD_RE.findall(text))


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


def parse_touchpoint(
    root: Path,
    path: Path,
    max_sync_body_words: int,
    max_body_words: int,
) -> Touchpoint | None:
    parts = read_markdown_parts(path)
    if parts is None:
        return None
    fm, body = parts

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
    body_words = count_words(body)
    heavy_sync = (sync_qdrant or sync_surreal) and body_words > max_sync_body_words
    heavy_touchpoint = body_words > max_body_words

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
    if heavy_sync:
        warnings.append(
            f"sync-enabled touchpoint is too large ({body_words} words > {max_sync_body_words}); "
            "move detail into typed sources, generated projections, or runtime context packs"
        )
    if heavy_touchpoint:
        warnings.append(
            f"touchpoint body is too large ({body_words} words > {max_body_words}); "
            "keep Markdown as a routing manifest"
        )

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
        body_words=body_words,
        heavy_sync=heavy_sync,
        heavy_touchpoint=heavy_touchpoint,
        warnings=warnings,
    )


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--root", default=".", help="repository root")
    parser.add_argument("--json", action="store_true", help="emit JSON inventory")
    parser.add_argument("--strict", action="store_true", help="exit non-zero when warnings are present")
    parser.add_argument(
        "--max-sync-body-words",
        type=int,
        default=1200,
        help="warn when sync-enabled touchpoints exceed this word count",
    )
    parser.add_argument(
        "--max-body-words",
        type=int,
        default=2500,
        help="warn when any touchpoint exceeds this word count",
    )
    args = parser.parse_args()

    root = Path(args.root).resolve()
    touchpoints: list[Touchpoint] = []
    for path in iter_markdown(root):
        item = parse_touchpoint(
            root,
            path,
            args.max_sync_body_words,
            args.max_body_words,
        )
        if item is not None:
            touchpoints.append(item)
    touchpoints.sort(key=lambda item: item.path)

    if args.json:
        print(json.dumps([asdict(item) for item in touchpoints], indent=2))
        return 0

    v2 = sum(1 for item in touchpoints if item.key == "clyffy_touchpoint" and item.version == 2)
    v1 = sum(1 for item in touchpoints if item.key == "wardenclyffe_touchpoint")
    qdrant = sum(1 for item in touchpoints if item.sync_qdrant)
    surreal = sum(1 for item in touchpoints if item.sync_surreal)
    heavy_sync = sum(1 for item in touchpoints if item.heavy_sync)
    heavy_touchpoints = sum(1 for item in touchpoints if item.heavy_touchpoint)
    warnings = [(item.path, warning) for item in touchpoints for warning in item.warnings]

    print(
        f"touchpoints={len(touchpoints)} v2={v2} v1_deprecated={v1} "
        f"qdrant_sync={qdrant} surreal_sync={surreal} "
        f"heavy_sync={heavy_sync} heavy_touchpoints={heavy_touchpoints}"
    )
    for path, warning in warnings:
        print(f"WARN {path}: {warning}", file=sys.stderr)

    return 1 if args.strict and warnings else 0


if __name__ == "__main__":
    raise SystemExit(main())
