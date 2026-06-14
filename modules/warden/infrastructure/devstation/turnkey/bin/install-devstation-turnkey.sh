#!/usr/bin/env bash
# install-devstation-turnkey.sh — normalize a Warden devstation into the turnkey
# shape: install the Infisical Agent secret broker, the refresh hooks, and the
# verification tool, then enable + start the broker.
#
# Idempotent. Run as root (or via sudo) on the devstation. Reads the machine
# identity from /etc/warden/infisical-mi.env (root-only) — it never echoes the
# secret. Mirrors the 2026-06-06 turnkey-secret decision.
set -euo pipefail

HERE="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TURNKEY="$(cd "$HERE/.." && pwd)"           # modules/.../devstation/turnkey
MI_ENV="/etc/warden/infisical-mi.env"
ETC="/etc/warden"
BIN="/usr/local/bin"

require_root() { [ "$(id -u)" -eq 0 ] || { echo "must run as root (sudo)"; exit 1; }; }
have() { command -v "$1" >/dev/null 2>&1; }

require_root

echo "==> 1/6 machine identity"
if [ ! -f "$MI_ENV" ]; then
  install -d -m 0700 "$ETC"
  install -m 0600 "$TURNKEY/etc/warden/infisical-mi.env.template" "$MI_ENV"
  echo "    wrote template -> $MI_ENV (fill it, then re-run). exiting."
  exit 2
fi
# shellcheck disable=SC1090
set -a; . "$MI_ENV"; set +a
: "${INFISICAL_CLIENT_ID:?set in $MI_ENV}"
: "${INFISICAL_CLIENT_SECRET:?set in $MI_ENV}"
: "${INFISICAL_PROJECT_ID:?set in $MI_ENV}"
: "${INFISICAL_ENV:?set in $MI_ENV}"

echo "==> 2/6 infisical binary -> $BIN/infisical"
if ! [ -x "$BIN/infisical" ]; then
  if have infisical; then ln -sf "$(command -v infisical)" "$BIN/infisical";
  else echo "    infisical CLI not found; install it first"; exit 1; fi
fi

echo "==> 3/6 credential files (root-only)"
install -d -m 0700 "$ETC/infisical"
printf '%s' "$INFISICAL_CLIENT_ID"     > "$ETC/infisical/client-id"
printf '%s' "$INFISICAL_CLIENT_SECRET" > "$ETC/infisical/client-secret"
chmod 0600 "$ETC/infisical/client-id" "$ETC/infisical/client-secret"

echo "==> 4/6 agent config + template (project/env substituted, secret never written)"
install -m 0644 "$TURNKEY/etc/warden/agent-config.yaml" "$ETC/agent-config.yaml"
sed -e "s#__PROJECT_ID__#${INFISICAL_PROJECT_ID}#g" \
    -e "s#__ENV__#${INFISICAL_ENV}#g" \
    "$TURNKEY/etc/warden/secrets.tmpl" > "$ETC/secrets.tmpl"
chmod 0644 "$ETC/secrets.tmpl"
# Optional self-hosted address
if [ -n "${INFISICAL_API_URL:-}" ]; then
  sed -i "s#address: \"https://app.infisical.com\".*#address: \"${INFISICAL_API_URL}\"#" "$ETC/agent-config.yaml"
fi

echo "==> 5/6 hooks + status tool -> $BIN"
install -m 0755 "$TURNKEY/bin/warden-secrets-preflight"      "$BIN/warden-secrets-preflight"
install -m 0755 "$TURNKEY/bin/warden-secrets-refresh-hook"   "$BIN/warden-secrets-refresh-hook"
install -m 0755 "$TURNKEY/bin/warden-devstation-status"      "$BIN/warden-devstation-status"

echo "==> 6/6 install + start broker"
install -m 0644 "$TURNKEY/systemd/infisical-agent.service" /etc/systemd/system/infisical-agent.service
systemctl daemon-reload
systemctl enable --now infisical-agent.service

echo "==> done. verifying:"
sleep 2
"$BIN/warden-devstation-status" || true
