#!/usr/bin/env bash
# Quicket expand — run on PC (prompts for SSH password). Baby-step friendly.
# Usage: bash scripts/vps_quicket_expand.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOST="${VPS_HOST:-dave@mel55-nix02}"
APP="${VPS_APP:-/var/www/mygigguide}"
TMP="/tmp/quicket-expand-$$"

echo "==> 1/6 Rsync Quicket files to $HOST:$TMP"
ssh "$HOST" "mkdir -p '$TMP'"
rsync -avz --no-group --no-owner \
  "$ROOT/app/Services/Quicket/QuicketImportService.php" \
  "$ROOT/app/Console/Commands/QuicketPullCommand.php" \
  "$ROOT/app/Console/Commands/QuicketBackfillCategoriesCommand.php" \
  "$ROOT/config/quicket.php" \
  "$ROOT/database/seeders/CategorySeeder.php" \
  "$ROOT/routes/console.php" \
  "$ROOT/docs/QUICKET_IMPORT.md" \
  "$HOST:$TMP/"

echo "==> 2/6 Install into app tree (may ask sudo password)"
ssh -t "$HOST" "sudo cp -v \
  '$TMP/QuicketImportService.php' '$APP/app/Services/Quicket/' \
  && sudo cp -v '$TMP/QuicketPullCommand.php' '$TMP/QuicketBackfillCategoriesCommand.php' '$APP/app/Console/Commands/' \
  && sudo cp -v '$TMP/quicket.php' '$APP/config/' \
  && sudo cp -v '$TMP/CategorySeeder.php' '$APP/database/seeders/' \
  && sudo cp -v '$TMP/console.php' '$APP/routes/' \
  && sudo cp -v '$TMP/QUICKET_IMPORT.md' '$APP/docs/' \
  && sudo chown dave:www-data \
    '$APP/app/Services/Quicket/QuicketImportService.php' \
    '$APP/app/Console/Commands/QuicketPullCommand.php' \
    '$APP/app/Console/Commands/QuicketBackfillCategoriesCommand.php' \
    '$APP/config/quicket.php' \
    '$APP/database/seeders/CategorySeeder.php' \
    '$APP/routes/console.php' \
    '$APP/docs/QUICKET_IMPORT.md'"

echo "==> 3/6 Provinces + config clear + categories"
ssh -t "$HOST" "cd '$APP' && \
  grep -q '^QUICKET_PROVINCES=' .env \
    && sed -i 's/^QUICKET_PROVINCES=.*/QUICKET_PROVINCES=\"Gauteng,Western Cape,KwaZulu-Natal\"/' .env \
    || echo 'QUICKET_PROVINCES=\"Gauteng,Western Cape,KwaZulu-Natal\"' >> .env && \
  grep -q '^QUICKET_CATEGORIES=' .env || echo 'QUICKET_CATEGORIES=1' >> .env && \
  php artisan config:clear && \
  php artisan db:seed --class=CategorySeeder --force"

echo "==> 4/6 Cron + log file"
ssh -t "$HOST" "cd '$APP' && \
  touch storage/logs/quicket-pull.log && \
  (crontab -l 2>/dev/null | grep -F 'artisan schedule:run' >/dev/null) \
    || (crontab -l 2>/dev/null; echo '* * * * * cd /var/www/mygigguide && php artisan schedule:run >> /dev/null 2>&1') | crontab - && \
  php artisan schedule:list && \
  echo '--- crontab ---' && crontab -l | grep schedule"

echo "==> 5/6 Music dry-run page 1 (note pages count for seed)"
ssh -t "$HOST" "cd '$APP' && php artisan quicket:pull --page=1 --max-pages=1 --categories=1"

echo "==> 6/6 Sports + Travel dry-run (not applied, not cron)"
ssh -t "$HOST" "cd '$APP' && \
  php artisan quicket:pull --page=1 --max-pages=1 --categories=5 && \
  php artisan quicket:pull --page=1 --max-pages=1 --categories=6"

echo ""
echo "Done deploy/smoke. When Music dry-run looked OK, seed as dave:"
echo "  ssh $HOST"
echo "  cd $APP && php artisan quicket:pull --apply --page=1 --max-pages=NN --sleep=2"
echo "(NN = pages from dry-run; may take a while.)"
echo "Do NOT add categories 5/6 to QUICKET_CATEGORIES or cron yet."
