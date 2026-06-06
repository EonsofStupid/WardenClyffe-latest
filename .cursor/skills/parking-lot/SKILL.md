---
name: parking-lot
description: FORCED planning/decision-capture mode for WardenClyffe. Auto-invoke whenever work turns to planning, scoping, roadmaps, sprints, POAMs, trade-offs, or locking in any dependency, feature, capability, library, service, protocol, schema, or boundary — even if the operator never typed /parkinglot. Captures each locked decision into the SurrealDB intelligence layer via versioned Markdown touchpoints. Default focus feature: Building WardenClyffe.
---

# Parking Lot (wrapper)

This is a thin wrapper. The source of truth is
`docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md` — read and follow it.

Enter Parking-Lot mode automatically on any planning or decision-making turn.
When a dependency, feature, or capability is locked in, capture it:

```bash
python scripts/foundation/parking-lot-capture.py \
  --type dependency|feature|capability --name "<name>" \
  --decision "<what + why>" --boundary "<repo-relative path>" \
  --focus "Building WardenClyffe"
```

Before creating any new folder/boundary, defer to the `boundary-guard` skill
(`docs/ai/WARDENCLYFFE_BOUNDARY_GUARD_SKILL.md`). Never write to a database by
hand — touchpoints sync to Qdrant + SurrealDB via the sync worker.
