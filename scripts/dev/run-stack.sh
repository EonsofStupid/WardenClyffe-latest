#!/usr/bin/env bash
# Run the WardenClyffe dev stack on the devstation:
#   - warden-api   (Go) on :8081 — operator plane, backed by local Postgres
#   - clyffe-api   (Go) on :8082 — customer plane, customer-safe reads only
#   - console      (React/Vite) on :5173, proxying /api -> warden-api
#
# View it: from your laptop, SSH local-forward 5173 to the devstation, then
# open http://127.0.0.1:5173
#     ssh -L 5173:127.0.0.1:5173 warden-devstation
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

export WARDEN_DB_URL="${WARDEN_DB_URL:-postgres://warden:warden_dev_local@127.0.0.1:5432/wardenclyffe?sslmode=disable}"
export WARDEN_API_ADDR="${WARDEN_API_ADDR:-:8081}"
export CLYFFE_DB_URL="${CLYFFE_DB_URL:-$WARDEN_DB_URL}"
export CLYFFE_API_ADDR="${CLYFFE_API_ADDR:-:8082}"

echo "[dev] building warden-api..."
( cd "$ROOT/services/warden-api" && go build -o /tmp/warden-api ./cmd/warden-api )
echo "[dev] building clyffe-api..."
( cd "$ROOT/services/clyffe-api" && go build -o /tmp/clyffe-api ./cmd/clyffe-api )

echo "[dev] starting warden-api on $WARDEN_API_ADDR ..."
/tmp/warden-api &
WARDEN_PID=$!
echo "[dev] starting clyffe-api on $CLYFFE_API_ADDR ..."
/tmp/clyffe-api &
CLYFFE_PID=$!

cleanup() { kill "$WARDEN_PID" "$CLYFFE_PID" 2>/dev/null || true; }
trap cleanup EXIT

echo "[dev] starting console on :5173 ..."
cd "$ROOT/apps/console"
[ -d node_modules ] || npm install --no-audit --no-fund
exec npm run dev
