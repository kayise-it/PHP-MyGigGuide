# Quicket import

Pull public Quicket listings into My Gig Guide. We **link out** via `ticket_url` — we do not sell tickets.

**Status:** artisan dry-run + `--apply` (Jul 2026). Category map for Music / Sports / Travel / Family / Arts & Culture / Other.

## Where to put the API key

**Local** (`~/development/PHP-MyGigGuide/.env`) and **VPS** (`/var/www/mygigguide/.env`):

```env
QUICKET_API_KEY=your-subscriber-key-here
QUICKET_BASE_URL=https://api.quicket.co.za/api/events
QUICKET_CATEGORIES=1
QUICKET_PROVINCES="Gauteng,Western Cape,KwaZulu-Natal,Free State,Eastern Cape"
# Required only for --apply (events.owner_id):
QUICKET_OWNER_USER_ID=123
```

- Key from [Quicket developer portal](https://developer.quicket.co.za/) (subscriber / API key).
- Never commit the real key; `.env.example` has placeholders only.
- Quote `QUICKET_PROVINCES` when values contain spaces (dotenv).
- After editing `.env` on VPS: `php artisan config:clear`

Optional: `QUICKET_PAGE_SIZE`, `QUICKET_TIMEOUT`, `QUICKET_PROVINCES=` (empty / `--all-provinces` = no province filter).

**Default for manual pulls:** keep `QUICKET_CATEGORIES=1` in `.env`. Nightly cron passes `--categories` explicitly (Music / Sports / Family / Arts & Culture) — do **not** merge into one env value (tagging fallback uses the first category id).

Quicket category IDs: `1` Music, `5` Sports & Fitness, `6` Travel & Outdoor, `9` Arts & Culture, `30` Family & Education, `64` Other (miscategorised culture/cabaret → `theatre`). Travel (`6`) and Other (`64`) cron stay manual-only for now.

## How it works

```
Quicket API → quicket:pull → map fields → (dry-run report)
                              → --apply: match/create venue → dedupe by ticket_url → Event::create
                              → attach MGG categories via category_slug_map
```

| Quicket field | Our field |
|---------------|-----------|
| `name` | `events.name` |
| `description` (stripped HTML) | `events.description` |
| `startDate` | `date` + `time` (Africa/Johannesburg) |
| lowest non-donation ticket `price` | `events.price` |
| `url` | `events.ticket_url` (dedupe key) |
| `categoryId` / `category.id` (or request filter) | maps to MGG slugs |
| `venue.*` + `locality.*` | match existing venue or create **unclaimed** venue |

## Category map (Quicket → MGG)

Configured in `config/quicket.php` → `category_slug_map`:

| Quicket ID | MGG slugs |
|------------|-----------|
| `1` Music | `live-music`, `quicket` |
| `5` Sports & Fitness | `sports`, `quicket` |
| `6` Travel & Outdoor | `travel-outdoor`, `quicket` |
| `30` Family & Education | `family-friendly`, `quicket` |
| `9` Arts & Culture | `theatre`, `quicket` |
| `64` Other (culture/cabaret miscategorised) | `theatre`, `quicket` |

Create missing MGG categories before `--apply` for non-music (`php artisan db:seed --class=CategorySeeder` or admin). Organic / user-posted events keep whatever categories they choose (**not** forced Quicket).

Deprecated env fallback: `QUICKET_MGG_CATEGORY_SLUGS=live-music,quicket` (used only when map has no entry).

## Seed then cron

**Do not** blast all pages from cron on day one.

1. **Manual seed (once per province / category expansion):**  
   `php artisan quicket:pull --apply --page=1 --max-pages=NN --sleep=2 --categories=N`  
   (use dry-run page 1 to read API `pages` count)
2. **Cron (ongoing):** four staggered jobs in `routes/console.php` (Africa/Johannesburg), each first 3 pages, provinces from `QUICKET_PROVINCES`:
   - **03:20** Music `--categories=1` → `storage/logs/quicket-pull-music.log`
   - **03:35** Sports `--categories=5` → `storage/logs/quicket-pull-sports.log`
   - **03:50** Family `--categories=30` → `storage/logs/quicket-pull-family.log`
   - **04:05** Arts & Culture `--categories=9` → `storage/logs/quicket-pull-arts.log`  
   Crontab (user `dave`): `* * * * * cd /var/www/mygigguide && php artisan schedule:run >> /dev/null 2>&1`

**One-off seeds (manual):**

```bash
# Other (64) — dry-run first; Dave Jul 2026: 12 pages
php artisan quicket:pull --page=1 --max-pages=12 --categories=64
php artisan quicket:pull --apply --page=1 --max-pages=12 --sleep=2 --categories=64

# Arts & Culture (9) — dry-run page 1 to read API pages count (NN), then seed
php artisan quicket:pull --page=1 --categories=9
php artisan quicket:pull --apply --page=1 --max-pages=NN --sleep=2 --categories=9
```

Run poster-writing pulls as **`dave`**, not `www-data` (storage ownership).

Provinces (Gauteng, Western Cape, KwaZulu-Natal, Free State, Eastern Cape) are inherited from `QUICKET_PROVINCES` on VPS — cron does not re-seed geography. New listings appear gradually via the nightly first-3-pages walks.

## Commands

```bash
# Smoke-test key + mapping (no DB writes)
php artisan quicket:pull --page=1

# Override Quicket category for one run (does not change .env / cron)
php artisan quicket:pull --page=1 --categories=5
php artisan quicket:pull --page=1 --categories=6
php artisan quicket:pull --page=1 --categories=30

# Wider geography for one dry-run
php artisan quicket:pull --page=1 --all-provinces

# Write one page (needs QUICKET_OWNER_USER_ID) — downloads banner → poster
php artisan quicket:pull --apply --page=1

# Full seed after dry-run shows page count
php artisan quicket:pull --apply --page=1 --max-pages=42 --sleep=2

# Fix older Quicket imports that have no poster
php artisan quicket:backfill-posters
php artisan quicket:backfill-posters --force --apply

# Regenerate 2:3 poster_card from existing local posters (landscape banners included — no re-download)
php artisan quicket:backfill-posters --cards-only
php artisan quicket:backfill-posters --cards-only --force --apply --limit=500

# Attach categories to existing Quicket-linked events
php artisan quicket:backfill-categories
php artisan quicket:backfill-categories --quicket-category=1 --apply
php artisan quicket:backfill-categories --quicket-category=5 --apply
php artisan quicket:backfill-categories --quicket-category=30 --apply
php artisan quicket:backfill-categories --quicket-category=9 --apply
php artisan quicket:backfill-categories --quicket-category=64 --apply
```

## Later

- Tune nightly `--max-pages` if listings move slowly
- Travel (`6`) and Other (`64`) still manual-only; add staggered cron for `64` only after seeds look sane
- Venue main_picture via Google Places (on create + `quicket:backfill-venue-photos`)

## Duplicate venues (merge + prevention)

Quicket imports can create duplicate venue rows when legacy CSV rows lack `city` or sit just outside the 400m proximity window.

**One-time cleanup:**

```bash
# Preview merges (keeper = most events; copies missing city/coords/photo)
php artisan venues:merge-duplicates --dry-run

# Apply auto merges (skips ambiguous groups flagged REVIEW)
php artisan venues:merge-duplicates --apply

# Include review-flagged groups after manual check
php artisan venues:merge-duplicates --apply --include-review

# Single group
php artisan venues:merge-duplicates --dry-run --group=groundthevenue
```

**Import matching (Jul 2026):** `resolveVenue` now also matches on `google_place_id`, normalized name + address, and legacy rows with empty `city`.
