#!/usr/bin/env bash
set -euo pipefail

if [[ "$(hostname)" != "warden-devstation-01" ]]; then
  echo "error: this installer must run on warden-devstation-01" >&2
  exit 1
fi

if ! id wardenop >/dev/null 2>&1; then
  echo "error: user wardenop is missing" >&2
  exit 1
fi

packages=(
  podman
  distrobox
  bubblewrap
  uidmap
  slirp4netns
  fuse-overlayfs
)

sudo apt-get update
sudo DEBIAN_FRONTEND=noninteractive apt-get install -y "${packages[@]}"

if ! grep -q '^wardenop:' /etc/subuid; then
  echo 'wardenop:100000:65536' | sudo tee -a /etc/subuid >/dev/null
fi

if ! grep -q '^wardenop:' /etc/subgid; then
  echo 'wardenop:100000:65536' | sudo tee -a /etc/subgid >/dev/null
fi

sudo loginctl enable-linger wardenop || true

echo "devstation_container_runtime=installed"
podman --version
distrobox --version
bwrap --version || bubblewrap --version || true

sudo -n -u wardenop bash -lc 'podman info --format "{{.Host.Security.Rootless}}"'
