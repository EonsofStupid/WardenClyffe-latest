#!/bin/bash
set -e

# ─── WardenClyffeNet Docker Entrypoint ────────────────────────────────────────────────
# First-run bootstrap, then exec the daemon. The WardenClyffeNet binary creates
# config.toml and the private key itself on `init` / `join`, so this script
# just picks which of those to run on first boot.

CONFIG="/etc/wardenclyffenet/config.toml"
CONFIG_DIR="$(dirname "$CONFIG")"
STATUS_DIR="/var/run/wardenclyffenet"

mkdir -p "$CONFIG_DIR" "$STATUS_DIR"

if [ ! -c /dev/net/tun ]; then
    echo "✗ /dev/net/tun not found inside the container."
    echo "  Run with:  --device /dev/net/tun:/dev/net/tun"
    echo "  If the module is not loaded on the host:  sudo modprobe tun"
    exit 1
fi

if [ ! -f "$CONFIG" ]; then
    if [ -n "$WARDENCLYFFENET_JOIN_TOKEN" ]; then
        echo "→ Joining WardenClyffeNet using supplied invite token..."
        wardenclyffenet --config "$CONFIG" join "$WARDENCLYFFENET_JOIN_TOKEN"
    else
        ADDRESS="${WARDENCLYFFENET_ADDRESS:-10.0.10.1}"
        echo "→ No config and no join token — initialising new network at $ADDRESS"
        echo "  (run 'docker exec wardenclyffenet wardenclyffenet invite' to get a token for peers)"
        wardenclyffenet --config "$CONFIG" init --address "$ADDRESS"
    fi
fi

echo "→ Starting WardenClyffeNet daemon..."
exec wardenclyffenet --config "$CONFIG" "$@"
