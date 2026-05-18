# Public JSON API v1

Base path: **`/api/v1`**

All endpoints return **JSON**. No authentication required for these read-only routes (add Sanctum / Firebase later for private data).

## Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/meta` | App name + API version |
| GET | `/api/v1/categories` | Active event categories (`id`, `name`, `slug`) for filters / UI |
| GET | `/api/v1/events` | Paginated events (same filters as website listing) |
| GET | `/api/v1/events/{id}` | Single event (`upcoming` / `ongoing` only) |
| GET | `/api/v1/venues` | Paginated venues |
| GET | `/api/v1/venues/{id}` | Single venue (+ `upcoming_events`, next 90 days) |
| GET | `/api/v1/artists` | Paginated artists |
| GET | `/api/v1/artists/{id}` | Single artist (+ genres, `upcoming_events`, next 90 days) |

**Mobile:** Native detail screens use the three `show` routes above. Artist/venue upcoming gigs come from embedded `upcoming_events` (same fields as event list rows). See `mygigguide_app/docs/MOBILE_NATIVE_DETAIL.md`.

### Authenticated endpoints (Sanctum bearer token)

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/v1/auth/login` | Login with username + password, returns bearer token |
| POST | `/api/v1/auth/firebase` | Login with Firebase ID token (user must be linked or same email) |
| POST | `/api/v1/auth/logout` | Revoke current bearer token |
| GET | `/api/v1/me` | Current user profile (`firebase_linked` boolean) |
| POST | `/api/v1/me/link-firebase` | Link Firebase UID to current user (requires bearer + `id_token`) |
| GET | `/api/v1/me/favorites` | Current user favorites (events, venues, artists, organisers) |
| POST | `/api/v1/me/favorites/{type}/{id}` | Add favorite (`type`: events\|venues\|artists\|organisers) |
| DELETE | `/api/v1/me/favorites/{type}/{id}` | Remove favorite |
| POST | `/api/v1/events` | **Create event** (requires `create-events` permission; see below) |

### Create event (`POST /api/v1/events`)

**Auth:** `Authorization: Bearer {access_token}` from `POST /api/v1/auth/login` (verified email required at login).

**Permission:** Laratrust `create-events` (all member roles including plain `user` after May 2026).

**Content types:**

- **`multipart/form-data`** — when sending `poster` and/or `gallery[]` files (recommended for mobile).
- **`application/json`** — when there are no file uploads.

**Required fields**

| Field | Type | Notes |
|-------|------|--------|
| `name` | string | Event title |
| `date` | string | `Y-m-d`, today or later |
| `time` | string | `H:i` e.g. `19:30` |
| `venue_id` | integer | Must exist in `venues` table |

**Optional fields**

| Field | Type | Notes |
|-------|------|--------|
| `description` | string | |
| `price` | number | |
| `ticket_url` | url | |
| `capacity` | integer | |
| `category` | string | Legacy single category label |
| `categories[]` | integer[] | Category IDs (`GET /api/v1/categories`) |
| `artists[]` | integer[] | Artist IDs |
| `youtube_videos[]` | url[] | |
| `poster` | file | jpeg/png/gif/webp, max 10MB |
| `gallery[]` | files | Up to 10 images |

**Success:** `201 Created` — body is an `EventResource` wrapper:

```json
{
  "data": { "id": 123, "name": "...", "venue": { ... }, ... },
  "message": "Event created successfully."
}
```

**Errors:** `401` unauthenticated · `403` missing permission or unverified account at login · `422` validation (e.g. missing `venue_id`)

### Firebase ↔ website account linking

**Server:** set `FIREBASE_WEB_API_KEY` in `.env` (Firebase Console → Project settings → Web API Key). Run migration `firebase_uid` on `users`.

**Typical mobile flow**

1. Sign in with **username + password** (`POST /api/v1/auth/login`) — same `users.id` as the website.
2. Sign in to **Firebase** in the app (Settings → App account).
3. `POST /api/v1/me/link-firebase` with body `{ "id_token": "<Firebase JWT>" }` and `Authorization: Bearer {sanctum}`.

After link, `GET /api/v1/me` returns `"firebase_linked": true`. You can then use `POST /api/v1/auth/firebase` with only the Firebase ID token to obtain a Sanctum token (auto-links by email if the Firebase email matches an existing user with no `firebase_uid` yet).

**Errors:** `422` email mismatch or UID already on another user · `404` on `/auth/firebase` when no matching website account · `503` when `FIREBASE_WEB_API_KEY` is missing

#### Example: login + create (JSON, no poster)

```bash
BASE="https://www.mygigguide.co.za"

TOKEN=$(curl -s -X POST "$BASE/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"username":"YOUR_USERNAME","password":"YOUR_PASSWORD","device_name":"curl"}' \
  | jq -r '.access_token')

curl -s -X POST "$BASE/api/v1/events" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Friday Jazz Night",
    "description": "Live quartet",
    "date": "2026-06-01",
    "time": "20:00",
    "venue_id": 16,
    "price": 0,
    "categories": [1, 3]
  }' | jq .
```

#### Example: create with poster (multipart)

```bash
curl -s -X POST "$BASE/api/v1/events" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -F "name=Poster Night" \
  -F "date=2026-06-15" \
  -F "time=19:00" \
  -F "venue_id=16" \
  -F "poster=@/path/to/poster.jpg"
```

**Mobile:** Use the same fields from `AddGigScreen` after Laravel login (not Firebase). Pick `venue_id` from `GET /api/v1/venues?search=...`.

### Query parameters

**Events**

- `search` — text search (name, description, venue, artists, categories)
- `category` — category id, slug, or name (same behaviour as web)
- `date_from`, `date_to` — `Y-m-d` (optional; default ~30-day window)
- `per_page` — max **50** (default 30)

**Venues / artists**

- `search` — text search
- `per_page` — max **50**

**Artist / venue `show` only**

- `upcoming_events` — array of event objects (same shape as `GET /events` rows): upcoming/ongoing gigs in the **next 90 calendar days**, ordered by date/time (max 100).

## Local testing (Sail)

```bash
./vendor/bin/sail up -d
curl -s "http://localhost/api/v1/meta" | jq .
curl -s "http://localhost/api/v1/events?per_page=5" | jq .
```

## Auth testing example

```bash
# 1) Login (username-based)
TOKEN=$(curl -s -X POST "http://127.0.0.1:8000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"username":"mobileuser1","password":"Password123!","device_name":"local-cli"}' | jq -r '.access_token')

# 2) Current user
curl -s "http://127.0.0.1:8000/api/v1/me" \
  -H "Authorization: Bearer $TOKEN" | jq .

# 3) Favorites
curl -s "http://127.0.0.1:8000/api/v1/me/favorites" \
  -H "Authorization: Bearer $TOKEN" | jq .

# 4) Create event (replace venue_id)
curl -s -X POST "http://127.0.0.1:8000/api/v1/events" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"CLI Test Gig","date":"2026-12-01","time":"20:00","venue_id":1}' | jq .
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
