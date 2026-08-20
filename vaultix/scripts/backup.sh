#!/bin/bash
# SME backup: dump Postgres. The encryption key is NOT in the dump.
# Restore without /opt/vaultix/secrets/backend.env ENCRYPTION_KEY = unreadable secrets.
set -euo pipefail
DEST="${VAULTIX_BACKUP_DIR:-/opt/vaultix/backups}"
install -d -m 0700 "$DEST"
STAMP=$(date -u +%Y%m%dT%H%M%SZ)
# shellcheck disable=SC1091
set -a
source /opt/vaultix/secrets/postgres.env
set +a
podman exec vaultix_db_1 pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB" \
  | gzip -c > "$DEST/vaultix-${STAMP}.sql.gz"
chmod 600 "$DEST/vaultix-${STAMP}.sql.gz"
# keep 14 days
find "$DEST" -name 'vaultix-*.sql.gz' -mtime +14 -delete
echo "wrote $DEST/vaultix-${STAMP}.sql.gz"
