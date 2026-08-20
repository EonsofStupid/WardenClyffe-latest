# API inventory tooling (ADR 0005)

Regenerates `schemas/upstream.core.v<TAG>.endpoints.tsv` — the classified
endpoint ledger the alignment tests enforce against. Run this whenever the
core image pin moves.

```bash
# 1. Sparse checkout of the pinned tag (scratch dir; NOT the fork tree —
#    consider-upstream.sh still gates pulls into our tree)
git clone --depth 1 --branch v<TAG> --filter=blob:none --sparse \
    https://github.com/Infisical/infisical.git upstream-inspect
cd upstream-inspect
git sparse-checkout set --no-cone \
    '/backend/src/server/routes' '/backend/src/ee/routes' \
    '**/*enums.ts' '/backend/src/ee/services/external-kms/providers'

# 2. Capture the spec from the running core (it documents itself)
./capture-openapi.sh https://vaultix.shippin.cloud openapi.json

# 3. Classify: resolves the Fastify registration tree (nested wrappers,
#    router maps + enum segments, factories, curried/alias exports) and
#    matches every spec operation to its source file
./classify.py upstream-inspect openapi.json \
    ../../schemas/upstream.core.v<TAG>.endpoints.tsv
```

The classifier must report **0 unresolved** and no enum warnings; anything
else means upstream introduced a new registration pattern — extend
`classify.py`, do not hand-edit the TSV. At v0.162.19: 2,281 operations,
1,804 core / 477 ee, all basis `source`.
