#!/usr/bin/env bash
# Full local backup — Laravel site on this PC.
# Usage: ./scripts/full_backup_local.sh
# Output: ~/backups/mygigguide/YYYY-MM-DD_HHMMSS/

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
STAMP="$(date +%Y-%m-%d_%H%M%S)"
DEST="${HOME}/backups/mygigguide/${STAMP}"
mkdir -p "$DEST"

echo "==> Backing up Laravel project to $DEST"

tar -czf "$DEST/laravel-code.tar.gz" \
  --exclude='vendor' \
  --exclude='node_modules' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='.git' \
  -C "$(dirname "$ROOT")" "$(basename "$ROOT")"

if [ -f "$ROOT/.env" ]; then
  cp "$ROOT/.env" "$DEST/.env.backup"
  echo "    Copied .env (keep private — do not upload to GitHub)"
fi

if command -v mysqldump >/dev/null 2>&1 && [ -f "$ROOT/.env" ]; then
  DB_URL="$(grep -E '^DB_' "$ROOT/.env" | head -5 || true)"
  if grep -q '^DB_CONNECTION=mysql' "$ROOT/.env" 2>/dev/null; then
    echo "    Tip: run mysqldump manually if you use local MySQL (see full_backup_vps.sh for VPS DB)"
  fi
fi

echo ""
echo "Done."
echo "  $DEST/laravel-code.tar.gz"
ls -lh "$DEST"
