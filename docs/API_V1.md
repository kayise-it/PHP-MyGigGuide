# Public JSON API v1

Base path: **`/api/v1`**

All endpoints return **JSON**. No authentication required for these read-only routes (add Sanctum / Firebase later for private data).

## Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/meta` | App name + API version |
| GET | `/api/v1/events` | Paginated events (same filters as website listing) |
| GET | `/api/v1/events/{id}` | Single event (`upcoming` / `ongoing` only) |
| GET | `/api/v1/venues` | Paginated venues |
| GET | `/api/v1/venues/{id}` | Single venue |
| GET | `/api/v1/artists` | Paginated artists |
| GET | `/api/v1/artists/{id}` | Single artist (+ genres) |

### Query parameters

**Events**

- `search` — text search (name, description, venue, artists, categories)
- `category` — category id, slug, or name (same behaviour as web)
- `date_from`, `date_to` — `Y-m-d` (optional; default ~30-day window)
- `per_page` — max **50** (default 30)

**Venues / artists**

- `search` — text search
- `per_page` — max **50**

## Local testing (Sail)

```bash
./vendor/bin/sail up -d
curl -s "http://localhost/api/v1/meta" | jq .
curl -s "http://localhost/api/v1/events?per_page=5" | jq .
```

## Production deploy

1. `git pull` on the server (app directory).
2. `composer install --no-dev --optimize-autoloader` (or your standard command).
3. `php artisan migrate` (no destructive commands without backup).
4. `npm ci && npm run build` so the web UI assets exist (API does not depend on Vite, but the site does).
5. `php artisan config:cache` / `route:cache` if you normally cache in production.
6. Reload PHP-FPM or queue workers as you do today.

**Nginx/Apache:** must pass `/api/*` to Laravel’s `public/index.php` (same as other routes).

## Notes for mobile / WhatsApp

- **`detail_url`** fields point at **website** routes. Some pages require a **logged-in browser session** today; the API is the stable way to read event data without HTML.
- Image URLs use **`APP_URL`** + `storage` paths. Ensure **`php artisan storage:link`** has been run on the server and `APP_URL` matches the public site URL.
