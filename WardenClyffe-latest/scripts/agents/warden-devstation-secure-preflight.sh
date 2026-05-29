#!/usr/bin/env bash
set -euo pipefail

failures=0

check_pass() {
  printf '[pass] %s\n' "$1"
}

check_fail() {
  printf '[fail] %s\n' "$1"
  failures=$((failures + 1))
}

check_warn() {
  printf '[warn] %s\n' "$1"
}

require_command() {
  local name="$1"
  if command -v "$name" >/dev/null 2>&1; then
    check_pass "command ${name}: $(command -v "$name")"
  else
    check_fail "command ${name}: missing"
  fi
}

require_path() {
  local path="$1"
  if [[ -e "$path" ]]; then
    check_pass "path ${path}: present"
  else
    check_fail "path ${path}: missing"
  fi
}

check_optional_command() {
  local name="$1"
  if command -v "$name" >/dev/null 2>&1; then
    check_pass "optional command ${name}: $(command -v "$name")"
  else
    check_warn "optional command ${name}: missing"
  fi
}

host="$(hostname)"
user="$(whoami)"
groups="$(id -nG)"

printf 'target=warden-devstation-secure-workspace\n'
printf 'host=%s\n' "$host"
printf 'user=%s\n' "$user"
printf 'groups=%s\n' "$groups"

if [[ "$host" == "warden-devstation-01" ]]; then
  check_pass "hostname is warden-devstation-01"
else
  check_fail "hostname is ${host}, expected warden-devstation-01"
fi

if [[ "$user" == "wardenop" || "$user" == "hades" ]]; then
  check_pass "operator user ${user}"
else
  check_fail "unexpected operator user ${user}"
fi

require_path "/workspace/warden-storage/projects/WardenClyffe-latest"
require_path "/workspace/warden-storage/projects/Master-Clyffy"

require_command "tmux"
require_command "codex"
require_command "claude"
require_command "infisical"
require_command "gh"

if systemctl is-active --quiet code-server@wardenop 2>/dev/null; then
  check_pass "code-server@wardenop active"
else
  check_fail "code-server@wardenop not active"
fi

if [[ -d /run/warden-secrets ]]; then
  mode="$(stat -c '%a %U:%G' /run/warden-secrets)"
  check_pass "/run/warden-secrets exists (${mode})"
else
  check_fail "/run/warden-secrets missing"
fi

for helper in \
  /home/wardenop/bin/warden-secret-write \
  /home/wardenop/bin/warden-secret-list \
  /home/wardenop/bin/warden-secret-path \
  /home/wardenop/bin/warden-secret-remove \
  /home/wardenop/bin/warden-secret-breakglass-cat
do
  if [[ -x "$helper" ]]; then
    check_pass "secret helper ${helper}: executable"
  else
    check_fail "secret helper ${helper}: missing or not executable"
  fi
done

runtime_found=0
for runtime in podman docker; do
  if command -v "$runtime" >/dev/null 2>&1; then
    check_pass "container runtime ${runtime}: $(command -v "$runtime")"
    runtime_found=1
  fi
done
if [[ "$runtime_found" -eq 0 ]]; then
  check_fail "container runtime missing: install podman or docker"
fi

isolation_found=0
for isolation in distrobox bwrap bubblewrap firejail; do
  if command -v "$isolation" >/dev/null 2>&1; then
    check_pass "workspace isolation ${isolation}: $(command -v "$isolation")"
    isolation_found=1
  fi
done
if [[ "$isolation_found" -eq 0 ]]; then
  check_fail "workspace isolation missing: install distrobox, bubblewrap/bwrap, or firejail"
fi

check_optional_command "sops"
check_optional_command "age"
check_optional_command "uv"

if [[ "$failures" -eq 0 ]]; then
  printf 'preflight_result=pass\n'
  exit 0
fi

printf 'preflight_result=fail\n'
printf 'failure_count=%s\n' "$failures"
exit 1
