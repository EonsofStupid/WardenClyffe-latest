#!/usr/bin/env bash
# Run the WardenClyffe pre-release stack on the devstation:
#   warden-api :8081 · clyffe-api :8082 · console :5173
#
# Loads secrets/proxmox.env when present (never printed).
# PID files under .dev-pids/ — stop with scripts/dev/stop-stack.sh or make stop
#
# Laptop view (only when working on this host's console):
#   ssh -N -L 5173:127.0.0.1:5173 warden-devstation
#   http://127.0.0.1:5173/login
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PIDDIR="${WARDEN_PID_DIR:-$ROOT/.dev-pids}"
mkdir -p "$PIDDIR"

export WARDEN_REPO_ROOT="${WARDEN_REPO_ROOT:-$ROOT}"
export WARDEN_DB_URL="${WARDEN_DB_URL:-postgres://warden:warden_dev_local@127.0.0.1:5432/wardenclyffe?sslmode=disable}"
export WARDEN_API_ADDR="${WARDEN_API_ADDR:-:8081}"
export CLYFFE_DB_URL="${CLYFFE_DB_URL:-$WARDEN_DB_URL}"
export CLYFFE_API_ADDR="${CLYFFE_API_ADDR:-:8082}"
export WARDEN_OPERATOR_USER="${WARDEN_OPERATOR_USER:-operator}"
export WARDEN_OPERATOR_PASS="${WARDEN_OPERATOR_PASS:-warden-dev}"

# Load proxmox env into process without echoing
if [ -f "$ROOT/secrets/proxmox.env" ]; then
  set -a
  # shellcheck disable=SC1091
  source "$ROOT/secrets/proxmox.env" 2>/dev/null || true
  set +a
  echo "[dev] loaded secrets/proxmox.env (values not printed)"
else
  echo "[dev] WARN: no secrets/proxmox.env — Slice 0 inventory will be unconfigured"
fi

echo "[dev] building warden-api..."
( cd "$ROOT/services/warden-api" && go build -o "$PIDDIR/warden-api" ./cmd/warden-api )
echo "[dev] building clyffe-api..."
( cd "$ROOT/services/clyffe-api" && go build -o "$PIDDIR/clyffe-api" ./cmd/clyffe-api )

# Free ports if leftovers
if command -v fuser >/dev/null 2>&1; then
  fuser -k 8081/tcp 2>/dev/null || true
  fuser -k 8082/tcp 2>/dev/null || true
fi
sleep 0.5

echo "[dev] starting warden-api on $WARDEN_API_ADDR ..."
"$PIDDIR/warden-api" >"$PIDDIR/warden-api.log" 2>&1 &
echo $! >"$PIDDIR/warden-api.pid"

echo "[dev] starting clyffe-api on $CLYFFE_API_ADDR ..."
"$PIDDIR/clyffe-api" >"$PIDDIR/clyffe-api.log" 2>&1 &
echo $! >"$PIDDIR/clyffe-api.pid"

cleanup() {
  if [ -f "$PIDDIR/warden-api.pid" ]; then kill "$(cat "$PIDDIR/warden-api.pid")" 2>/dev/null || true; fi
  if [ -f "$PIDDIR/clyffe-api.pid" ]; then kill "$(cat "$PIDDIR/clyffe-api.pid")" 2>/dev/null || true; fi
}
trap cleanup EXIT

echo "[dev] waiting for APIs..."
for i in 1 2 3 4 5 6 7 8 9 10; do
  if curl -sS -m 1 "http://127.0.0.1:${WARDEN_API_ADDR#:}/healthz" >/dev/null 2>&1; then
    break
  fi
  sleep 0.3
done
curl -sS "http://127.0.0.1:${WARDEN_API_ADDR#:}/healthz" || true
echo
curl -sS "http://127.0.0.1:${WARDEN_API_ADDR#:}/api/warden/proxmox/status" || true
echo

echo "[dev] starting console on :5173 ..."
echo "[dev] Slice 0 UI: http://127.0.0.1:5173/admin/proxmox (after login)"
echo "[dev] Login: operator / warden-dev   STATE: docs/STATE.md"
cd "$ROOT"
[ -d node_modules ] || npm install --no-audit --no-fund
exec npm run dev
