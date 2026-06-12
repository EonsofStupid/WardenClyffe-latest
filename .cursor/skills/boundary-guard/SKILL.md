---
name: boundary-guard
description: FORCED pre-creation review for WardenClyffe. Auto-invoke during development whenever a NEW folder, module, package, service, or domain boundary is about to be created. Snapshots and diffs the file tree, then checks the proposed path against existing boundaries so Clyffy reviews it and does not duplicate effort. Companion to the parking-lot skill.
---

# Boundary Guard (wrapper)

This is a thin wrapper. The source of truth is
`docs/ai/WARDENCLYFFE_BOUNDARY_GUARD_SKILL.md` — read and follow it.

Before creating any new boundary, run the duplicate check first:

```bash
python scripts/foundation/boundary-guard-tree.py --snapshot   # refresh manifest
python scripts/foundation/boundary-guard-tree.py --diff       # what changed
python scripts/foundation/boundary-guard-tree.py --check "<proposed/path>"
```

Exit 2 = likely duplicate; reuse the existing boundary instead of creating a new
one. Establishing a genuinely new domain needs operator approval. Pairs with the
`parking-lot` skill (`docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md`).
