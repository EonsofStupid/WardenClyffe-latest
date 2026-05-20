#!/bin/bash
set -e

# ─── WardenClyffeDisk Docker Entrypoint ──────────────────────────────────────────────
# Writes /etc/wardenclyffedisk/config.toml from env vars on first run, initialises
# the data directory, then mounts the filesystem.

CONFIG="/etc/wardenclyffedisk/config.toml"
DATA_DIR="${WARDENCLYFFEDISK_DATA_DIR:-/var/lib/wardenclyffedisk}"
MOUNT="${WARDENCLYFFEDISK_MOUNT:-/mnt/wardenclyffedisk}"

mkdir -p "$(dirname "$CONFIG")" "$MOUNT" "$DATA_DIR"

if [ ! -c /dev/fuse ]; then
    echo "✗ /dev/fuse not found inside the container."
    echo "  Run with:  --device /dev/fuse:/dev/fuse  (and usually --privileged or --cap-add SYS_ADMIN)"
    exit 1
fi

wardenclyffedisk --config "$CONFIG" init --data-dir "$DATA_DIR" >/dev/null 2>&1 || true

if [ ! -f "$CONFIG" ]; then
    NODE_ID="${WARDENCLYFFEDISK_NODE_ID:-$(hostname)}"
    ROLE="${WARDENCLYFFEDISK_ROLE:-client}"
    BIND="${WARDENCLYFFEDISK_BIND:-0.0.0.0:8550}"
    MODE="${WARDENCLYFFEDISK_MODE:-shared}"

    # Parse comma-separated WARDENCLYFFEDISK_PEERS into a TOML array
    peers_toml=""
    if [ -n "$WARDENCLYFFEDISK_PEERS" ]; then
        IFS=',' read -ra parts <<< "$WARDENCLYFFEDISK_PEERS"
        first=1
        for p in "${parts[@]}"; do
            p="${p// /}"
            [ -z "$p" ] && continue
            if [ $first -eq 1 ]; then
                peers_toml="\"$p\""
                first=0
            else
                peers_toml="$peers_toml, \"$p\""
            fi
        done
    fi

    cat > "$CONFIG" <<EOF
[node]
id = "$NODE_ID"
role = "$ROLE"
bind = "$BIND"
data_dir = "$DATA_DIR"

[cluster]
peers = [$peers_toml]

[replication]
mode = "$MODE"
factor = 3
chunk_size = 4194304

[mount]
path = "$MOUNT"
allow_other = true
EOF
    echo "→ Wrote $CONFIG (role=$ROLE, peers=[$peers_toml])"
fi

echo "→ Mounting WardenClyffeDisk at $MOUNT..."
exec wardenclyffedisk --config "$CONFIG" mount --mountpoint "$MOUNT" "$@"
