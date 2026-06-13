#!/usr/bin/env python3
"""Validate every plugins/*/plugin.json against the master plugin.v1 contract.

Mirrors validate-touchpoints.py: dependency-light, repo-rooted, CI-friendly.
The contract is schemas/contracts/plugin.v1.schema.json. cortex-control is the
master control-layer plugin; Clyffy-Dean minions derive from it and must pass
this same check. Exit non-zero on any failure.

Uses the `jsonschema` package when available (full Draft 2020-12); otherwise
falls back to a structural check of required keys + enums so CI without the
dependency still catches gross drift.
"""
import json
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SCHEMA = ROOT / "schemas" / "contracts" / "plugin.v1.schema.json"
PLUGINS = ROOT / "plugins"


def manifests():
    return sorted(PLUGINS.glob("*/plugin.json"))


def validate_full(schema, docs):
    from jsonschema import Draft202012Validator

    v = Draft202012Validator(schema)
    failed = False
    for p, doc in docs:
        errs = sorted(v.iter_errors(doc), key=lambda e: list(e.path))
        if errs:
            failed = True
            print(f"FAIL {p.relative_to(ROOT)}")
            for e in errs:
                print(f"  - {list(e.path)}: {e.message}")
        else:
            print(f"OK   {p.relative_to(ROOT)}")
    return failed


def validate_fallback(schema, docs):
    required = set(schema["required"])
    kinds = set(schema["properties"]["kind"]["enum"])
    failed = False
    for p, doc in docs:
        problems = []
        missing = required - set(doc)
        if missing:
            problems.append(f"missing keys: {sorted(missing)}")
        if doc.get("kind") not in kinds:
            problems.append(f"kind {doc.get('kind')!r} not in {sorted(kinds)}")
        if problems:
            failed = True
            print(f"FAIL {p.relative_to(ROOT)}: {'; '.join(problems)}")
        else:
            print(f"OK   {p.relative_to(ROOT)} (structural check; jsonschema not installed)")
    return failed


def main():
    schema = json.loads(SCHEMA.read_text())
    docs = [(p, json.loads(p.read_text())) for p in manifests()]
    if not docs:
        print("no plugin manifests found", file=sys.stderr)
        return 1
    try:
        import jsonschema  # noqa: F401

        failed = validate_full(schema, docs)
    except ImportError:
        failed = validate_fallback(schema, docs)
    print(f"plugins={len(docs)} contract=plugin.v1 result={'FAIL' if failed else 'PASS'}")
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main())
