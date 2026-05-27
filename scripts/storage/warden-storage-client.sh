#!/usr/bin/env bash
set -euo pipefail

COMMAND="${1:-status}"
SMB_HOST="${WARDEN_STORAGE_SMB_HOST:-10.0.0.117}"
SMB_SHARE="${WARDEN_STORAGE_SMB_SHARE:-warden-storage}"
MOUNT_PATH="${WARDEN_STORAGE_MOUNT_PATH:-/mnt/warden/storage}"
SYMLINK_PATH="${WARDEN_STORAGE_SYMLINK_PATH:-~/warden-storage}"
BROKER_HOST="${WARDEN_STORAGE_BROKER_HOST:-warden-capsule}"
INFISICAL_PROJECT_ID="${WARDEN_INFISICAL_PROJECT_ID:-4a897376-3cbd-4aeb-8550-c7d3ed7ad113}"
INFISICAL_ENV="${WARDEN_INFISICAL_ENV:-dev}"
INFISICAL_PATH="${WARDEN_SHARED_STORAGE_INFISICAL_PATH:-/warden/shared-storage/01}"
SECRET_NAME="${WARDEN_SHARED_STORAGE_SECRET_NAME:-WARDEN_SHARED_STORAGE_01_SMB_PASSWORD}"
CRED_FILE="/run/warden-secrets/warden-shared-storage-01-local.smb"

usage() {
  cat <<'USAGE'
Usage:
  warden-storage-client.sh status
  warden-storage-client.sh mount
  warden-storage-client.sh unmount
  warden-storage-client.sh path

Purpose:
  Mount Warden shared storage on a Linux/WSL client without printing secrets.

Defaults:
  SMB host:   10.0.0.117
  SMB share:  warden-storage
  Mount path: /mnt/warden/storage
  Symlink:    ~/warden-storage

The mount command brokers the SMB password through warden-capsule/Infisical and
writes a temporary CIFS credentials file under /run/warden-secrets.
USAGE
}

require_root() {
  if [[ "${EUID}" -ne 0 ]]; then
    exec sudo -E "$0" "$@"
  fi
}

operator_user() {
  if [[ -n "${SUDO_USER:-}" && "${SUDO_USER}" != "root" ]]; then
    printf '%s\n' "${SUDO_USER}"
  else
    id -un
  fi
}

run_as_operator() {
  local user
  user="$(operator_user)"
  if [[ "${EUID}" -eq 0 && "${user}" != "root" ]]; then
    runuser -u "${user}" -- "$@"
  else
    "$@"
  fi
}

broker_password() {
  if [[ -n "${WARDEN_SHARED_STORAGE_01_SMB_PASSWORD:-}" ]]; then
    printf '%s\n' "${WARDEN_SHARED_STORAGE_01_SMB_PASSWORD}"
    return
  fi

  run_as_operator ssh "${BROKER_HOST}" \
    "infisical secrets get ${SECRET_NAME} --projectId ${INFISICAL_PROJECT_ID} --env ${INFISICAL_ENV} --path ${INFISICAL_PATH} --output json | python3 -c 'import json,sys; data=json.load(sys.stdin); item=data[0] if isinstance(data,list) else data; value=item.get(\"secretValue\") or item.get(\"value\"); assert value; print(value)'"
}

write_credentials() {
  local password
  password="$(broker_password)"
  if [[ -z "${password}" ]]; then
    echo "error: empty SMB password from broker" >&2
    exit 1
  fi

  install -d -m 0700 /run/warden-secrets
  {
    printf 'username=%s\n' 'warden-share'
    printf 'password=%s\n' "${password}"
  } > "${CRED_FILE}"
  unset password
  chmod 0600 "${CRED_FILE}"
}

ensure_link() {
  local user home_dir link_path
  user="$(operator_user)"
  home_dir="$(getent passwd "${user}" | cut -d: -f6)"
  link_path="${SYMLINK_PATH/#\~/${home_dir}}"

  if [[ "${link_path}" != "${MOUNT_PATH}" ]]; then
    if [[ -L "${link_path}" || ! -e "${link_path}" ]]; then
      ln -sfn "${MOUNT_PATH}" "${link_path}"
      chown -h "${user}:${user}" "${link_path}" 2>/dev/null || true
    fi
  fi
}

cmd_mount() {
  require_root mount
  write_credentials
  install -d -m 0770 "${MOUNT_PATH}"

  local user uid gid
  user="$(operator_user)"
  uid="$(id -u "${user}")"
  gid="$(id -g "${user}")"
  chown "${uid}:${gid}" "${MOUNT_PATH}"

  if mountpoint -q "${MOUNT_PATH}"; then
    echo "already_mounted=1"
  else
    mount -t cifs "//${SMB_HOST}/${SMB_SHARE}" "${MOUNT_PATH}" \
      -o "credentials=${CRED_FILE},vers=3.1.1,seal,uid=${uid},gid=${gid},file_mode=0660,dir_mode=0770,noserverino"
    echo "mounted=1"
  fi

  ensure_link
  cmd_status
}

cmd_unmount() {
  require_root unmount
  if mountpoint -q "${MOUNT_PATH}"; then
    umount "${MOUNT_PATH}"
    echo "unmounted=1"
  else
    echo "mounted=0"
  fi
}

cmd_status() {
  echo "service=warden-shared-storage-01"
  echo "mount_path=${MOUNT_PATH}"
  echo "smb=//${SMB_HOST}/${SMB_SHARE}"
  if mountpoint -q "${MOUNT_PATH}"; then
    echo "mounted=1"
    df -h "${MOUNT_PATH}" | tail -n 1
  else
    echo "mounted=0"
  fi
}

case "${COMMAND}" in
  mount) cmd_mount ;;
  unmount) cmd_unmount ;;
  status) cmd_status ;;
  path) printf '%s\n' "${MOUNT_PATH}" ;;
  help|-h|--help) usage ;;
  *)
    usage >&2
    exit 2
    ;;
esac
