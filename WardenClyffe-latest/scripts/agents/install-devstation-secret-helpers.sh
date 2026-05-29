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

sudo install -d -m 0700 -o wardenop -g wardenop /run/warden-secrets
printf 'd /run/warden-secrets 0700 wardenop wardenop -\n' |
  sudo tee /etc/tmpfiles.d/warden-secrets.conf >/dev/null

install -d -m 0700 /home/wardenop/bin

cat > /home/wardenop/bin/warden-secret-path <<'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail
name="${1:-}"
if [[ -z "$name" || "$name" == */* || ! "$name" =~ ^[A-Za-z0-9._-]+$ ]]; then
  echo "usage: warden-secret-path <safe-name>" >&2
  exit 2
fi
printf '/run/warden-secrets/%s\n' "$name"
SCRIPT

cat > /home/wardenop/bin/warden-secret-write <<'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail
name="${1:-}"
path="$(warden-secret-path "$name")"
umask 077
cat > "$path"
chmod 0600 "$path"
printf 'wrote %s\n' "$path"
SCRIPT

cat > /home/wardenop/bin/warden-secret-list <<'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail
if [[ ! -d /run/warden-secrets ]]; then
  echo "no secret mount"
  exit 0
fi
find /run/warden-secrets -maxdepth 1 -type f -printf '%f %m %u:%g %s bytes\n' | sort
SCRIPT

cat > /home/wardenop/bin/warden-secret-remove <<'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail
name="${1:-}"
path="$(warden-secret-path "$name")"
rm -f -- "$path"
printf 'removed %s\n' "$path"
SCRIPT

cat > /home/wardenop/bin/warden-secret-breakglass-cat <<'SCRIPT'
#!/usr/bin/env bash
set -euo pipefail
if [[ "${WARDEN_SECRET_VIEW_ALLOWED:-0}" != "1" ]]; then
  echo "refusing to print secret; set WARDEN_SECRET_VIEW_ALLOWED=1 for deliberate break-glass viewing" >&2
  exit 3
fi
name="${1:-}"
path="$(warden-secret-path "$name")"
exec cat -- "$path"
SCRIPT

chmod 0700 /home/wardenop/bin
chmod 0700 /home/wardenop/bin/warden-secret-*
sudo chown -R wardenop:wardenop /home/wardenop/bin

if ! grep -q '/home/wardenop/bin' /home/wardenop/.profile 2>/dev/null; then
  cat >> /home/wardenop/.profile <<'SCRIPT'

if [ -d "$HOME/bin" ]; then
  PATH="$HOME/bin:$PATH"
fi
SCRIPT
fi

echo "devstation_secret_helpers=installed"
ls -ld /run/warden-secrets /home/wardenop/bin
sudo -n -u wardenop bash -lc 'command -v warden-secret-list && warden-secret-list'
