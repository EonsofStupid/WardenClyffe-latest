---
name: parking-lot
description: FORCED multi-stage planning/deliberation engine for WardenClyffe. Auto-invoke whenever work turns to planning, scoping, roadmaps, sprints, POAMs, trade-offs, or locking in any dependency, feature, capability, library, service, protocol, schema, or boundary — even if the operator never typed /parkinglot. Runs a five-stage protocol: frame → build an ordered agenda (bullet list) → deliberate each item with research-backed rounds (cap 12) until it is locked or parked → advance → crystallize a granular build spec. Captures every lock into the SurrealDB intelligence layer via versioned Markdown touchpoints. Default focus feature: Building WardenClyffe.
---

# Parking Lot (wrapper)

This is a thin wrapper. The source of truth is
`docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md` — read and follow it.

Enter Parking-Lot mode automatically on any planning or decision-making turn,
then run the five-stage protocol with its deterministic ledger:

```bash
# Stage 1 — agenda (the bullet list)
python scripts/foundation/parking-lot-agenda.py init  --focus "Building WardenClyffe" --boundary "<path>"
python scripts/foundation/parking-lot-agenda.py add   --agenda "<key>" --question "<decision>" --boundary "<path>"

# Stage 2/3 — deliberate each item (≤12 rounds), then lock or park, and capture
python scripts/foundation/parking-lot-agenda.py round --agenda "<key>" --item <id>
python scripts/foundation/parking-lot-capture.py --type dependency|feature|capability \
  --name "<name>" --decision "<what + why>" --boundary "<path>" --focus "Building WardenClyffe"
python scripts/foundation/parking-lot-agenda.py lock  --agenda "<key>" --item <id>
python scripts/foundation/parking-lot-agenda.py park  --agenda "<key>" --item <id> --reason "<why deferred>"

# Stage 4 — crystallize the granular build spec
python scripts/foundation/parking-lot-spec.py emit --agenda "<key>"
```

Before creating any new folder/boundary, defer to the `boundary-guard` skill
(`docs/ai/WARDENCLYFFE_BOUNDARY_GUARD_SKILL.md`). Never write to a database by
hand — touchpoints sync to Qdrant + SurrealDB via the sync worker.
