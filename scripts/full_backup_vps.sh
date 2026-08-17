#!/usr/bin/env bash
# Pull production site + database dump from VPS to this PC.
# Usage: ./scripts/full_backup_vps.sh
# Output: ~/backups/mygigguide-vps/YYYY-MM-DD_HHMMSS/

set -euo pipefail

VPS_USER="${VPS_USER:-dave}"
VPS_HOST="${VPS_HOST:-mel55-nix02}"
REMOTE_APP="${REMOTE_APP:-/var/www/mygigguide}"
STAMP="$(date +%Y-%m-%d_%H%M%S)"
DEST="${HOME}/backups/mygigguide-vps/${STAMP}"
mkdir -p "$DEST"

echo "==> VPS backup from ${VPS_USER}@${VPS_HOST}:${REMOTE_APP}"
echo "    Saving to $DEST"

rsync -avz --no-group --no-owner \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/logs' \
  --exclude='storage/framework/cache' \
  --exclude='storage/framework/sessions' \
  --exclude='storage/framework/views' \
  "${VPS_USER}@${VPS_HOST}:${REMOTE_APP}/" \
  "$DEST/site/"

echo "==> Database dump (requires .env DB_* on VPS)"
DUMP_REMOTE="/tmp/mygigguide_db_${STAMP}.sql"
if ssh "${VPS_USER}@${VPS_HOST}" "cd ${REMOTE_APP} && test -f .env && grep -q '^DB_CONNECTION=mysql' .env"; then
  ssh "${VPS_USER}@${VPS_HOST}" bash <<REMOTE
set -euo pipefail
cd ${REMOTE_APP}
set -a
source <(grep -E '^DB_' .env | sed 's/\r$//')
set +a
mysqldump -h"\${DB_HOST:-127.0.0.1}" -u"\${DB_USERNAME}" -p"\${DB_PASSWORD}" "\${DB_DATABASE}" > "${DUMP_REMOTE}"
REMOTE
  scp "${VPS_USER}@${VPS_HOST}:${DUMP_REMOTE}" "$DEST/database.sql"
  ssh "${VPS_USER}@${VPS_HOST}" "rm -f ${DUMP_REMOTE}" || true
  echo "    database.sql saved"
else
  echo "    (skipped — no mysql .env on VPS or SSH failed)"
fi

echo ""
echo "Done. Backup folder:"
ls -lah "$DEST"
