# Dave — working preferences (for humans & AI)

**Read this at the start of a session** when Dave says “continue”, “pick up where we left off”, or opens the My Gig Guide / Flutter work again.

Last updated: **23 May 2026**

**Resume:** [SESSION_HANDOFF.md](./SESSION_HANDOFF.md) § “After reboot — pick up here” (live stack + check-in done; **Play Store MGG** next).

---

## Who & environment

| Item | Detail |
|------|--------|
| **Name** | Dave |
| **OS** | **Linux Mint** — treat as capable but **not** a daily terminal power user |
| **Dev roots** | `~/development/PHP-MyGigGuide` (Laravel), `~/development/mygigguide_app` (Flutter) |
| **Production site** | https://www.mygigguide.co.za — VPS app path `/var/www/mygigguide` |
| **Rogues tenant site** | https://rogues.mygigguide.co.za — same Laravel app (hostname branding TBD) |
| **VPS SSH** | `dave@mel55-nix02` — app path `/var/www/mygigguide` |

---

## How Dave wants to work

1. **Slow, baby steps** — one clear action at a time; explain *why* before the next step. No dumping ten commands without context.
2. **Plain language** — avoid jargon; when you use a term (RSS, flavor, Sanctum), one short sentence on what it is.
3. **Copy-paste commands** — when Dave needs to run something, give **ready-to-paste terminal blocks** (VPS and local). Say what success looks like. **Do not** run builds (APK, long Gradle jobs) in the agent unless Dave asks — give him the command to paste instead. **Do not** walk through git commit, push, or rsync unless Dave explicitly asks for that.
4. **Small safe changes** — prefer focused diffs; no drive-by refactors or extra markdown files unless asked.
5. **Git** — **do not commit or push** unless Dave explicitly asks.
6. **Continuity** — after meaningful work, add a line to [UPDATE_LOG.md](./UPDATE_LOG.md), update [SESSION_HANDOFF.md](./SESSION_HANDOFF.md), and skim [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md). **After a tested milestone**, sync GitHub — see SESSION_HANDOFF § *Milestone GitHub sync*.

---

## VPS deploy (copy-paste)

Run on the server after the updated PHP files are already in `/var/www/mygigguide`:

```bash
cd /var/www/mygigguide
php composer.phar install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan route:cache
php artisan config:cache
curl -s "https://www.mygigguide.co.za/api/v1/meta"
```

Last line should return JSON with `"api_version"`.

---

## Test create-event API from PC (copy-paste)

Edit `YOUR_USERNAME`, `YOUR_PASSWORD`, and `VENUE_ID` (numeric id from `/venues/123` → `123`).

```bash
BASE="https://www.mygigguide.co.za"

TOKEN=$(curl -s -X POST "$BASE/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"YOUR_USERNAME","password":"YOUR_PASSWORD","device_name":"curl"}' \
  | jq -r '.access_token')

curl -s -X POST "$BASE/api/v1/events" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"API test gig","date":"2026-06-15","time":"20:00","venue_id":VENUE_ID}' | jq .
```

Success: HTTP body with `"data": { "id": ... }` and `"message": "Event created successfully."`

Find a venue id:

```bash
curl -s "https://www.mygigguide.co.za/api/v1/venues?search=johannesburg&per_page=5" | jq '.data[] | {id, name}'
```

---

## Product context (short)

- **My Gig Guide** — Laravel web + **API v1** + admin; data/identity SSOT.
- **Flutter app** (`mygigguide_app`) — flavors: `mygigguide`, `rogues`, `fm919`, `hot1027`; brand via `--dart-define=BRAND=...`.
- **Rogues on Radio** — noir/cyan UI, Radio tab, separate app id `za.co.mygigguide.rogues_demo`.
- **WhatsApp** — Evolution → n8n → miggs-bridge (separate from core DB).
- **Event discovery** — most gigs from **Facebook groups** (~30); native RSS for some sites; **FetchRSS** pilot (5 free feeds) — see [RSS_EVENT_DISCOVERY.md](./RSS_EVENT_DISCOVERY.md).

---

## Build commands Dave uses

**Flavor workflow:** develop and test on **`mygigguide`** (vanilla). After each **major step**, build **`rogues`** once to keep the white-label in step (same code, different brand/Radio tab).

**Release APK (auto build number — use this):**

Each run bumps the Android **build number** by 1 and updates `pubspec.yaml`. Check **Settings** in the app to confirm.

```bash
cd ~/development/mygigguide_app && ./scripts/build_apk.sh mygigguide
```

Rogues (major milestones only — sets `BRAND=rogues` and `SITE_URL=https://rogues.mygigguide.co.za`):

```bash
cd ~/development/mygigguide_app && ./scripts/build_apk.sh rogues
```

Latest on PC: check **Settings** in app after build (dark theme / logo glow milestone **23 May 2026**).

Optional human tag (only when you want one — shown in Settings):

```bash
cd ~/development/mygigguide_app && ./scripts/build_apk.sh mygigguide --label=logo-round
```

After changing launcher icons: `dart run flutter_launcher_icons` then `flutter clean` before the script.

**Example — vanilla APK (Dave runs locally, uploads to Drive):**

```bash
cd ~/development/mygigguide_app && ./scripts/build_apk.sh mygigguide
```

APK path: `build/app/outputs/flutter-apk/app-mygigguide-release.apk` — check **Settings** for build number.

**Rogues on device (debug):**

```bash
cd ~/development/mygigguide_app && ./tool/run_rogues_android.sh
```

**Vanilla on device (debug):**

```bash
cd ~/development/mygigguide_app && flutter run --flavor mygigguide --dart-define=BRAND=mygigguide --dart-define=SITE_URL=https://www.mygigguide.co.za
```

---

## What annoys / avoid

- Instructions to commit, push, or rsync when Dave only asked for **commands to run**.
- Losing thread after reboot — point Dave to **SESSION_HANDOFF.md**.
- Assuming Inoreader reads Facebook natively (it doesn’t without a feed URL).
- Nested `flavors:` under `flutter_launcher_icons` in pubspec — use **`flutter_launcher_icons-<flavor>.yaml`** files instead.
- `flutter build` without `--flavor` and expecting Rogues launcher/icon.

---

## Docs map

| File | Use |
|------|-----|
| [personal.md](./personal.md) | This file — how Dave likes to work |
| [SESSION_HANDOFF.md](./SESSION_HANDOFF.md) | Latest session state & “resume here” |
| [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md) | Architecture & backlog snapshot |
| [API_V1.md](./API_V1.md) | API contract |
| [DEPLOY_VPS.md](./DEPLOY_VPS.md) | Extra deploy notes (only if Dave asks) |
| [RSS_EVENT_DISCOVERY.md](./RSS_EVENT_DISCOVERY.md) | Feeds, FetchRSS, n8n plan |
| [MAIL_ADMIN_DB_SETUP.md](./MAIL_ADMIN_DB_SETUP.md) | **Pinned** — fix `/admin/mail-accounts` (MariaDB `mailuser`) |
| [VPS_CREDENTIALS.local.md](./VPS_CREDENTIALS.local.md) | **Local only** — VPS/mail/DB passwords (gitignored) |
| `mygigguide_app/docs/MOBILE_NATIVE_DETAIL.md` | Mobile detail screens plan |
