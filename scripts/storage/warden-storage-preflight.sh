#!/usr/bin/env bash
set -euo pipefail

TARGET="${1:-all}"
CANONICAL_MOUNT="${WARDEN_STORAGE_CANONICAL_MOUNT:-/workspace/warden-storage}"
PROJECT_PATH="${WARDEN_STORAGE_PROJECT_PATH:-${CANONICAL_MOUNT}/projects/shippin-platform}"

usage() {
  cat <<'USAGE'
Usage:
  scripts/storage/warden-storage-preflight.sh [all|local|windows|devstation|capsule]

Checks the Warden shared-storage mount contract without printing secrets.
The required remote mount path is /workspace/warden-storage on devstation.
Local WSL may still use /mnt/warden/storage as a client convenience.
Windows uses W: as the native endpoint mapping when private reachability exists.
Capsule is checked only as brokered/infra access; it is not the daily SSOT mount.
USAGE
}

remote_check() {
  local label="$1"
  local host="$2"
  ssh -o BatchMode=yes -o ConnectTimeout=8 "${host}" \
    "WARDEN_PREFLIGHT_LABEL=${label} WARDEN_STORAGE_CANONICAL_MOUNT=${CANONICAL_MOUNT} WARDEN_STORAGE_PROJECT_PATH=${PROJECT_PATH} bash -s" <<'REMOTE'
set -euo pipefail
label="${WARDEN_PREFLIGHT_LABEL}"
mount_path="${WARDEN_STORAGE_CANONICAL_MOUNT}"
project_path="${WARDEN_STORAGE_PROJECT_PATH}"

echo "target=${label}"
echo "host=$(hostname)"
echo "mount_path=${mount_path}"
if findmnt -T "${mount_path}" >/dev/null 2>&1; then
  echo "mounted=1"
  findmnt -T "${mount_path}" -o SOURCE,FSTYPE -n | awk '{print "source="$1; print "fstype="$2}'
  df -hP "${mount_path}" | awk 'NR==2 {print "size="$2; print "used="$3; print "available="$4; print "use_percent="$5}'
else
  echo "mounted=0"
fi

if [ -d "${project_path}" ]; then
  echo "project_path=${project_path}"
  echo "project_present=1"
  git -C "${project_path}" rev-parse --short HEAD 2>/dev/null | sed 's/^/root_commit=/' || true
  git -C "${project_path}/wardenclyffe" rev-parse --short HEAD 2>/dev/null | sed 's/^/nested_commit=/' || true
else
  echo "project_present=0"
fi
REMOTE
}

local_check() {
  local mount_path="${WARDEN_LOCAL_STORAGE_MOUNT:-/mnt/warden/storage}"
  local project_path="${mount_path}/projects/shippin-platform"
  echo "target=local"
  echo "host=$(hostname)"
  echo "mount_path=${mount_path}"
  if findmnt -T "${mount_path}" >/dev/null 2>&1; then
    echo "mounted=1"
    findmnt -T "${mount_path}" -o SOURCE,FSTYPE -n | awk '{print "source="$1; print "fstype="$2}'
    df -hP "${mount_path}" | awk 'NR==2 {print "size="$2; print "used="$3; print "available="$4; print "use_percent="$5}'
  else
    echo "mounted=0"
  fi
  if [ -d "${project_path}" ]; then
    echo "project_path=${project_path}"
    echo "project_present=1"
    git -C "${project_path}" rev-parse --short HEAD 2>/dev/null | sed 's/^/root_commit=/' || true
    git -C "${project_path}/wardenclyffe" rev-parse --short HEAD 2>/dev/null | sed 's/^/nested_commit=/' || true
  else
    echo "project_present=0"
  fi
}

windows_check() {
  echo "target=windows"
  if ! command -v powershell.exe >/dev/null 2>&1; then
    echo "available=0"
    echo "reason=powershell.exe_not_available"
    return
  fi
  powershell.exe -NoProfile -Command '
$ErrorActionPreference = "Stop"
$mapping = Get-SmbMapping -LocalPath W: -ErrorAction SilentlyContinue
if (-not $mapping) {
  "mounted=0"
  exit 0
}
"mounted=1"
"drive=W:"
"remote_path=$($mapping.RemotePath)"
"status=$($mapping.Status)"
$drive = Get-PSDrive -Name W -PSProvider FileSystem
"available_gib=$([math]::Round($drive.Free / 1GB, 1))"
"used_gib=$([math]::Round($drive.Used / 1GB, 1))"
$project = "W:\projects\shippin-platform"
"project_path=$project"
"project_present=$((Test-Path -LiteralPath $project).ToString().ToLowerInvariant())"
'
}

capsule_check() {
  ssh -o BatchMode=yes -o ConnectTimeout=8 capsule.clyffy.ai \
    "bash -s" <<'REMOTE'
set -euo pipefail
echo "target=capsule"
echo "host=$(hostname)"
echo "storage_posture=brokered_only"
echo "kernel_mount_required=0"
if [ -d /workspace/WardenClyffe-latest ]; then
  echo "workspace=/workspace/WardenClyffe-latest"
  echo "workspace_present=1"
else
  echo "workspace_present=0"
fi
REMOTE
}

case "${TARGET}" in
  all)
    local_check
    echo "---"
    windows_check
    echo "---"
    remote_check devstation devstation.clyffy.ai
    ;;
  local)
    local_check
    ;;
  windows)
    windows_check
    ;;
  devstation)
    remote_check devstation devstation.clyffy.ai
    ;;
  capsule)
    capsule_check
    ;;
  help|-h|--help)
    usage
    ;;
  *)
    usage >&2
    exit 2
    ;;
esac
