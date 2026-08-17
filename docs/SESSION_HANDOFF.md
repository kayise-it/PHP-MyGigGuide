# Session handoff — resume after reboot

**Updated:** 13 Aug 2026 — Station poll feature (server-side aggregate votes for VOW FM Radio Hub card)

Use this with **[personal.md](./personal.md)**, **[UPDATE_LOG.md](./UPDATE_LOG.md)** (progression history), and **[PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md)**.

**Prior chats:** `35475cb9-efa1-4988-8836-cf7d4dfda896` (Rise FM + VOW FM flavors, live session fix, venue coords).

---

## ▶ After reboot — pick up here

**Latest (13 Aug 2026 — Station Poll feature):**

| Area | What | Status |
|------|------|--------|
| **Laravel migrations** | `polls` table (context, question, options JSON, active, closes_at) + `poll_votes` table (poll_id, device_id, option_index, unique constraint) | **Local** — deploy below |
| **Laravel models** | `Poll` (scope `activeForContext`, `resultsArray()`, `totalVotes()`), `PollVote` | Done |
| **Laravel controller** | `PollController` — `GET /api/v1/polls/{context}` + `POST /api/v1/polls/{poll}/vote` | Done |
| **Laravel routes** | Added to public section of `routes/api.php` | Done |
| **Artisan command** | `php artisan poll:create` — interactive: context, question, options, closes_at | Done |
| **Tests** | `tests/Feature/Api/V1/PollApiTest.php` (8 tests) | Local |
| **Flutter API** | `LaravelApi.fetchActivePoll()` + `LaravelApi.submitPollVote()` in `laravel_api.dart` | Done |
| **Flutter DeviceIdService** | `lib/services/device_id_service.dart` — stable hex ID in SharedPreferences | Done |
| **Flutter widget** | `lib/widgets/station_poll_card.dart` — fetches poll, option buttons pre-vote, percent bars post-vote | Done |
| **Flutter wiring** | `radio_station_detail_screen.dart` — "This Week's Poll" section, hidden when no active poll | Done |
| **flutter analyze** | Clean (0 errors, pre-existing info hints only) | ✓ |

### Create first VOW FM poll (after deploy):

```bash
php artisan poll:create
# → Select: vowfm
# → Enter question + 2–4 options + closes_at (default: +7 days)
```

### Deploy poll feature to VPS:

```bash
cd ~/development/PHP-MyGigGuide
HOST=dave@mel55-nix02

# 1. Migrations (via /tmp — database/migrations not writable by dave direct rsync)
rsync -avz --no-group --no-owner \
  database/migrations/2026_08_13_080000_create_polls_table.php \
  database/migrations/2026_08_13_080100_create_poll_votes_table.php \
  "$HOST:/tmp/"

ssh "$HOST" 'sudo cp /tmp/2026_08_13_080000_create_polls_table.php /var/www/mygigguide/database/migrations/ && \
  sudo cp /tmp/2026_08_13_080100_create_poll_votes_table.php /var/www/mygigguide/database/migrations/ && \
  sudo chown www-data:www-data \
    /var/www/mygigguide/database/migrations/2026_08_13_080000_create_polls_table.php \
    /var/www/mygigguide/database/migrations/2026_08_13_080100_create_poll_votes_table.php'

# 2. Models + Controller + Command
rsync -avz --no-group --no-owner \
  app/Models/Poll.php \
  app/Models/PollVote.php \
  "$HOST:/var/www/mygigguide/app/Models/"

rsync -avz --no-group --no-owner \
  app/Http/Controllers/Api/V1/PollController.php \
  "$HOST:/var/www/mygigguide/app/Http/Controllers/Api/V1/"

rsync -avz --no-group --no-owner \
  app/Console/Commands/CreatePollCommand.php \
  "$HOST:/var/www/mygigguide/app/Console/Commands/"

rsync -avz --no-group --no-owner \
  routes/api.php \
  "$HOST:/var/www/mygigguide/routes/"

# 3. Run migrations (--path= only — safe on this VPS)
ssh "$HOST" "cd /var/www/mygigguide && \
  php artisan migrate --path=database/migrations/2026_08_13_080000_create_polls_table.php --force && \
  php artisan migrate --path=database/migrations/2026_08_13_080100_create_poll_votes_table.php --force && \
  php artisan route:clear && php artisan config:clear"
```

### Rebuild app (Flutter — vanilla MGG):

```bash
cd ~/development/mygigguide_app
./scripts/build_apk.sh mygigguide --label=station-poll
```

### Smoke test:

```bash
# GET (no poll yet → 404)
curl -s https://www.mygigguide.co.za/api/v1/polls/vowfm | jq .

# Create poll on VPS
php artisan poll:create

# GET again → poll JSON with options + percentages
curl -s https://www.mygigguide.co.za/api/v1/polls/vowfm | jq .

# POST a vote
curl -s -X POST -H "Content-Type: application/json" \
  -d '{"device_id":"test-abc","option_index":0}' \
  https://www.mygigguide.co.za/api/v1/polls/POLL_ID/vote | jq .
```

---



| Area | What | Status |
|------|------|--------|
| **Claim notifications** | `ClaimNotificationService` — admin email (works now if `ARTIST_CLAIM_ADMIN_EMAIL` set) + WhatsApp stub (set 3 `WHATSAPP_*` vars on VPS to activate) | **Done** — deploy rsync below |
| **Rise FM parity** | Dark chrome, gold/red accent, news feed, social chips (FB/IG/YT), podcast, call studio, quality picker, "now on air", "Open in iono" | **Done** — Flutter |
| **Live session 422 fix** | Flutter fetches artist-level sessions; `laravel_api.dart` parses 422 body | **Done** — Flutter |
| **VOW FM flavor** | `vowfm` scaffold: gradle, `brand_config.dart`, iono stream 101, gold `#FFD100`, Wits navy `#003B5C`, WhatsApp `27745779461`, "Open in iono" chip | **Done** — Flutter; analyze clean |
| **Venue coord backfill** | Script to find venues missing lat/lng + Google Places lookup | **Script given** — Dave to run on VPS via Tinker heredoc |
| **Rise FM APK** | Needs rebuild after all Rise FM changes | **Dave builds:** `./scripts/build_apk.sh risefm --label=v2` |
| **VOW FM APK** | Ready to build; app icon needs a square PNG first | **Dave builds after icon:** `./scripts/build_apk.sh vowfm --label=v1` |
| **VOW FM icon** | `assets/images/brands/vowfm/app_icon.png` is a landscape JPEG — replace with square PNG ≥ 512×512 before `dart run flutter_launcher_icons -f flutter_launcher_icons-vowfm.yaml` | **Dave provides** |
| **HOT 1027 parity** | Dark chrome, red accent, iono stream 57, quality picker, social chips, WhatsApp `27834531027`, "Open in iono" | **Done** — Flutter; analyze clean |
| **HOT 1027 APK** | Assets already exist; check `app_icon.png` quality | **Dave builds:** `./scripts/build_apk.sh hot1027` |
| **Radio Hub (Home)** | "On Air" horizontal strip — Rise FM, VOW 88.1, HOT 102.7, 91.9 FM — in vanilla MGG home screen. Analyze clean. | **Dave builds:** `./scripts/build_apk.sh mygigguide` |
| **VOW FM logo** | PDF only in `assets/images/brands/vowfm/Provided/` — export horizontal logo PNG from PDF → drop into `assets/images/brands/vowfm/header_logo.png` | **Dave exports** |
| **Dave Walter (Rise FM)** | Meeting done; APK share requested | **Send latest risefm APK** |
| **Lundi / VOW FM** | Video call Tue 11 Aug | **Build vowfm APK to demo** |
| **Play Store** | MGG AAB + declarations | **Next priority after demos** |
| **VPS migrations** | `venue_owners` out of sync — use `--path=` only | **Parked** |

**Deploy note:** Never plain `migrate --force` on VPS until migrations table audited. Copy new files to correct folders (not just `routes/api.php`).

### Deploy station poll feature (13 Aug 2026)

```bash
cd ~/development/PHP-MyGigGuide
HOST=dave@mel55-nix02

# Rsync new files:
rsync -avz --no-group --no-owner \
  app/Models/Poll.php \
  app/Models/PollVote.php \
  "$HOST:/var/www/mygigguide/app/Models/"

rsync -avz --no-group --no-owner \
  app/Http/Controllers/Api/V1/PollController.php \
  "$HOST:/var/www/mygigguide/app/Http/Controllers/Api/V1/"

rsync -avz --no-group --no-owner \
  app/Console/Commands/CreatePollCommand.php \
  "$HOST:/var/www/mygigguide/app/Console/Commands/"

rsync -avz --no-group --no-owner \
  routes/api.php \
  "$HOST:/var/www/mygigguide/routes/"

# Migrations via /tmp (Quicket subfolder permission pattern):
rsync -avz --no-group --no-owner \
  database/migrations/2026_08_13_080000_create_polls_table.php \
  database/migrations/2026_08_13_080100_create_poll_votes_table.php \
  "$HOST:/tmp/"

ssh "$HOST" 'sudo cp /tmp/2026_08_13_080000_create_polls_table.php \
  /var/www/mygigguide/database/migrations/ && \
  sudo cp /tmp/2026_08_13_080100_create_poll_votes_table.php \
  /var/www/mygigguide/database/migrations/ && \
  sudo chown www-data:www-data \
    /var/www/mygigguide/database/migrations/2026_08_13_080000_create_polls_table.php \
    /var/www/mygigguide/database/migrations/2026_08_13_080100_create_poll_votes_table.php'
```

Then on VPS:
```bash
cd /var/www/mygigguide

php artisan migrate --path=database/migrations/2026_08_13_080000_create_polls_table.php --force
php artisan migrate --path=database/migrations/2026_08_13_080100_create_poll_votes_table.php --force
php artisan route:clear && php artisan config:cache

# Create first VOW FM poll:
php artisan poll:create
```

**Flutter rebuild after deploy:**
```bash
cd ~/development/mygigguide_app && ./scripts/build_apk.sh mygigguide
```

### Deploy event creation alerts + Quicket system user (8 Aug 2026)

```bash
cd ~/development/PHP-MyGigGuide
HOST=dave@mel55-nix02

rsync -avz --no-group --no-owner \
  app/Services/EventNotificationService.php \
  app/Services/EventCreationService.php \
  app/Services/Quicket/QuicketImportService.php \
  "$HOST:/var/www/mygigguide/app/Services/"

rsync -avz --no-group --no-owner \
  config/services.php config/quicket.php \
  "$HOST:/var/www/mygigguide/config/"

rsync -avz --no-group --no-owner \
  database/seeders/SystemUserSeeder.php \
  database/seeders/DatabaseSeeder.php \
  "$HOST:/var/www/mygigguide/database/seeders/"
```

Then on VPS:
```bash
cd /var/www/mygigguide

# 1. Create Quicket system user — note the printed ID:
php artisan db:seed --class=SystemUserSeeder

# 2. Add to .env (replace X with printed ID, Y with Dave's own user ID):
#    QUICKET_OWNER_USER_ID=X
#    EVENT_NOTIFY_EXCLUDE_USER_IDS=Y,X

# 3. Clear config cache:
php artisan config:cache
```

To find Dave's own user ID:
```bash
php artisan tinker --execute="echo App\Models\User::where('email','davewelmans@gmail.com')->value('id');"
```

### Deploy claim alerts (5 Aug 2026)

```bash
cd ~/development/PHP-MyGigGuide
HOST=dave@mel55-nix02

rsync -avz --no-group --no-owner \
  app/Services/ClaimNotificationService.php \
  app/Services/ClaimService.php \
  "$HOST:/var/www/mygigguide/app/Services/"

rsync -avz --no-group --no-owner \
  config/services.php \
  "$HOST:/var/www/mygigguide/config/"

ssh "$HOST" "cd /var/www/mygigguide && php artisan config:cache"
```

Then add to VPS `.env` (email already works — just add the Evolution API vars):
```
EVOLUTION_API_URL=http://localhost:8080    # your Evolution API host + port
EVOLUTION_INSTANCE=default                # instance name shown in Evolution dashboard
EVOLUTION_API_KEY=                        # global API key from Evolution config
EVOLUTION_ADMIN_NUMBER=27XXXXXXXXX        # your WhatsApp number in E.164
```
Run `php artisan config:clear` after editing `.env`.

**Say in new chat:** `Continue from SESSION_HANDOFF.md` + read `PRODUCT_ROADMAP.md`.

---

## ▶ Earlier — Play AD_ID / Facebook manifest fix (23 Jul 2026)

| Area | What | Status |
|------|------|--------|
| **Play error** | Declaration **No** for advertising ID, but `flutter_facebook_auth` merged `ACCESS_ADSERVICES_*` permissions | **Fixed locally** |
| **Manifest** | Strip all Facebook ad/attribution permissions + remove FacebookInitProvider / activities / meta-data | **Local done** (`mygigguide_app`) |
| **Play Console** | **Advertising ID → No** for **each** listing (MGG + 919 FM) | **Dave done** — keep No |
| **Facebook Login** | Disabled in this build (was placeholder ids anyway); Google/Apple/email OK | As intended |
| **Share on Facebook** | Detail “Share” opens web sharer — **not** the SDK | Unchanged |

### Build Play AABs (PC — upload after manifest fix)

```bash
cd ~/development/mygigguide_app

./scripts/build_aab.sh mygigguide --label=play-no-ad-id
./scripts/build_aab.sh fm919 --label=play-no-ad-id
```

**Vanilla MGG:** `android/app/src/mygigguide/AndroidManifest.xml` strips radio foreground-service permissions (no Radio tab). **919 FM** keeps them — use the live-stream declaration there only.

**Play Console — foreground service (overdue):**

| App | Declaration |
|-----|-------------|
| **919 FM** | **Media playback** — “Live FM radio stream…” (see chat) |
| **My Gig Guide** | After new AAB: permission removed — choose **remove from app** / skip media declaration if Play allows |

Output: `build/app/outputs/bundle/mygigguideRelease/` and `fm919Release/` — upload **new** AABs; old artifacts may keep showing the error until replaced.

**Verify merged manifest (optional, after build):**

```bash
grep -E 'AD_ID|ACCESS_ADSERVICES|facebook' \
  build/app/intermediates/merged_manifest/mygigguideRelease/processMygigguideReleaseMainManifest/AndroidManifest.xml \
  && echo "FAIL — still present" || echo "OK — clean"
```

**Earlier today (23 Jul 2026 — home gallery sort + week window + Quicket cron):**

| Area | What | Status |
|------|------|--------|
| **Gallery sort** | Home coverflow always **chronological** (app: near-me filters radius only, no distance sort) | **Local done** — rebuild APK |
| **Week window (web)** | Hero query limit raised (60→500 for Week); inclusive 7-day window fixed | **Local done** — rsync `HomeController.php` + `npm run build` |
| **Week window (app)** | Already fetches up to 10×100 API pages; OK for ~453/week national | No code change |
| **Quicket cron** | 3 new overnight = normal delta; gaps = category 64/6 not on cron, province allowlist, page-3 cap | **Explained** — see Quicket block below |

### Deploy web gallery fix (PC → VPS)

```bash
cd ~/development/PHP-MyGigGuide
HOST=dave@mel55-nix02

rsync -avz --no-group --no-owner \
  app/Http/Controllers/HomeController.php \
  "$HOST:/tmp/"

ssh "$HOST" 'sudo cp /tmp/HomeController.php /var/www/mygigguide/app/Http/Controllers/ && \
  sudo chown www-data:www-data /var/www/mygigguide/app/Http/Controllers/HomeController.php && \
  cd /var/www/mygigguide && php artisan view:clear'
```

Then on VPS if front-end assets stale: `npm run build` (or rsync `public/build` from PC).

### App rebuild (gallery sort)

```bash
cd ~/development/mygigguide_app
./scripts/build_apk.sh risefm --label=gallery-chrono
```

(or `mygigguide` if testing vanilla)

### Quicket — why only 3 new + missing events

**Normal:** Nightly cron walks **first 3 pages** per category (Music 1, Sports 5, Family 30, Arts 9). After seed, almost everything is **duplicate** — **3 created** is expected.

**Not on cron (manual seed only):** Quicket **Other (64)**, **Travel (6)**. Many cabaret/comedy listings are tagged Other.

**Province filter:** `QUICKET_PROVINCES` = Gauteng, WC, KZN, Free State, EC only — **Mpumalanga / Limpopo / etc. skipped**.

**Check this morning's logs (VPS):**

```bash
cd /var/www/mygigguide
tail -30 storage/logs/quicket-pull-music.log
tail -30 storage/logs/quicket-pull-sports.log
tail -30 storage/logs/quicket-pull-family.log
tail -30 storage/logs/quicket-pull-arts.log
```

Look for **Created** vs **Duplicates** in the Totals table.

**One example URL helps** if you want to confirm which bucket (wrong category vs province vs already imported). Not required for the general diagnosis above.

**One-off pull for a missing listing (dry-run first):**

```bash
php artisan quicket:pull --page=1 --max-pages=12 --categories=64
php artisan quicket:pull --apply --page=1 --max-pages=12 --sleep=2 --categories=64
```

**Earlier (22 Jul 2026 — Quicket clear-cards + letterbox restore):**

| Area | What | Status |
|------|------|--------|
| **Intent** | Clear **all** Quicket `poster_card`s → API `poster_card_url` null → app shows full poster with **contain** letterbox. Leave non-Quicket user-upload cards alone. | **Ready for Dave** |
| **Laravel** | `--clear-cards` / `--clear-landscape-cards`; `clearQuicketPosterCards`; import skips landscape invent-crops | **Local done** — deploy + run clear |
| **Flutter** | `EventPosterImage` `useListThumb`: contain+mat when no card, cover when card. Diary/strip same via `fit` | **Local done** — rebuild APK |
| **APK** | `./scripts/build_apk.sh risefm --label=letterbox-posters` | **Dave runs** |

### 1) PC → VPS (rsync to `/tmp`, then sudo cp)

```bash
cd ~/development/PHP-MyGigGuide
HOST=dave@mel55-nix02

rsync -avz --no-group --no-owner \
  app/Services/Quicket/QuicketImportService.php \
  app/Services/Quicket/QuicketPosterService.php \
  "$HOST:/tmp/"

rsync -avz --no-group --no-owner \
  app/Console/Commands/QuicketBackfillPostersCommand.php \
  "$HOST:/tmp/"

ssh "$HOST" 'sudo cp /tmp/QuicketImportService.php /var/www/mygigguide/app/Services/Quicket/ && \
  sudo cp /tmp/QuicketPosterService.php /var/www/mygigguide/app/Services/Quicket/ && \
  sudo cp /tmp/QuicketBackfillPostersCommand.php /var/www/mygigguide/app/Console/Commands/ && \
  sudo chown www-data:www-data \
    /var/www/mygigguide/app/Services/Quicket/QuicketImportService.php \
    /var/www/mygigguide/app/Services/Quicket/QuicketPosterService.php \
    /var/www/mygigguide/app/Console/Commands/QuicketBackfillPostersCommand.php'
```

### 2) VPS — dry-run then apply (clear ALL Quicket poster_cards)

```bash
cd /var/www/mygigguide
php artisan quicket:backfill-posters --clear-cards --limit=5000
php artisan quicket:backfill-posters --clear-cards --apply --limit=5000
```

Success: dry-run **Would clear** > 0 (or 0 if already cleared); APPLY **Cleared** matches. Non-Quicket events untouched.

### 3) Rebuild Rise APK (on PC)

```bash
cd ~/development/mygigguide_app
./scripts/build_apk.sh risefm --label=letterbox-posters
```

**Earlier (22 Jul 2026 — Rise schedule UX + Quicket “small landscape” jump):**

| Area | What | Status |
|------|------|--------|
| **Rise Hosts screen** | Day chips Mon–Sun + show rows (time / title / host / schedule-card photo) — matches site; presenters section only has ~4 headshots | **Done** (Flutter) |
| **Rise photos** | Parser reads images from `.show-box` schedule cards (not only “Our Radio Presenters”) | **Done** |
| **Quicket jump** | **Bug (Flutter):** coverflow used full `poster_url` + `contain` → letterboxed landscape; diary used `posterUrls` not card. API `poster_card_url` was fine | **Superseded** by clear-cards + letterbox restore above |
| **APK** | `./scripts/build_apk.sh risefm --label=schedule-fix` | Older label |

**Earlier (22 Jul 2026 — Rise FM app: presenters, schedule, poll):**

| Area | What | Status |
|------|------|--------|
| **Presenter photos** | `RiseFmSiteRepository` scrapes risefm.co.za presenters; merges photos into `risefm_hosts_catalog` | Superseded by day schedule UI |
| **Today on Rise** | Weekday schedule strip on Radio tab from parsed homepage grid | **Done** (Flutter) + thumbs |
| **Local poll** | `RiseDemoPollScreen` — SharedPreferences, no server | **Done** (Flutter) |
| **APK** | `./scripts/build_apk.sh risefm --label=presenters-poll` | Older label |

**Earlier (22 Jul 2026 — Quicket landscape cards + Rise UI) — superseded by clear-cards restore:**

| Area | What | Status |
|------|------|--------|
| **Quicket import** | Portrait-only honest 2:3 cards; landscape banners stay poster-only | **Local done** |
| **Backfill** | `--cards-only` skips landscapes; prefer `--clear-cards` to restore letterbox lists | See latest block above |
| **Rise light accents** | Yellow-on-white → muted red / near-black on light chrome only | **Done** (Flutter) |
| **Gallery bg** | Soft grey `#E8E8E8` on light brands; black on dark chrome | **Done** (Flutter coverflow + fullscreen) |

**Today (21 Jul 2026) — todo list:**

| # | Task | Notes |
|---|------|--------|
| 1 | **Rise FM flavour + app** | **Flutter scaffold done (local)** — `BRAND=risefm`, Radio tab, iono stream, Mbombela map. Dave builds APK. Play Store / Laravel tenant **not** done |
| 2 | **Fix location length issue** | App or API — venue/address field truncating? (confirm where it shows wrong) |
| 3 | **Fix Quicket cron feed** | **Local done (21 Jul)** — map `9`/`64` → `theatre`; Arts & Culture cron **04:05**; Dave one-off `--categories=64` (12 pages) + seed `9` on VPS |
| 4 | **AMFI category?** | MGG category for AMFI partner listings — scope TBD |
| 5 | **Mix FM flavour** | Same pattern as 919 FM / Rogues / Rise — `BRAND=mixfm` scaffold |
| 6 | **Google Docs/Sheets + Cursor** | Explore workflow; keep `SESSION_HANDOFF.md` as agent source of truth |
| 7 | **My pages alerts** | **Parked** — logged in [NOTIFICATIONS_PLAN.md](./NOTIFICATIONS_PLAN.md) § “My pages” |

**Rise FM — next (tight, no subdomain yet):**

| Item | Approach |
|------|----------|
| **Day schedule UI** | **Done** — Hosts → Mon–Sun chips + show rows; photos from schedule cards |
| **Today on Rise** | **Done** — Radio tab strip + thumbs |
| **Local demo poll** | **Done** — `RiseDemoPollScreen` (SharedPreferences); competitions still → browser |
| **Defer** | `risefm.mygigguide.co.za` Laravel tenant until partner sign-off |
| **API** | Stay on `www.mygigguide.co.za` (already in `BrandConfig`) |

**Latest (21 Jul 2026 — Rise FM Flutter scaffold):**

| Area | What | Status |
|------|------|--------|
| **Flavor** | Gradle `risefm` · `applicationId` `za.co.mygigguide.risefm` | **Done** (Flutter repo) |
| **Brand** | Title RISE fm · accent `#EC1C24` · Radio tab · hosts catalog · WhatsApp `27728857702` | **Done** |
| **Stream** | iono AAC `https://edge.iono.fm/xice/73_medium.aac` (same pattern as 919 `112`) | **Done** |
| **Map** | Default centre Mbombela `-25.4753, 30.9694` | **Done** |
| **SITE_URL** | `https://www.mygigguide.co.za` until Rise tenant DNS | **As decided** |
| **Build** | `./scripts/build_apk.sh risefm --label=v1` | **Dave runs** |
| **Not done** | Play Store listing · Laravel web tenant · Mix FM | Parked |

**Quicket cron note (21 Jul):** Cron is fine for Music/Sports/Family. Gaps were **Other (64)** and **Arts & Culture (9)** — both map to `theatre` + `quicket`. Example [385036](https://www.quicket.co.za/events/385036-oui-spill-the-rooibus-tea-a-cabaret-matine/) — tagged Other, not Music. **Local:** fourth cron job Arts & Culture `9` at **04:05** SAST → `quicket-pull-arts.log`. **VPS:** rsync `config/quicket.php` + `routes/console.php`; one-off `64` (12 pages); dry-run + seed `9`; `64` not on cron yet.

**Latest (21 Jul 2026 — Quicket Arts & Culture + Other map):**

| Area | What | Status |
|------|------|--------|
| **Category map** | Quicket `9` + `64` → `theatre`, `quicket` | **Local done** — rsync `config/quicket.php` |
| **Cron** | Arts & Culture `9` at **04:05** SAST → `quicket-pull-arts.log` | **Local done** — rsync `routes/console.php` |
| **One-off 64** | `--apply --categories=64 --max-pages=12` | **Dave on VPS** |
| **Seed 9** | dry-run → `--max-pages=NN` | **Dave on VPS** |
| **Cron 64** | Not added yet | Parked |

**Latest (20 Jul 2026 — Quicket nightly cron expand):**

| Area | What | Status |
|------|------|--------|
| **Cron** | Three staggered jobs: Music `1` 03:20, Sports `5` 03:35, Family `30` 03:50 SAST | **Local done** — rsync `routes/console.php` to VPS |
| **Env default** | `QUICKET_CATEGORIES=1` for manual pulls (do not merge 1,5,30) | Keep as-is |
| **Provinces** | Gauteng, WC, KZN, Free State, Eastern Cape via `QUICKET_PROVINCES` | Cron inherits; no re-seed needed for cron |
| **Logs** | Separate: `quicket-pull-music/sports/family.log` | After deploy |
| **RainLoop** | PHP-FPM write paths | **Fixed** earlier today |
| **Bandsintown / FIXR** | Partner tokens | Parked |

**Dave must run (SSH):** rsync `routes/console.php` → `config:clear` / `route:clear` if needed → `schedule:list` → confirm crontab still runs `schedule:run`.

**Next when SSH works:**
1. Deploy cron schedule (paste block in latest chat)
2. Confirm `schedule:list` shows three Quicket jobs
3. Parked: Bandsintown / FIXR / Spotify · Travel (`6`) still manual-only

**Earlier (20 Jul 2026 — Quicket expand 1/5/6):**

| Area | What | Status |
|------|------|--------|
| **Category map** | Quicket 1/5/6 → MGG; Sports/Travel dry-runs local OK | **Local done** (pre-Family) |
| **Provinces** | Gauteng + Western Cape + KwaZulu-Natal | Superseded by Free State/EC expand |
| **Quicket categories** | Dual-tag `live-music` + `quicket`; backfill 328 on VPS | **Done** |
| **Quicket seed** | Gauteng music pages 1–42 | **Done** (pre-province expand) |

**Earlier — Tomorrow — pick up here:**
1. ~~**Categorise Quicket imports**~~ **Done 20 Jul**
2. **Cron + province expand** — blocked on SSH; see paste block above.
3. Parked: Bandsintown partner `app_id`, FIXR token, Spotify scope.

**Earlier (18 Jul 2026):**

| Area | What | Status |
|------|------|--------|
| **Read image / Groq** | `VISION_MODELS = qwen/qwen3.6-27b` (llama-4-scout retired) | **Done** — bind-mounted `/home/limpho/miggs-bot` → `/app` |
| **Local source** | `~/local-agent-grok/groq_client.py` | Keep in sync when re-bundling |

**Earlier (26 Jun 2026 — break point):**

| Area | What | Status |
|------|------|--------|
| **Fav updates Phase 1** | Header 🔔 badge (new since last seen) + ❤️ saved + **Alerts** diary screen; Me tabs: Account \| Saved \| Gigs \| Pages \| Settings | **Coded on PC** — deploy API + rebuild APK |
| **Poster UX (app)** | Detail = full poster (`contain`); home coverflow = `contain`; list tiles use `poster_card_url`; Add event **Adjust crop** | **Tested OK** — APK `--label=poster-cards` (or `poster-contain`) |
| **Poster card (Laravel)** | `poster_card` migration + `EventPosterCardService`; API `poster_card_url` | **Deployed VPS** — migration via `/tmp` copy; rsync PHP + blade |
| **Near me radius (app)** | In same APK as poster work | **Coded** — retest when back if not smoke-tested |
| **Posted-by / owner (Laravel)** | Phase B web + API + app | **Coded** — rsync if not all paths on VPS |
| **Next when ready** | 919fm **web subdomain** · Play closed test AAB · npm `install`+`build` on VPS if web Firebase assets needed |

**Deploy note (Jun 2026):** Migration file → rsync to `/tmp/` then `sudo cp` into `database/migrations/` (folder not writable by `dave` direct rsync). `npm run build` on VPS needs `npm install` first (Firebase in `package.json`).

**Deploy fav updates Phase 1 (API only — no migration):**
```bash
cd ~/development/PHP-MyGigGuide
rsync -avz --no-group --no-owner app/Services/ApiFavoriteUpdatesService.php dave@mel55-nix02:/var/www/mygigguide/app/Services/
rsync -avz --no-group --no-owner app/Http/Controllers/Api/V1/MeController.php dave@mel55-nix02:/var/www/mygigguide/app/Http/Controllers/Api/V1/
rsync -avz --no-group --no-owner routes/api.php dave@mel55-nix02:/var/www/mygigguide/routes/
ssh dave@mel55-nix02 "cd /var/www/mygigguide && php artisan route:clear && php artisan config:clear"
```
**App rebuild:** `./scripts/build_apk.sh mygigguide --label=fav-updates`

**Smoke:** log in on phone with saved artist/venue → home banner **New for your saves**; Me → Favs → **Upcoming for your saves**.

**Smoke-tested:** Event **2975** — curl shows `poster_url` + `poster_card_url`; app detail/fullscreen = full poster after correct APK reinstall.

**Add event Phase A (Jun 2026):** Numbered steps 1–5 (poster → description/read → when → who/where → finish); gallery at bottom; scroll to top after post + return from event detail. Rebuild APK to test.

**Earlier (19 May 2026 — same session):**

| Area | What | Status |
|------|------|--------|
| **Poster UX (app)** | Event detail hero = **full poster** (`BoxFit.contain`). Home coverflow = **contain**. Diary cards use **`poster_card_url`** when API provides it. Add event: optional **Adjust crop** (`image_cropper`). | **Coded on PC** — rebuild APK |
| **Poster card (Laravel)** | On upload, auto-generates **`poster_card`** (2:3 center crop) → `poster_card_url` in API; www home coverflow uses card + `object-contain` | **Coded** — migrate + rsync |
| **Near me radius (app)** | Map/Events no longer fall back to all gigs when filter empty; rows without venue coords excluded from radius | **Coded on PC** — rebuild APK |
| **Posted-by / owner (Laravel)** | `EventCreationService` + legacy scope for mis-filed `owner_id`; Phase B “Gigs they posted” web + API + app | **Coded** — rsync if not on VPS |
| **Ticket URL (app)** | Add/edit event sends `ticket_url`; Read image parses ticket link variants | **Coded on PC** |
| **919 FM app parity** | Radio tab, Post a gig in Settings, `./scripts/build_aab.sh fm919` | **Coded on PC** |
| **Next** | 919fm **web subdomain** (`919fm.mygigguide.co.za`) — [WEB_APP_ALIGNMENT.md](./WEB_APP_ALIGNMENT.md) Phase 2 |

**Earlier (19 May 2026):** **Phase B notifications plan** — **“Gigs they posted”** on artist + organiser web pages; `posted_events` on `GET /api/v1/artists/{id}`; Flutter artist detail section. Trait: `ResolvesPostedEvents`. Component: `page-posted-events`. **Deploy:** rsync paths below + `php artisan view:clear`; app rebuild for Flutter.

**919 FM app (May 2026):** Same Dart tree as MGG closed test — **Radio** tab (4th), **Post a gig** under Settings, live stream, yellow dark chrome. Build: `./scripts/build_aab.sh fm919` with `SITE_URL=www` until `919fm.mygigguide.co.za` live. **Next:** Phase 2 web tenant (SiteBrand + nginx) — [WEB_APP_ALIGNMENT.md](./WEB_APP_ALIGNMENT.md).

### Done today (19 May 2026) — poster + Near me + posted-by

**Flutter (`mygigguide_app`):** Event detail **full poster** (`detail_helpers.dart`, `event_detail_screen.dart`); optional **Adjust crop** on Add event (`poster_crop_service.dart`, `image_cropper`, UCrop in manifest); **Near me radius** fix (`map_event.dart`, `event_geo.dart`, `home_map_screen.dart`, `events_directory_screen.dart`, `playing_soon_from_events.dart`); Phase B links + **ticket URL** on create/edit; 919 FM build scripts.

**Rebuild:** `./scripts/build_apk.sh mygigguide --label=poster-contain`

**Laravel:** Posted-by owner fix + “Gigs they posted” web/API (`EventCreationService.php`, `ResolvesPostedEvents.php`, artist/organiser views). Rsync if not on VPS.

**Parked (poster):** ~~server-side portrait thumb~~ **done** (`EventPosterCardService`); ~~contain on coverflow cards~~ **done** (app + www).

**Milestone (3 Jun 2026):** **Web Google sign-in** aligned with mobile app — Firebase on `/login` + `/register`, deferred email verify (first session OK, verify before next login), signup always `user` role. **Tested OK** on www + Rogues. Deploy: rsync + `npm run build` + migration `last_login_at` + `FIREBASE_*` in `.env`. Cosmetic auth polish **pinned for later**.

**Previous (27 May 2026):** **Read image** on Add event — app → Laravel `POST /api/v1/events/parse-poster` → miggs-bridge → Groq. **Rogues** same API. Privacy policy for Play.

**Google Play:** **Pinned (May 2026)** — personal vs Kee Consulting (D-U-N-S); resume Phase E in `mygigguide_app/docs/GOOGLE_PLAY_RELEASE.md`. **Not blocking app work.**

**Still open:**

1. ~~**Mail**~~ **Done (May 2026):** RainLoop send; `privacy@` alias OK. **Pinned:** `/admin/mail-accounts` — `docs/MAIL_ADMIN_DB_SETUP.md`.
2. **Web ↔ app home alignment** — plan in **[WEB_APP_ALIGNMENT.md](./WEB_APP_ALIGNMENT.md)** (Schedule/Map hero, Artists/Venues toggle, then 919 subdomain).
3. **Deploy** latest Laravel commit to VPS if not rsync'd after `git push` (auth milestone now on GitHub after this commit).
4. **Flutter app** — no git repo on laptop yet; APK from `~/development/mygigguide_app`.
5. **Auth cosmetic polish** — dashboard verify banner, logged-in users redirect off `/login`; optional Rogues-aware SSO URL + `SESSION_DOMAIN=.mygigguide.co.za`.

**VPS bridge note:** `whatsapp_bridge_server.py` + `wa_command_router.py` in Docker for `/app/parse-poster`.

---

## ▶ Web Google auth (Jun 2026) — deployed + tested

| Item | Detail |
|------|--------|
| **Google on web** | Firebase JS → `POST /auth/firebase` → session cookie (same project as app) |
| **Signup** | Always `user` role; auto-login; verify email before **next** sign-in |
| **Rogues** | Same auth pages; hostname branding only |
| **VPS** | `FIREBASE_WEB_API_KEY` + optional `FIREBASE_*`; Firebase Console authorized domains |
| **Build** | `npm run build` on VPS (or rsync `public/build` from PC) |

**Deploy (PC → VPS):** see [DEPLOY_VPS.md](./DEPLOY_VPS.md). After rsync: `php artisan migrate --force`, `npm run build`, `php artisan config:clear`.

---

## ▶ Latest — mobile dark theme (MGG + Rogues fixes)

**My Gig Guide (`mygigguide` flavor):** always-dark minimalist UI · **indigo** `#6366F1` · scaffold **`#000000`** · cards **`#12121A`** · **black** iTunes coverflow stage · dark bottom nav · **`brandAccent`** on detail/ratings (legible).

**Rogues:** dark bottom nav; detail/ratings use **cyan** `brandAccent` (not slate `brandPurple`).

**Key Flutter files:** `lib/main.dart`, `lib/brand_config.dart`, `lib/widgets/brand_logo_header.dart`, `lib/widgets/home_events_coverflow.dart`, `lib/app_shell.dart`, detail screens + `detail_rating_section.dart`.

**Logo glow:**

| `HEADER_LOGO_GLOW` | What you see |
|---------------------|--------------|
| **`both` (default)** | Rounded **`header_logo_glow.png`** + Flutter shadow |
| `baked` | Glow PNG only |
| `code` | Plain PNG + shadow |
| `plain` | No glow |

**Asset:** `mygigguide_app/assets/images/brands/mygigguide/header_logo_glow.png`

**Rebuild:**
```bash
cd ~/development/mygigguide_app
./scripts/build_apk.sh mygigguide --label=logo-round
```

**Rogues rebuild (when milestone):** `./scripts/build_apk.sh rogues`

---

## 📌 Pinned — glow logo → favicon + app icon (Dave — use later)

**Decision:** Reuse **`header_logo_glow.png`** (rounded mark + indigo backlight) for:

| Use | Where | Status |
|-----|--------|--------|
| **Browser tab (favicon)** | Website `www.mygigguide.co.za` | **Later** — after web dark theme or when refreshing brand |
| **App launcher icon** | Android / iOS home screen | **Pinned** — before store listing / next icon refresh |

**Today:** www favicon is still `public/logos/logo1.jpeg` via `SiteBrand::mygigguide()->faviconPath`. App launcher still `app_icon.png` (old art, blue adaptive background).

**When doing favicon (Laravel):**

1. Export square PNGs from glow asset (e.g. 32×32, 180×180 apple-touch) → `public/logos/mgg-favicon.png` (or similar).
2. Update `app/Support/SiteBrand.php` → `faviconPath` for `mygigguide()`.
3. Optional: replace root `public/favicon.ico` for error pages (404/500 still hardcode `/favicon.ico`).

**When doing app icon (Flutter):**

1. Square canvas from `header_logo_glow.png` → `assets/images/brands/mygigguide/app_icon.png` (keep glow + padding; black or `#000000` background).
2. Update `flutter_launcher_icons-mygigguide.yaml` — set `adaptive_icon_background: "#000000"` (not old blue `#0D47A1`).
3. Run: `dart run flutter_launcher_icons -f flutter_launcher_icons-mygigguide.yaml` then `flutter clean` + rebuild APK.
4. Rogues flavor unchanged — separate icon.

**Do not** until Dave asks — asset generation can be scripted (same Python pipeline as header glow).

---

## 📌 Pinned — App analytics & user insights (later)

**Context (Jun 2026):** Dave is **only rolling out the vanilla `mygigguide` build** for now. Rogues / FM919 / HOT1027 analytics can wait until those flavors ship separately.

**What you have today (no extra build):**

| Source | Where | Useful for |
|--------|--------|------------|
| **Google Play Console** | play.google.com/console → `za.co.mygigguide.mygigguide_app` | Installs, active devices, crashes, version adoption — **after Play upload** |
| **Laravel admin** | Admin → Dashboard / Users | Registered users, `last_login_at`, content posted (web + app share one DB) |
| **OneSignal** | onesignal.com | Push opt-in count, notification opens — **not** in-app behaviour; one app id for all flavors today |

**Not in the app today:** Firebase Analytics, Crashlytics, screen funnels, or brand-split reporting. API login sends generic `device_name: mobile-app` (can’t split Rogues vs MGG in DB).

**Suggested phases (when Dave asks — smallest first):**

1. **Quick win — API attribution:** On login/register, send `device_name` like `mygigguide-android-149` (`BrandConfig.brandKey` + build). Query `personal_access_tokens` / admin for app vs web-ish tokens. Vanilla-only is fine at first.
2. **Play + stability:** Firebase **Crashlytics** + optional **Analytics** on the **vanilla** Firebase Android app — events e.g. `post_gig`, `claim_page`. Update **/popia** + Play Data safety form.
3. **White-label:** Separate Play listings per flavor → separate Play stats; separate OneSignal apps + Firebase apps per brand when Rogues/FM919 ship.
4. **Optional bigger scope:** Admin “app insights” dashboard or Laravel `app_events` table (POPIA-conscious). Kayise scope doc lists **Advanced user analytics** as optional add-on (~R2 500) — not started.

**Do not start** until vanilla store path is stable unless Dave explicitly prioritises.

---

## 📌 Pinned — edit own events in app (Dave)

**Status:** **API tested OK on live (May 2026)** — VPS deploy + curl create/edit on event **2491**; app edit UI needs new APK when USB/Drive ready.

**Goal:** Let users **edit gigs they posted** in the app — same idea as the website dashboard (`GET /events/{event}/edit`, `PUT` update).

| Layer | Done |
|-------|------|
| **Laravel (live)** | `PUT/PATCH /api/v1/events/{event}` — deployed VPS; `user_can_edit`; owner update + 401 for guest verified |
| **Flutter (PC)** | **Edit event** on event detail when `user_can_edit`; reuses add-event form — **not on phone yet** |
| **Tests** | `tests/Feature/Api/V1/EventUpdateApiTest.php` (local PHPUnit when test DB set up) |

**Optional cleanup:** delete or edit test gig https://www.mygigguide.co.za/events/2491 when done.

**App when ready:** `./scripts/build_apk.sh mygigguide --label=edit-event` → Drive install (no USB needed).

**Not in scope yet:** edit someone else’s event, admin-only fields, or full curation queue (see event curation).

---

## Say this in a new chat to resume

Copy-paste:

```text
Continue My Gig Guide work. Read @docs/SESSION_HANDOFF.md and @docs/personal.md first.
Repos: ~/development/PHP-MyGigGuide and ~/development/mygigguide_app.
```

Or shorter: **“Continue from SESSION_HANDOFF.md”**

---

## 🔴 Urgent / look at soon (Dave)

| Priority | Task | Notes |
|----------|------|--------|
| **🔥 Deploy** | **Claim alert notification** | Email + WhatsApp (Evolution API) on every claim. Working on VPS. |
| **🔥 Deploy** | **Event creation WhatsApp alert** | Crowd-sourced event posted → WhatsApp to Dave. Excludes Dave + Quicket bot. See deploy block below. |
| **🔥 Deploy** | **Quicket system user** | Run seeder → get ID → set `QUICKET_OWNER_USER_ID` + `EVENT_NOTIFY_EXCLUDE_USER_IDS` in VPS `.env`. |

| Priority | Task | Notes |
|----------|------|--------|
| **1 — Done** | **Identity (#7)** | Register/login on phone OK. |
| **2 — Done** | **Saved thumbnails (#7c)** | Deployed — `image_url` in API; photos show in Saved tab. |
| **3 — Done** | **Add event — gallery** | **Tested OK (19 May)** — sizes visible, post works, form clears. Any member can post (API deployed). |
| **3a — Done** | **Anyone can post (API)** | `EnsureApiPermission` deployed. |
| **3b — Done** | **Image sizes before post (app)** | `--label=gallery` APK on phone — confirmed. |
| **3c — Done** | **Clear form after post (app)** | Confirmed with gallery build. |
| **4 — Done** | **Ratings (#6)** | **Tested OK (19 May)** — compact card on detail; sign in to rate; public reviews. Deployed. |
| **4b — Coded** | **Browse sort — Top rated / Most events** | API + app chips done. **Deploy API rsync** + rebuild if not on phone yet. |
| **4c — Done** | **Dark theme (MGG)** | **Tested OK (23 May)** — indigo, black coverflow, flat cards, logo glow. PC code; rebuild APK to share. |
| **4d — Done** | **Rogues dark nav + detail legibility** | Same codebase; cyan accent on Rogues. Rebuild Rogues APK at next milestone. |
| **5 — When ready** | **Tester APKs on Drive** | Latest: dark + logo-round (+ browse sort when deployed). |
| **6 — Later** | **Event curation** | Approve/merge/spam — after crowd-source posting works for everyone. |
| **6a — Done (API live)** | **Edit own events** | VPS tested OK (May 2026). App **Edit event** button — rebuild APK when ready. |
| **7 — Done** | **App → website SSO** | `POST /me/web-session` + **Open on website** signed in. **Tested OK.** |
| **8 — Done** | **Glow logo → favicon + app icon** | Gorilla badge; favicons + launcher regenerated. Deploy favicons to VPS when ready. |
| **9 — Pinned** | **Website dark theme** | ~**2–3 weeks** public pages (Tailwind sweep); admin can stay light. Not blocking app launch. |
| **10 — Done** | **Identity walkthrough** | Personas reviewed; Phase 1a badges coded. |
| **10a — Coded** | **Page ownership badges (web)** | Artist + venue show pages — Official / Unclaimed / Claim pending / Under review. **Deploy rsync below.** |
| **10b — Coded** | **Claim CTA on unclaimed pages** | Amber banner + Claim / Log in on artist & venue show. **Deploy rsync below.** |
| **11 — Coded** | **Phase 2 — `/me` + app banner** | `owned_pages`, `claimable_pages` in API; app banner + Settings copy. **Deploy below.** |
| **11a — Done** | **Phase 3 — claims from app** | `POST /me/claims/initiate`; banner **Claim now**. **Tested** (Shades1). |
| **12 — Done** | **Phase 4 — SSO** | `POST /me/web-session` + **Account on website** auto sign-in. **Tested OK.** |
| **12a — Done** | **Post sign-up website offer** | Dialog after **Create account** → open www signed in. **Tested OK** (`sso-signup`). |
| **13 — Coded** | **Phase 5 — manual claims** | `POST /me/claims/request`; artist/venue detail **Request to manage**; admin approve/reject on unclaimed edit. **Deploy below.** |
| **13a — Pinned / fix coded** | **Venue claim 422 + admin list** | Submit fix: drop redundant `isUnclaimed()` gate in `ClaimService`. **Admin list fix:** imported venues (`user_id`/`owner_id` set, not approved) now appear in **Unclaimed** with **Pending Claim** badge — rsync `Claimable.php`, `ClaimService.php`, `UnclaimedController.php`, `layouts/admin.blade.php`. |
| **14 — When ready** | **GitHub milestone sync** | Laravel + init Flutter repo — after SSO milestone (now). |
| **15 — Later** | **App analytics & insights** | Vanilla Play rollout first; white-label analytics deferred — see **§ App analytics (pinned)** below. |

---

## 📋 Product — Pages model (Dave — agreed direction, pre-launch)

**One-liner:** *Users are people; artists/venues/organisers are **Pages**; posting a gig links to a Page but does **not** make you its owner until **claim + verification**.*

| Topic | Launch stance |
|--------|----------------|
| **Signup** | One **`user`** account (app + web); roles from **claiming/creating a Page**, not signup forks. |
| **App register** | Creates Laravel user + token immediately; **no** email-verify gate; **no** auto-claim on register (web does claim match). |
| **Existing web user** | App login = **username + password** (same row). |
| **Crowd-created artists/venues** | **Unclaimed listings** — OK for launch; label clearly (Official / Unclaimed / Pending) on web before heavy ads. |
| **Claims** | **Email match:** app banner + `POST /me/claims/initiate`. **No email match:** app **Request to manage this page** + `POST /me/claims/request` → admin review. |
| **Venue “join existing venue”** | Website dashboard **`venues/request-ownership`** — separate from email claim. |
| **Full Facebook Pages platform** | **Phase 2+** — defer multi-admin, badges, app-native claim until after launch. |
| **Curation** | Light trust (flag/review queue) before big ad spend — not full moderation platform yet. |

**Next step when Dave asks:** persona walkthrough (new fan, existing web artist, venue manager) — what works today vs website-only.

### Phase 5 — Manual claim (no email match) — coded

**Flow:** App artist/venue detail → **Request to manage this page** → `POST /me/claims/request` → admin **Unclaimed** edit shows claimant + message → **Approve claim** / **Reject**.

**Not email auto-match** — no warning email, no auto-approve.

**Venue claim status (May 2026):** End-to-end is **coded** (app + API + admin approve/reject). **Artist** manual claim tested OK. **Venue** hit **422** because many listings still have legacy `user_id=1` / `owner_id` from CSV import — they show as **Unclaimed** in the API (`can_request_claim: true`) but the old server check required empty `user_id` **and** `owner_id`. **Fixed locally** in `ClaimService::requestManualClaimForUser()` — deploy `ClaimService.php` and retest one venue from the app.

**Optional data cleanup (VPS, when ready):** clear bogus venue owner ids from old import — ask agent or see chat; not required for claims after the fix.

---

## ▶ Deploy page ownership badges (Phase 1a)

```bash
cd ~/development/PHP-MyGigGuide
rsync -avz --no-group --no-owner app/Traits/Claimable.php dave@41.61.20.39:/var/www/mygigguide/app/Traits/
rsync -avz --no-group --no-owner app/Services/ClaimService.php dave@41.61.20.39:/var/www/mygigguide/app/Services/
rsync -avz --no-group --no-owner app/Models/Venue.php dave@41.61.20.39:/var/www/mygigguide/app/Models/
rsync -avz --no-group --no-owner app/Http/Controllers/Admin/ArtistManagementController.php app/Http/Controllers/Admin/VenueManagementController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Admin/
rsync -avz --no-group --no-owner resources/views/components/page-ownership-badge.blade.php dave@41.61.20.39:/var/www/mygigguide/resources/views/components/
rsync -avz --no-group --no-owner resources/views/artists/show.blade.php dave@41.61.20.39:/var/www/mygigguide/resources/views/artists/
rsync -avz --no-group --no-owner resources/views/venues/show.blade.php dave@41.61.20.39:/var/www/mygigguide/resources/views/venues/
ssh dave@41.61.20.39 "cd /var/www/mygigguide && php artisan view:clear"
```

**Badge rule (May 2026 fix):** **Official page** = `claim_status` **approved** (email claim or admin link). **Unclaimed** = no owner, or owner linked but not approved yet.

**Smoke:** most imported artists/venues → **Unclaimed listing**. Artists you admin-linked with approval → **Official page**.

**Optional one-time data on VPS (when ready):** mark pre-linked artists official: `Artist::whereNotNull('user_id')->where('claim_status', '!=', 'approved')->update(['claim_status' => 'approved']);` — clear bogus venue owner `user_id=1` from old CSV import (see chat / ask agent).

---

## ▶ Deploy claim CTA (Phase 1b)

```bash
cd ~/development/PHP-MyGigGuide
rsync -avz --no-group --no-owner resources/views/components/page-claim-cta.blade.php dave@41.61.20.39:/var/www/mygigguide/resources/views/components/
rsync -avz --no-group --no-owner resources/views/artists/show.blade.php dave@41.61.20.39:/var/www/mygigguide/resources/views/artists/
rsync -avz --no-group --no-owner resources/views/venues/show.blade.php dave@41.61.20.39:/var/www/mygigguide/resources/views/venues/
rsync -avz --no-group --no-owner resources/views/auth/register.blade.php dave@41.61.20.39:/var/www/mygigguide/resources/views/auth/
ssh dave@41.61.20.39 "cd /var/www/mygigguide && php artisan view:clear"
```

**Smoke:** open an **Unclaimed listing** artist/venue — amber **Claim this page** banner under the hero. **Official page** listings should **not** show the banner.

**Check Official counts (optional, read-only):**
```bash
ssh dave@41.61.20.39 "cd /var/www/mygigguide && php artisan tinker --execute=\"echo 'Official artists: '.App\\\\Models\\\\Artist::where('claim_status','approved')->whereNotNull('user_id')->count().PHP_EOL.'Official venues: '.App\\\\Models\\\\Venue::where('claim_status','approved')->where(function (\\\$q) { \\\$q->whereNotNull('user_id')->orWhereNotNull('owner_id'); })->count();\""
```

To mark a specific venue Official after admin links an organiser/user: set `claim_status` to `approved` in admin (or re-save link via unclaimed dashboard).

---

## ▶ Deploy Phase 2 — `/me` pages + app banner

**Laravel (VPS):**
```bash
cd ~/development/PHP-MyGigGuide
rsync -avz --no-group --no-owner app/Services/ApiMeProfileService.php dave@41.61.20.39:/var/www/mygigguide/app/Services/
rsync -avz --no-group --no-owner app/Http/Controllers/Api/V1/MeController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Api/V1/
ssh dave@41.61.20.39 "cd /var/www/mygigguide && php artisan route:clear && php artisan config:clear"
```

**Smoke API:** log in, then `curl -s -H "Authorization: Bearer TOKEN" https://www.mygigguide.co.za/api/v1/me | jq '.claimable_pages, .owned_pages'`

**App:** rebuild APK when ready — `./scripts/build_apk.sh mygigguide --label=claim-pages`

**App files changed (Flutter repo):** `lib/services/laravel_api.dart`, `lib/widgets/claim_pages_banner.dart`, `lib/app_shell.dart`, `lib/app_prefs.dart`, `lib/screens/settings_screen.dart`, `lib/brand_config.dart`

---

## ▶ Deploy Phase 3 — claims from app API

**Laravel (VPS):**
```bash
cd ~/development/PHP-MyGigGuide
rsync -avz --no-group --no-owner app/Services/ClaimService.php dave@41.61.20.39:/var/www/mygigguide/app/Services/
rsync -avz --no-group --no-owner app/Http/Controllers/Api/V1/MeController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Api/V1/
rsync -avz --no-group --no-owner routes/api.php dave@41.61.20.39:/var/www/mygigguide/routes/
ssh dave@41.61.20.39 "cd /var/www/mygigguide && php artisan route:clear && php artisan config:clear"
```

**Smoke API:**
```bash
curl -s -X POST -H "Authorization: Bearer TOKEN" -H "Accept: application/json" \
  https://www.mygigguide.co.za/api/v1/me/claims/initiate | jq '.message, .approved, .claimable_pages'
```

**App:** rebuild when ready — `./scripts/build_apk.sh mygigguide --label=claims`

**App files (Flutter):** `lib/services/laravel_api.dart` (`initiateClaims`), `lib/widgets/claim_pages_banner.dart` (**Claim now** button)

**Test on phone:** log in with email matching an unclaimed artist `contact_email` → banner → **Claim now** → snackbar + banner clears; artist page on www shows **Official**.

---

## ▶ Deploy Phase 4 — app → website SSO

**Laravel (VPS):**
```bash
cd ~/development/PHP-MyGigGuide
rsync -avz --no-group --no-owner app/Services/AppWebSessionService.php dave@41.61.20.39:/var/www/mygigguide/app/Services/
rsync -avz --no-group --no-owner app/Http/Controllers/AppWebSessionController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/
rsync -avz --no-group --no-owner app/Http/Controllers/Api/V1/MeController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Api/V1/
rsync -avz --no-group --no-owner config/app_web_session.php dave@41.61.20.39:/var/www/mygigguide/config/
rsync -avz --no-group --no-owner routes/api.php routes/web.php dave@41.61.20.39:/var/www/mygigguide/routes/
ssh dave@41.61.20.39 "cd /var/www/mygigguide && php artisan route:clear && php artisan config:clear"
```

**Smoke API:**
```bash
curl -s -X POST -H "Authorization: Bearer TOKEN" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"redirect":"/dashboard"}' \
  https://www.mygigguide.co.za/api/v1/me/web-session
```

**App:** `./scripts/build_apk.sh mygigguide --label=sso`

**Test on phone:** log in → **Settings → Account on website** → browser opens **already signed in** (dashboard or profile, not login form). Link expires in 5 minutes and works once.

---

## ▶ Deploy Phase 5 — manual claims

**Laravel (VPS):**
```bash
cd ~/development/PHP-MyGigGuide
rsync -avz --no-group --no-owner database/migrations/2026_05_19_120000_add_claim_request_message_to_claimable_tables.php dave@41.61.20.39:/var/www/mygigguide/database/migrations/
rsync -avz --no-group --no-owner app/Traits/Claimable.php dave@41.61.20.39:/var/www/mygigguide/app/Traits/
rsync -avz --no-group --no-owner app/Services/ClaimService.php dave@41.61.20.39:/var/www/mygigguide/app/Services/
rsync -avz --no-group --no-owner app/Models/Artist.php app/Models/Venue.php app/Models/Organiser.php dave@41.61.20.39:/var/www/mygigguide/app/Models/
rsync -avz --no-group --no-owner app/Http/Controllers/Api/V1/MeController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Api/V1/
rsync -avz --no-group --no-owner app/Http/Controllers/Admin/UnclaimedController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Admin/
rsync -avz --no-group --no-owner app/Http/Resources/Api/V1/ArtistResource.php app/Http/Resources/Api/V1/VenueResource.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Resources/Api/V1/
rsync -avz --no-group --no-owner app/Http/Resources/Api/V1/Concerns/SerializesPageOwnership.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Resources/Api/V1/Concerns/
rsync -avz --no-group --no-owner resources/views/admin/unclaimed/_pending-claim-review.blade.php dave@41.61.20.39:/var/www/mygigguide/resources/views/admin/unclaimed/
rsync -avz --no-group --no-owner resources/views/admin/unclaimed/edit-artist.blade.php resources/views/admin/unclaimed/edit-venue.blade.php resources/views/admin/unclaimed/edit-organiser.blade.php dave@41.61.20.39:/var/www/mygigguide/resources/views/admin/unclaimed/
rsync -avz --no-group --no-owner routes/api.php routes/admin.php dave@41.61.20.39:/var/www/mygigguide/routes/
ssh dave@41.61.20.39 "cd /var/www/mygigguide && php artisan migrate --path=database/migrations/2026_05_19_120000_add_claim_request_message_to_claimable_tables.php --force && php artisan route:clear && php artisan view:clear"
```

**Note:** Use `--path=…` for this deploy only. Plain `migrate --force` on the VPS can fail on older pending migrations (e.g. `venue_owners` already exists but not recorded in `migrations`).

**App:** `./scripts/build_apk.sh mygigguide --label=manual-claim`

**Test on phone:** log in → open an **unclaimed** artist whose `contact_email` ≠ your account → **Request to manage this page** → amber pending banner. Admin: **Unclaimed** → edit that artist → approve or reject.

**Venue retest (after `ClaimService.php` rsync):** same flow on a venue detail page → should return **200** + pending banner (not 422).

**Smoke API:**
```bash
curl -s -X POST -H "Authorization: Bearer TOKEN" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"type":"artist","id":851,"message":"Test request"}' \
  https://www.mygigguide.co.za/api/v1/me/claims/request
```

---

## ▶ Deploy browse sort API (if not on VPS yet)
```bash
cd ~/development/PHP-MyGigGuide
rsync -avz --no-group --no-owner app/Http/Controllers/Api/V1/Concerns/AppliesDirectorySort.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Api/V1/Concerns/
rsync -avz --no-group --no-owner app/Http/Controllers/Api/V1/ArtistController.php app/Http/Controllers/Api/V1/VenueController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Api/V1/
rsync -avz --no-group --no-owner app/Http/Resources/Api/V1/ArtistResource.php app/Http/Resources/Api/V1/VenueResource.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Resources/Api/V1/
ssh dave@41.61.20.39 "cd /var/www/mygigguide && php artisan route:clear && php artisan config:clear"
```

**Later (pinned):** edit own events (app); event curation; Rogues rebuild; **Ratings moderation** (hide/merge abusive reviews).

---

## ▶ Was: ratings deploy (done)

**Ratings MVP coded** — deploy + test.

| Step | Action | Done? |
|------|--------|-------|
| **1** | **Deploy Laravel** — rsync ratings files (see below) | ☑ |
| **2** | **Rebuild app** — `./scripts/build_apk.sh mygigguide --label=ratings` | ☑ |
| **3** | **Test** — open event/artist/venue → rate (signed in) → see review in list | ☑ |

**Deploy ratings API (from PC):**
```bash
cd ~/development/PHP-MyGigGuide
rsync -avz --no-group --no-owner routes/api.php dave@41.61.20.39:/var/www/mygigguide/routes/
rsync -avz --no-group --no-owner app/Http/Middleware/EnsureApiPermission.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Middleware/
rsync -avz --no-group --no-owner app/Services/ApiRatingService.php dave@41.61.20.39:/var/www/mygigguide/app/Services/
rsync -avz --no-group --no-owner app/Http/Controllers/Api/V1/RatingController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Api/V1/
rsync -avz --no-group --no-owner app/Http/Resources/Api/V1/RatingResource.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Resources/Api/V1/
rsync -avz --no-group --no-owner app/Http/Resources/Api/V1/Concerns/SerializesRatingSummary.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Resources/Api/V1/Concerns/
rsync -avz --no-group --no-owner app/Http/Resources/Api/V1/EventResource.php app/Http/Resources/Api/V1/ArtistResource.php app/Http/Resources/Api/V1/VenueResource.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Resources/Api/V1/
rsync -avz --no-group --no-owner app/Http/Controllers/Api/V1/EventController.php app/Http/Controllers/Api/V1/ArtistController.php app/Http/Controllers/Api/V1/VenueController.php dave@41.61.20.39:/var/www/mygigguide/app/Http/Controllers/Api/V1/
ssh dave@41.61.20.39 "cd /var/www/mygigguide && php artisan route:clear && php artisan config:clear"
```

---

## Done (21 May 2026) — Home milestone + Rogues parity

### Mobile (`mygigguide_app`) — both flavors share this code

| Change | Where |
|--------|--------|
| **Home Browse** — continuous **iTunes-style coverflow** (all events in range); **1 / 3 / 5 / 7** day pills; genre chips | `home_map_screen.dart`, `home_events_coverflow.dart`, `coverflow_carousel.dart` |
| **Home Map** — full-screen toggle; slim header; **genre chips** under range (same `SearchPrefs` as Browse) | `home_map_screen.dart` |
| **Events tab** — diary-style list by day (unchanged role vs Home browse) | `home_map_screen.dart` / Events tab |
| **Event detail** — hero = poster only; **gallery** = coverflow on `gallery_urls` | `event_detail_screen.dart`, `coverflow_gallery.dart` |
| **WhatsApp share** — green **Share on WhatsApp** bar under poster (event / artist / venue); removed from app bar | `detail_whatsapp_share.dart`, detail screens |
| **Rogues `SITE_URL`** — default `https://rogues.mygigguide.co.za`; build script passes `--dart-define=SITE_URL=…` | `brand_config.dart`, `scripts/build_apk.sh`, `tool/run_rogues_android.sh` |

**Rogues APK (built):** `./scripts/build_apk.sh rogues` → **1.0.1 (build 27)** · `SITE_URL=https://rogues.mygigguide.co.za`  
**Path:** `~/development/mygigguide_app/build/app/outputs/flutter-apk/app-rogues-release.apk` (~62 MB)

**Dave — optional before/after reboot:** install on phone; smoke **Settings**, Home browse/map, WhatsApp share, **Radio** tab. Upload to Drive as `rogues-1.0.1-build27.apk` if sending to Clive.

### Infrastructure — Rogues subdomain (live)

| Item | Detail |
|------|--------|
| **URL** | https://rogues.mygigguide.co.za (HTTPS, certbot) |
| **DNS** | A record `rogues` → VPS (`41.61.20.39`) — Domains.co.za |
| **nginx** | Dedicated vhost `/etc/nginx/sites-enabled/rogues.mygigguide.co.za` (same Laravel app as www for now; hostname skin TBD) |

**Not yet:** Laravel host-based Rogues web branding (serves main MGG site today).

**Phase A (22 May 2026 — deployed):** `SiteBrand` hostname skin on `rogues.mygigguide.co.za`. **Phase B** deferred.

**Identity MVP (#7):** **Smoke test passed (22 May)** — register + sign-in on phone OK. Settings shows **username** (e.g. Skivvy); Account screen shows **name** (e.g. Dave Test) — same account, two fields. Optional polish: unify Settings subtitle copy later.

**Next after auth:** quick Saved/Add check → optional emoji APK → **#7c Saved thumbnails** (backlog).

**App UI (22 May):** Emoji bottom nav + poster loading fix on PC — install with `--label=emoji` when ready (after auth test OK on current APK is fine too).

### Saved favourites thumbnails (#7c) — done 22 May

API `image_url` on favourites + app thumbnails in Saved tab. Deploy: `MeController.php` → correct VPS path (not project root).

### Add event gallery — coded 22 May; tested 19 May 2026

Add tab: **Gallery photos (optional)** — pick up to 10 images, horizontal previews, uploads as `gallery[]` with poster on post.

**Dave test (19 May):**
- **Done** — gallery post, size labels, form clear; works for member accounts.
- Skivvy + Dave Test path both OK after API permission fix.

**Product pin:** **Any logged-in member can post** — curation/approval comes later.

---

### App icons / posters (historical)

Icon-font issue on some Android builds → **emoji bottom nav** (`--label=emoji`). Posters need working `/storage` on VPS (fixed 22 May).

---

## ✅ Identity decisions (Dave — 20 May 2026)

Logged before reboot / going out. Build to this when back.

| Topic | Dave’s choice | Build note |
|--------|----------------|------------|
| **Signup → Laravel `user`** | **Yes** | Create Laravel user on app signup (recommended; one account story). |
| **Email verification** | **Website only for now**; **not required on mobile** (later) | Verify on web login flow when we wire it; app MVP skips verify gate. |
| **Existing web users** | **Same password on app** — **Yes** | First app login = Laravel email/password (same as site); forgot-password path. |
| **Add event if guest** | **No for now** | Add event requires login; may relax later depending on spam/disinfo. Guest browse OK. |
| **One account (Firebase ↔ Laravel link)** | **Agent: hide for MVP** | No “Link website account” card unless we detect mismatch later. Testers: “Use your My Gig Guide email in Log in.” Dave does not need link. |
| **Rogues** | **Same identity rules as MGG** | Do **not** build a separate Rogues auth path now — only align at **end of development** / milestone. |
| **App → website login** | **Not automatic (pinned)** | App uses Sanctum bearer; website uses session cookie. Opening site in WebView still needs web sign-in unless we build SSO later. |

**Still default (unchanged):** Laravel `users.id` = SSOT; **no Laravel user before app signup/login** (guest = local prefs only); app **Create account** creates Laravel `user` immediately; new users role **`user`**; any `user` may post when logged in; ratings later require login.

**Open (low priority):** unify Settings username vs Account name copy.

---

## ▶ Historical — after reboot (21 May, superseded)

**Session closed:** 21 May 2026 (Dave rebooting laptop). **Rogues milestone complete** — code + docs saved on disk; **no git commits** this session unless Dave asks later.

**Main focus when back (May 2026):** **Browse sort deploy** — see urgent table + rsync block at top. Identity + ratings already tested.

**Optional 10 min (no coding):**

1. Install latest **mygigguide** APK after `--label=sort` build.
2. Smoke **Browse → Artists / Venues** — **Top rated** and **Most events** chips.
3. Upload tester APK to Drive if sharing.

**When coding again:** `./scripts/build_apk.sh mygigguide --label=sort` after API rsync.

---

## Done today (19 May 2026) — mobile UI + Browse

| Change | Where |
|--------|--------|
| **Vanilla theme** lighter off-white (`#F2F3F7` scaffold, white cards) | `mygigguide_app/lib/main.dart` |
| **Brand logo** stable slot on Events, Browse, Saved, Add event | `brand_logo_header.dart` |
| **Event cards** 40% image / 60% text; ~30% narrower carousels | `gig_overview_card.dart`, `gig_carousel_layout.dart` |
| **Browse** chips: **All** \| **Gigs Near Me** \| **Top rated** \| **Most events** | `directory_sort_chips.dart`, `api_directory_screen.dart`, `directory_list_logic.dart` |
| **Gigs Near Me** — 14-day start, date→distance sort, loading banner, **Load 2 more weeks** (up to 42d) | `playing_soon_from_events.dart`, `directory_gig_index.dart`, `api_directory_screen.dart` |
| **Fuzzy search** on Browse + Add event pickers | `fuzzy_text_match.dart`, `add_event_directory_search.dart` |
| **Add event** — create artist/venue (“Add new”), **multi-category** fix (`categories[n]` multipart) | `add_gig_screen.dart`, `laravel_api.dart` |
| **Paste poster** hidden (`kShowPosterPaste = false`) — gallery only | `add_gig_screen.dart` |

**Rogues:** same Dart tree — `./scripts/build_apk.sh rogues` sets `BRAND=rogues` + `SITE_URL=https://rogues.mygigguide.co.za`. Add event via **Settings**.

**Superseded by 21 May milestone** — rebuild both flavors after Home/coverflow/WhatsApp changes.

---

## Done today (19 May 2026) — Laravel API (create artist/venue)

| Change | Where |
|--------|--------|
| **`POST /api/v1/artists`** and **`POST /api/v1/venues`** (Sanctum + `create-events`) | `ArtistController`, `VenueController`, `routes/api.php` |
| Crowd-source services | `CrowdSourceArtistService.php`, `CrowdSourceVenueService.php` |
| Tests + docs | `ArtistStoreApiTest`, `VenueStoreApiTest`, `docs/API_V1.md` |

**VPS:** Dave rsync’d — routes live. **GitHub:** not pushed in this session unless Dave commits.

---

## Done today (19 May 2026) — mobile Add event

| Change | Where |
|--------|--------|
| **Paste image** on Add event (clipboard → temp file → same flow as gallery pick) | `mygigguide_app` → `lib/screens/add_gig_screen.dart` + `pasteboard` dep |
| **Read poster** hidden for now (`kShowPosterReader = false`) — miggs-bridge / `BRIDGE_*` **lower priority** | Same file; flip flag when bridge is ready |
| **Not yet:** paste poster **text** from clipboard | Backlog |

Rebuild **`mygigguide`** APK to test on phone.

---

## Done today (18 May 2026)

### Kayise / process

- Alignment meeting; follow-up email sent (deploy path, Phase 3, APK links).
- **Policy:** finish this block on `main`, deploy, adopt Kayise branch workflow later.

### Laravel — committed, pushed, **live on VPS**

- **GitHub `main`:** commit `5860450` — API v1 event create, Sanctum, `EnsureApiPermission`, `EventCreationService`, Firebase link endpoints (optional), migrations, tests, `docs/API_V1.md`.
- **Deploy:** `git pull` on VPS failed (GitHub key); used **rsync** from laptop → `dave@41.61.20.39:/var/www/mygigguide` (see [DEPLOY_VPS.md](./DEPLOY_VPS.md)).
- **On VPS after rsync:** `composer install`, two `2026_05_17_*` migrations (one reported “nothing to migrate” — already applied), `config:clear`, `route:clear`, `curl …/api/v1/meta` OK.

### Mobile (Dave’s phone)

- New APK with **“One account”** card; Laravel login works (connection must be on).
- **Post event** via API works when logged in.
- **Events** tab OK after connection + fetch retries build.
- **Firebase link:** not needed for Dave (+ror vs Google email); **skipped**.

### Identity / roles (decided — build later)

| Decision | Choice |
|----------|--------|
| Default signup role | **`user`** |
| Who can post events | **Any `user`** — crowd-source; pick **existing** venue + artists |
| New **artist** | **Yes**, with **fuzzy search** first to avoid duplicates |
| New **venue** | Same in add-event flow (new places allowed; dedupe + curation later) |
| **Organiser** | Opt-in role; can create artists from event flow + Artists page (API TBD) |
| **Rogues / brands** | Tag events/users by brand/silo later (`source` field) |
| **Curation** | Later — pending/approve/merge duplicates |

Dave does **not** need to link his own Firebase ↔ Laravel accounts.

---

## 🎯 Focused backlog (Dave — don’t drift)

| # | Task | Status |
|---|------|--------|
| 1 | **Add event — pick existing artists** (`artists[]`, search + chips) | **Done in PC code** — rebuild APK to test |
| 2 | **Events tab** — diary by day + **All / Near me**; no 1/3/5/7; **Browse** tab (Artists \| Venues) | **Done in PC code** — rebuild APK |
| 2b | **Vanilla theme** — softer off-white (not stark white) | **Done in PC code** |
| 3 | **Playing soon** on Artists tab — fix + APK (`playing_soon_from_events.dart`) | After #2 or parallel |
| 4 | Fuzzy **create** artist/venue on add-event | **Done in PC code** — API `POST /artists` + `/venues`; fuzzy search + “Add new” on Add event; deploy API + rebuild APK |
| 4b | **Gigs Near Me — load more** (+2 weeks button) | **Done in PC code** — 14d start; **Load 2 more weeks** appends to 28/42d; rebuild APK |
| 5 | **Saved tab** (events/artists/venues only) | **Done in PC code** — rebuild APK |
| 5b | Merge **Artists + Venues** into **Browse** tab | **Done in PC code** |
| 6 | **Ratings & reviews — mobile MVP** | **Done (19 May)** — API + compact detail UI; public reviews |
| 6b | **Browse — Top rated / Most events** | **Coded** — deploy API + APK |
| 6c | **Dark theme + logo glow (MGG)** | **Done & tested (23 May)** |
| 6d | **Website dark theme** | **Pinned** — ~2–3 wk public pages |
| 6e | **Favicon + launcher from glow logo** | **Done** — gorilla badge; rebuild APK |
| 6f | **Edit own events (app)** | **Pinned** — API update + in-app edit; mirror web `/events/{id}/edit` |
| 7 | **Identity / Pages walkthrough** | **Done** — Phases 1–5 coded/deployed |
| 8 | Firebase link / Rogues `source` / RSS ingest | Later |
| 12 | **Featured** artist / venue / event (web + app MVP) | **After #7** — see prior estimate |
| 11 | **Advertising website** — planning & development (advertiser portal + ad delivery into app/site) | **Todo** — see § Advertising below |
| 9 | **n8n** — open issue (WhatsApp bridge updates, RSS/event workflows, VPS access) | **Todo** — discuss when Dave has time; see [RSS_EVENT_DISCOVERY.md](./RSS_EVENT_DISCOVERY.md) |
| 10 | **Poster bridge** — “Read poster” → miggs-bridge `POST /app/parse-poster` (`BRIDGE_BASE_URL`, `BRIDGE_POSTER_SECRET`) | **Lower priority** — UI hidden; re-enable with `kShowPosterReader` |
| 10b | **Paste poster text** (clipboard text → fields) | **Not started** — image paste done first |
| 13 | **Shelf rows** — titled horizontal rows (“This week”, “Near you”) | **Polish backlog** — Home now has **continuous coverflow browse** (21 May); shelves = optional extra layout later |
| 14 | **Fuzzy search + adding venues** — add-event venue pick/create polish, dedupe UX | **Todo** |
| 15 | **91.9 FM flavor** (`fm919`) — assets, tenant URL, branded APK | **Todo** — scaffold exists in app; needs logo/site/config |
| 16 | **MGG logo — slate drum set icon** (replace gorilla badge?) | **Review** — PNG + SVG concepts in app assets; Dave to approve before launcher swap |
| 17 | **App analytics & insights** | **Pinned** — vanilla Play first; see **§ App analytics (pinned)** in this file. Phase 1: brand+build in API `device_name`. |

**Paused:** Firebase linking for Dave; Home vs Events = keep both tabs (clarify in UI later, not merge now).

---

## 🎨 Shelf rows (polish — Dave May 2026)

**Shipped (21 May):** Home **Browse** uses one continuous **coverflow** carousel for all events in the selected day range (1/3/5/7) — not per-day diary tiles.

**Still optional (polish):** multiple **titled shelves** (like Apple TV) — e.g. “This week”, “Near you”, “Jazz” — each a horizontal row. Lower priority now that coverflow browse is on Home.

---

## 📣 Advertising platform (backlog — planning)

**Goal:** Self-serve (or admin-assisted) **advertising site** where businesses buy placement; **same Laravel backend** serves creatives to **website + mobile** for ad revenue.

**High-level — yes, this fits the stack:**

| Piece | Role |
|-------|------|
| **Advertiser site** (new or `/advertise` on MGG) | Sign up, create campaign, upload banner, pick regions/genres, pay (manual invoicing → Stripe later) |
| **Laravel (SSOT)** | `ad_campaigns`, `ad_creatives`, slots, schedule, targeting, impression/click logs |
| **API v1** | `GET /api/v1/ads?placement=home_carousel&brand=mygigguide` — app + web consume JSON |
| **Website** | Blade/React slots: home, event list, event detail (clear “Sponsored”) |
| **Mobile** | Widget between carousels / Browse header; respect flavor (`BRAND`) |
| **Revenue** | You set CPM/CPC or fixed packages; not the same as dropping in **Google AdMob** (can add later as filler) |

**Phases (rough):** (1) planning — slots, pricing, legal/privacy; (2) MVP admin creates ads in Laravel; (3) display on web + app; (4) advertiser self-serve portal; (5) payments + reporting.

**Open:** Rogues-only campaigns vs network-wide; house ads for Rogues sponsors first.

---

## 📅 Repeat / multi-day events (when required — not now)

**Trigger:** build when a client or crowd-source flow needs it (e.g. “Karaoke every Thursday”). **Not** on the active mobile backlog until then.

**Dave’s priority order:**

| Priority | Feature | Approach (agreed direction) | Effort (rough) |
|----------|---------|-----------------------------|----------------|
| **1** | **Repeat events** (weekly residency, same night each week) | Laravel + web + API: e.g. “every Thursday until …” → generate linked rows + `series_id` (not full iCal) | ~2–3 weeks |
| **2** | **Multi-day series** (festival Fri–Sun, one gig) | `end_date` (or date range); one row, show on each day in calendar/map | ~1–2 weeks after or alongside #1 |
| **3** | **Full iCal / RRULE recurrence** | **Probably never** — too heavy for gig-guide MVP | — |

**Cannot be app-only:** today each event = one `date` + `time`; app could spam multiple `POST /api/v1/events` as a hack, but proper product needs **website + API + app** (SSOT is Laravel).

**Open questions when we pick this up:** edit whole series vs one night; same time every occurrence; one favourite vs per night.

**Rogues pitch:** client-facing deck + speaker notes → [ROGUES_ON_RADIO_PITCH_DECK.md](./ROGUES_ON_RADIO_PITCH_DECK.md)

---

## 📱 App store release (tracked)

**Dev / test default:** **`mygigguide`** flavor (vanilla). **Rogues:** rebuild at **major milestones** (`./scripts/build_apk.sh rogues`) — **last milestone: 21 May 2026** (Home coverflow, map, WhatsApp share, `rogues.mygigguide.co.za`).

**Store priority (later):** Rogues may still be first **paying client** on Play/App Store; day-to-day work stays on vanilla until then.

**Testing path (now):** Dave’s **friends test APKs** (sideload / Drive) before any store — no Play/App Store account required yet.

| Phase | What | Status |
|-------|------|--------|
| **A — Friend APKs** | `./scripts/build_apk.sh mygigguide` (default); Rogues when milestone reached | **Dave’s next** |
| **B — Release-ready build** | Release **keystore** (Android); stop debug signing; Firebase/OneSignal SHA for release key | Not started |
| **C — Legal / listing** | Privacy policy URL on site; support email; screenshots; short/long description; Rogues branding assets | Not started |
| **D — Google Play** | Console account (~$25); app `za.co.mygigguide.rogues_demo` → production package name TBD with Kayise; **internal testing** → closed → production | Not started |
| **E — Apple** | Developer program (~$99/yr); **Mac + Xcode** required; TestFlight → App Review; Rogues bundle TBD | Not started |
| **F — MGG store** | Second listing after Rogues path proven | Later |

**Rogues-specific checklist (when prioritised):**

- [x] API/site URLs point at correct tenant (`BRAND=rogues`, `SITE_URL=https://rogues.mygigguide.co.za` — build script + `brand_config.dart`)
- [ ] Confirm final **applicationId** / bundle id with client (not `rogues_demo` for production)
- [ ] Rogues **OneSignal** app id + push copy for white-label
- [ ] Store listing: **Rogues** icon, screenshots, Radio tab, station stream URLs
- [ ] Add event via **Settings** (no Add tab) — clear in tester notes

**Blockers to watch:** Android still on **debug signing** in `build.gradle.kts`; iOS needs a **Mac**; privacy policy page for store forms.

**Rough timeline (from zero):** friend APKs **now** → Play internal **~2–4 weeks** after B+C → App Store **~4–8 weeks** (Mac-dependent). See prior chat for full helicopter bullets.

---

## 📌 Paused — not unless Dave asks

| Item | Notes |
|------|--------|
| **Firebase linking** | API on server; `FIREBASE_WEB_API_KEY` when testing |
| **Fuzzy create artist/venue** | After phase A pick-existing works in the field |
| **n8n** | On backlog (#9); not unless Dave asks to dig in |
| **miggs-bridge poster scan** | Code kept; `kShowPosterReader = false` in Add event until Dave prioritises |
| **Repeat / multi-day events** | See § above; repeat-first, iCal unlikely |
| **VPS `git pull` for dave** | [DEPLOY_VPS.md](./DEPLOY_VPS.md) |
| **Website dark theme** | ~2–3 weeks public pages; pin until after app launch messaging settled — [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md) |
| **Glow logo → favicon + app icon** | `header_logo_glow.png` — favicon later; launcher before store |
| **App analytics & insights** | Vanilla rollout first; white-label deferred — see **§ App analytics (pinned)** |

---

---

## 📌 Milestone GitHub sync (Dave — after major milestones)

**Goal:** PC, GitHub, and VPS stay aligned. Today production is often **rsync-first**; GitHub lags. After each milestone (identity phase done, SSO tested, etc.), run this **same day or next session**.

### When to sync

| Trigger | Example |
|---------|---------|
| Feature **coded + tested on phone/www** | Phase 3 claims, Phase 4 SSO |
| **Deployed to VPS** via rsync | Same files you just pushed to server |
| Before sharing with **Kayise / another dev** | So `main` reflects reality |

**Order:** deploy & smoke-test → **commit & push** → note in [UPDATE_LOG.md](./UPDATE_LOG.md) → (later) enable VPS `git pull`.

### Repo 1 — Laravel (`~/development/PHP-MyGigGuide`)

**Remote:** `git@github.com:kayise-it/PHP-MyGigGuide.git` · branch **`main`**

**Include:** app code, routes, tests, `docs/API_V1.md`, `docs/UPDATE_LOG.md`, `docs/SESSION_HANDOFF.md`, `docs/PRODUCT_CONTINUITY.md`, `docs/DEPLOY_VPS.md`

**Usually skip (local / sensitive):**

| Path | Why |
|------|-----|
| `.cursor/` | Editor-only |
| `docs/personal.md` | Your working prefs — keep local unless you want it shared |
| `docs/Alignment*.pdf` | Contract PDFs — your call |
| `.env` | Never (already gitignored) |

**Typical flow (when Dave asks to commit):**

```bash
cd ~/development/PHP-MyGigGuide
git status
git add app/ routes/ config/ tests/ resources/views/components/page-*.blade.php \
  resources/views/artists/show.blade.php resources/views/venues/show.blade.php \
  docs/API_V1.md docs/UPDATE_LOG.md docs/SESSION_HANDOFF.md docs/PRODUCT_CONTINUITY.md
git commit -m "Your message — focus on why (milestone)."
git push origin main
```

**This milestone (May 2026) would cover:** identity badges/CTA, `/me` + claims API, SSO, ratings, browse sort, Rogues `SiteBrand`, handoff docs.

### Repo 2 — Flutter (`~/development/mygigguide_app`)

**Status today:** **not a git repo yet** — no `.git` folder. Needs one-time setup before first push.

**Decide first:** GitHub org/repo (e.g. `kayise-it/mygigguide_app` or personal) — then:

```bash
cd ~/development/mygigguide_app
git init
git add lib/ android/ ios/ pubspec.yaml scripts/ assets/ docs/
git commit -m "Initial commit: MGG app — dark theme, claims, SSO."
git remote add origin git@github.com:YOUR_ORG/mygigguide_app.git
git push -u origin main
```

Add a root `.gitignore` (`.dart_tool/`, `build/`, `.env`, `*.apk`) before first commit if missing.

### After push (optional next step)

When deploy key works on VPS: `git pull` on server instead of rsync for code-only releases — see [DEPLOY_VPS.md](./DEPLOY_VPS.md).

---

## Git / deploy snapshot

| Where | State |
|-------|--------|
| **PC Laravel** | **Large uncommitted delta** vs `origin/main` (identity, SSO, ratings, sort, docs). Last push: `5860450`. |
| **VPS** | Often **ahead of GitHub** via rsync (claims, SSO when deployed). |
| **Flutter** | **No git repo yet** — all work local only. Latest APK labels: `claims`, `sso`. |

**Flutter APK:** `./scripts/build_apk.sh mygigguide --label=logo-round` · version in **Settings** · Rogues at milestones only.

---

## Agent instruction

On “continue”: read **personal.md** → **this file** → **PRODUCT_CONTINUITY.md**.

**Default next:** **Identity walkthrough** (Dave requested) — then browse sort deploy if needed.

**Pinned:** edit own events (app); web dark theme; event curation; GitHub milestone sync; Phase 6 Firebase unified auth.

---

## Pinned product — YouTube videos & Add tab (May 2026)

Dave + agent discussion **19 May 2026**. Keep in mind when prioritising backlog.

### YouTube videos — current state

| Entity | Web (admin / owner) | API read | API write (crowd) | App detail |
|--------|---------------------|----------|-------------------|------------|
| **Events** | Create/edit forms | Yes | Yes (`EventCreationService`) | Event detail shows videos; **Add/Edit event app — paste URLs + pull from venue/artists (Phase B — coded May 2026)** |
| **Artists** | Admin create/edit | Yes (`GET /artists/{id}`) | Quick-create = name only; **owner PATCH videos (May 2026)** | Artist detail shows videos; **Manage videos** when `can_edit_videos` |
| **Venues** | Owner edit + admin | Yes (`GET /venues/{id}`) | Quick-create = place/name only; **owner PATCH videos (May 2026)** | Venue detail shows videos; **Manage videos** when `can_edit_videos` |

**Model:** polymorphic `youtube_videos` table — **YouTube URLs only** (no hosted upload). Same shape everywhere: `youtube_url`, `youtube_video_id`, `title`, `order`.

### YouTube videos — phased plan (agreed direction)

| Phase | What | Why / when |
|-------|------|------------|
| **A — Display** | Artist + venue + event detail in app (read API) | Done for artist/venue/event show paths |
| **B — Post on gig** | Optional YouTube URLs on **Add event / edit own event** in app | **Done (May 2026):** paste up to 5 URLs + checkboxes from linked venue/artists; dedupe by video id on submit |
| **C — Page owner edit** | Claimed artist/venue can curate videos (API `PATCH` + app) | **Done (May 2026):** artist + venue **Manage videos** on detail pages |
| **D — Suggest video on any page** | Fan-submitted links + moderation | Defer until **event curation** / admin queue exists |

**Do not** open “anyone adds video to unclaimed artist/venue” without labels + curation — crowd stubs stay minimal for gig linking.

### Add tab — Option C (Dave’s choice)

**Keep the Add tab primary = post a gig** (consider label **“Post a gig”** later for clarity). Artist/venue creation stays **inside** Add event (search → pick existing → Add new / Google place).

**Optional “More” entry** (sheet or menu on Add tab): **Add venue only** · **Add artist only** — same `POST /api/v1/venues` and `POST /api/v1/artists` as today, minimal forms, copy like *“Usually you’ll add these while posting a gig.”*

**Not** a full three-way hub as equal top-level tabs — avoids orphan listings and duplicate UX.

**Rogues:** no bottom Add tab today (Add under **Settings**); any “More” sheet must work there too when built.

**Contextual CTAs unchanged:** Add gig here (venue detail), Add gig with this artist, map pin, etc.

---
