#!/bin/bash
# Pull Infisical only when the operator explicitly considers it.
# Usage: CONSIDER_UPSTREAM=yes ./consider-upstream.sh [tag]
set -euo pipefail
ROOT="${VAULTIX_UPSTREAM:-/opt/vaultix/upstream/infisical}"
TAG="${1:-}"
if [[ "${CONSIDER_UPSTREAM:-}" != "yes" ]]; then
  echo "refused: set CONSIDER_UPSTREAM=yes if you want to look at Infisical again." >&2
  echo "this tree is the Vaultix fork. waiver: SHIPPIN-WAIVER.md" >&2
  exit 2
fi
cd "$ROOT"
git remote add infisical https://github.com/Infisical/infisical.git 2>/dev/null || \
  git remote set-url infisical https://github.com/Infisical/infisical.git
git fetch --tags infisical
if [[ -n "$TAG" ]]; then
  echo "fetched. checkout is still yours. to inspect: git log $TAG"
else
  echo "fetched remotes/tags only. no merge. no rebase."
fi
echo "default remains: do not merge ee/. stay branched unless you say so."
