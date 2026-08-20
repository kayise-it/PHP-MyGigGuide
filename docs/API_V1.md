# Public JSON API v1

Base path: **`/api/v1`**

All endpoints return **JSON**. No authentication required for these read-only routes (add Sanctum / Firebase later for private data).

## Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/v1/meta` | App name + API version |
| GET | `/api/v1/categories` | Active event categories (`id`, `name`, `slug`) for filters / UI |
| GET | `/api/v1/events` | Paginated events (same filters as website listing) |
| GET | `/api/v1/events/{id}` | Single event (`upcoming` / `ongoing`; owners may also load their own past/cancelled gigs) |
| GET | `/api/v1/venues` | Paginated venues |
| GET | `/api/v1/venues/{id}` | Single venue (+ `upcoming_events`, next 90 days) |
| GET | `/api/v1/artists` | Paginated artists |
| GET | `/api/v1/artists/{id}` | Single artist (+ genres, `upcoming_events`, `posted_events`, next 90 days) |

**Artists / venues list** (`GET /api/v1/artists`, `GET /api/v1/venues`): optional query **`sort`** — `name` (default), `rating`, `events`, `newest`. Rows include **`rating_summary`** (`average`, `count`) and **`events_count`**. Matches website browse sorts (website venues also has capacity — not in app MVP).

**Mobile:** Native detail screens use the three `show` routes above.

### Authenticated endpoints (Sanctum bearer token)

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/v1/auth/login` | Login with username + password, returns bearer token |
| POST | `/api/v1/auth/register` | **Mobile-first signup** — name, email, password → creates Laravel `user`, returns bearer token |
| POST | `/api/v1/auth/firebase` | Login with Firebase ID token (user must be linked or same email) |
| POST | `/api/v1/auth/logout` | Revoke current bearer token |
| GET | `/api/v1/me` | Current user profile — `roles`, `email_verified`, `firebase_linked`, **`owned_pages`**, **`claimable_pages`**, `website_claim_url` |
| POST | `/api/v1/me/claims/initiate` | **Start email-matched page claims** (optional body `{ "type": "artist\|venue\|organiser", "id": 123 }`; omit for all matches) |
| POST | `/api/v1/me/claims/request` | **Manual claim request** when email does not match — `{ "type", "id", "message?" }` → pending admin review (no auto-approve) |
| POST | `/api/v1/me/web-session` | **App → website SSO** — one-time URL to open www signed in (optional `{ "redirect": "/dashboard" }`) |
| POST | `/api/v1/me/link-firebase` | Link Firebase UID to current user (requires bearer + `id_token`) |
| GET | `/api/v1/me/favorites` | Current user favorites (events, venues, artists, organisers). Events/venues/artists rows include optional **`image_url`** (poster / main picture / profile photo). |
| GET | `/api/v1/me/favorites/updates` | **In-app alerts (Phase 1)** — upcoming gigs for saved artists/venues/events. Query: optional **`since`** (ISO 8601 — new listings since last app visit), optional **`days`** (1–90, default 30). Each row includes `match_reasons`, `match_labels`, `is_reminder` (saved gig coming soon). |
| GET | `/api/v1/me/page-alerts` | **My pages alerts (Phase 1)** — upcoming **user-posted** gigs that tag a **claimed** artist, venue, or organiser page you own. Query: optional **`since`**, optional **`days`** (1–90, default 30). Excludes Quicket imports and gigs you posted yourself. Same row shape as favorites/updates (`match_reasons`, `match_labels`, `created_at`). |
| GET | `/api/v1/artists/{artist}/repertoire` | **Repertoire M1** — public song list for an artist (`title`, `original_artist`, `is_original`, optional `reference_youtube_id`). |
| GET | `/api/v1/me/artist/songs` | Owner repertoire (includes `notes`). Requires bearer. |
| POST | `/api/v1/me/artist/songs` | Add one song (`title`, optional `original_artist`, `is_original`, `youtube_url`, `spotify_url`, `notes`). |
| POST | `/api/v1/me/artist/songs/bulk` | Bulk paste — body `{ "text": "Artist - Title\\n..." }` → `{ summary: { created, skipped } }`. |
| PATCH | `/api/v1/me/artist/songs/{song}` | Update song fields. |
| DELETE | `/api/v1/me/artist/songs/{song}` | Remove song. |
| PATCH | `/api/v1/me/artist/songs/reorder` | Body `{ "song_ids": [3,1,2] }`. |
| GET | `/api/v1/me/artist/songs/spotify/search` | **Spotify v2** — search tracks (`q`, optional `limit` max 10). Requires `SPOTIFY_CLIENT_ID` / `SPOTIFY_CLIENT_SECRET`. |
| GET | `/api/v1/me/artist/songs/spotify/status` | `{ connected, connected_at }` — whether artist has linked Spotify for playlist import. |
| GET | `/api/v1/me/artist/songs/spotify/connect` | Returns `{ url }` — open in browser to connect Spotify (playlist import). Callback: `/spotify/callback`. |
| DELETE | `/api/v1/me/artist/songs/spotify/connect` | Disconnect Spotify from artist profile. |
| POST | `/api/v1/me/artist/songs/spotify` | Import Spotify **track or playlist** URL → `{ data, summary? }`. **Playlists require Connect Spotify first** (Spotify Feb 2026 API). |
| POST | `/api/v1/me/artist/songs/spotify/add` | Add one track by `{ "spotify_id": "..." }` (from search results). |
| GET | `/api/v1/events/{event}/live` | **Live M3** — active live sessions at this event. |
| GET | `/api/v1/artists/{artist}/live` | Current live session for artist (`data: null` if not live). |
| GET | `/api/v1/live-sessions/{session}` | Session detail + `pending_count`. |
| POST | `/api/v1/me/live-sessions` | **Go live** — `{ "event_id", "artist_id" }` (artist on event bill; owner or organiser). |
| POST | `/api/v1/live-sessions/{session}/end` | End live session. |
| GET | `/api/v1/live-sessions/{session}/queue` | Artist queue (fan name, song, status). |
| POST | `/api/v1/live-sessions/{session}/requests` | Fan request — `{ "artist_song_id"? , "message"? }` (logged in; session must be live). |
| GET | `/api/v1/live-sessions/{session}/my-requests` | Fan's requests in this session. |
| PATCH | `/api/v1/live-sessions/{session}/requests/{request}` | Update status: `pending`, `accepted`, `played`, `skipped`, `declined`. |
| GET | `/api/v1/events/{event}/board` | **Venue check-in V1** — tonight's board: lineup, LIVE sessions, check-in count. Optional bearer → `checked_in`. |
| POST | `/api/v1/events/{event}/check-in` | **Check in** (logged in; honor system — event day only). Returns board payload. |
| DELETE | `/api/v1/events/{event}/check-in` | **Undo check-in**. Returns board payload. |
| GET | `/api/v1/venues/{venue}/tonight` | Events at venue **today** with board payload per event. Optional bearer → `checked_in` per event. |
| POST | `/api/v1/me/favorites/{type}/{id}` | Add favorite (`type`: events\|venues\|artists\|organisers) |
| DELETE | `/api/v1/me/favorites/{type}/{id}` | Remove favorite |
| POST | `/api/v1/events/parse-poster` | **Read poster** — Groq vision via internal bridge (see below) |
| POST | `/api/v1/events` | **Create event** (requires `create-events` permission; see below) |
| PUT/PATCH | `/api/v1/events/{id}` | **Update own event** (same permission + ownership; see below) |
| DELETE | `/api/v1/events/{id}` | **Delete own event** (requires `delete-events` + ownership; see below) |
| POST | `/api/v1/artists` | **Quick-create artist** (same auth; for add-event crowd-source) |
| PATCH | `/api/v1/artists/{id}` | **Update artist page** (owner or admin; profile + videos; see below) |
| POST | `/api/v1/venues` | **Quick-create venue** (same auth; for add-event crowd-source) |
| PATCH | `/api/v1/venues/{id}` | **Update venue page videos** (owner or admin; see below) |
| POST | `/api/v1/ratings` | **Submit or update rating** (requires `rate-content`; 1–5 stars + optional review) |
| GET | `/api/v1/{type}/{id}/reviews` | Paginated reviews (`type`: `events` \| `artists` \| `venues`; query `offset`, `limit`) |

Event / artist / venue **`show`** responses include **`rating_summary`**: `{ average, count, user: { rating, review } | null }`. Send optional bearer token on GET to populate `user` for the signed-in account.

Artist / venue **`show`** also include ownership fields when a bearer token is sent: **`ownership_status`** (`official` \| `unclaimed` \| `pending` \| `disputed`), **`user_claim_pending`** (bool), **`can_request_claim`** (bool — logged-in user may call manual claim). **`show`** also returns **`can_edit_videos`** and **`can_edit_profile`** when the bearer may `PATCH` that page.

### Update artist page (`PATCH /api/v1/artists/{id}`)

**Auth:** `Authorization: Bearer {access_token}`.

**Who may edit:** approved page owner **or** admin/superuser (same as videos).

**Body (JSON or `multipart/form-data` when uploading `profile_picture`):**

| Field | Type | Notes |
|-------|------|--------|
| `stage_name` | string | Optional on PATCH; must stay unique |
| `real_name` | string | Optional |
| `genre` | string | Optional |
| `bio` | string | Optional; max 5000 chars |
| `phone_number` | string | Optional |
| `contact_email` | email | Optional |
| `instagram` | url | Optional |
| `facebook` | url | Optional |
| `twitter` | url | Optional |
| `tiktok` | url | Optional |
| `profile_picture` | file | Optional image (jpeg/png/gif/webp, max 10MB) |
| `youtube_videos[]` | url[] | Optional — replaces all videos (max 5). Send `[]` to clear. |

Send only fields you want to change. Omitted fields are left unchanged.

**Success:** `200 OK` — updated `ArtistResource` in `data` (includes contact + social fields).

**Errors:** `401` · `403` not owner · `422` validation

### Update artist videos only

Same endpoint — send only `youtube_videos[]` to replace video links without touching profile fields.

### Update venue videos (`PATCH /api/v1/venues/{id}`)

Same contract as **Update artist videos** — `youtube_videos[]` replaces all links (max 5). Owner = approved claim + listed on `venue_owners` or legacy `user_id` / polymorphic owner. Admin/superuser may edit any venue.

### Create event (`POST /api/v1/events`)

**Auth:** `Authorization: Bearer {access_token}` from `POST /api/v1/auth/login` or `POST /api/v1/auth/register`.

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
| `tiktok` | url | Event TikTok profile or video link |
| `capacity` | integer | |
| `category` | string | Legacy single category label |
| `categories[]` | integer[] | Category IDs (`GET /api/v1/categories`) |
| `artists[]` | integer[] | Artist IDs |
| `youtube_videos[]` | url[] | |
| `poster` | file | jpeg/png/gif/webp, max 10MB — Laravel also stores a **`poster_card`** portrait crop for list tiles |
| `gallery[]` | files | Up to 10 images |

**Event read fields (May 2026):** `poster_url` = full uploaded poster (or venue fallback). `poster_card_url` = server-generated 2:3 crop when a poster was uploaded via API/web — use for diary/list thumbnails; detail pages should use `poster_url`.

**Success:** `201 Created` — body is an `EventResource` wrapper:

```json
{
  "data": { "id": 123, "name": "...", "venue": { ... }, ... },
  "message": "Event created successfully.",
  "existing": false
}
```

**Likely duplicate (May 2026):** if the gig matches an existing listing (same `ticket_url`, or same **venue + date + similar title + start time within ~30 min**), the API returns **`200 OK`** with `"existing": true` and the **existing** event in `data` — **no new row** is created.

```json
{
  "data": { "id": 99, "name": "Friday Jazz Night", ... },
  "message": "Event already exists — using existing listing.",
  "existing": true
}
```

**Errors:** `401` unauthenticated · `403` missing permission or unverified account at login · `422` validation (e.g. missing `venue_id`)

### Parse poster (`POST /api/v1/events/parse-poster`)

**Auth:** same as create event (`Authorization: Bearer {access_token}`).

**Permission:** `create-events`.

**Body:** `multipart/form-data`

| Field | Type | Notes |
|-------|------|--------|
| `file` | file | Poster image (jpeg/png/webp/gif, max 8MB) |
| `hint` | string | Optional — extra text to help the vision model (e.g. typed description) |

Laravel forwards the upload to **miggs-bridge** on the VPS (`MIGGS_BRIDGE_URL`, default `http://127.0.0.1:8787`). The Groq API key stays on the bridge; the app never calls Groq directly.

**Success:** `200 OK` — bridge JSON, e.g.:

```json
{
  "ok": true,
  "parsed": {
    "name": "Friday Jazz Night",
    "artist": "Jazz Trio",
    "venue": "The Bassline",
    "date": "2026-06-01",
    "time": "20:00",
    "price": "0",
    "description": "",
    "categories": ["live-music"],
    "ticket_url": "https://tickets.example.com/gig"
  }
}
```

If the poster shows a booking or ticket URL, the bridge should include **`ticket_url`** (full `https://…` link). Laravel passes bridge JSON through unchanged; the app maps `ticket_url` into the Add event form. Updating the Groq prompt / n8n step on **miggs-bridge** is required for Read image to fill this automatically — no Laravel change beyond docs unless the bridge uses a different key (map it there).

**Failure:** `"ok": false` with `"error"` and optional `"poster_hint"` (same shape as the bridge).

**Errors:** `401` · `403` · `422` (missing/invalid file) · `502` (bridge error) · `503` (bridge not configured in Laravel `.env`)

**Server `.env` (VPS):**

```env
MIGGS_BRIDGE_URL=http://127.0.0.1:8787
MIGGS_BRIDGE_POSTER_SECRET=same-as-APP_POSTER_SECRET-on-bridge
```

### Update event (`PUT` / `PATCH /api/v1/events/{id}`)

**Auth:** same as create (`Authorization: Bearer {access_token}`).

**Permission:** `create-events` (member roles including plain `user`).

**Ownership:** only the user who posted the event (or admin/superuser) may update. Others receive **`403`**.

**Body:** same fields as create (`multipart/form-data` when uploading `poster` / `gallery[]`; JSON when no files). Sending `artists[]` or `categories[]` replaces the linked rows; omit to leave unchanged.

**Success:** `200 OK` — `EventResource` with `"message": "Event updated successfully."`

**`GET /api/v1/events/{id}`** includes **`user_can_edit`** (bool) when a bearer token is sent — `true` when the authenticated user owns the listing.

**`posted_by`** (object or `null`) — who listed the gig on event **`show`** responses:

| Field | Type | Notes |
|-------|------|--------|
| `name` | string | Display name |
| `username` | string \| null | Laravel username when set |
| `via` | string \| null | Page name when posted via artist/organiser (e.g. band name) |
| `owner_type` | string | `user` \| `artist` \| `organiser` |
| `page` | object \| null | `{ type, id, name, url }` when owner is an artist or organiser Page |

Admin/superuser listings omit `posted_by`. Follow + push roadmap: [NOTIFICATIONS_PLAN.md](./NOTIFICATIONS_PLAN.md).

**Errors:** `401` · `403` (not owner or missing permission) · `422` validation

### Delete event (`DELETE /api/v1/events/{id}`)

**Auth:** `Authorization: Bearer {access_token}`.

**Permission:** `delete-events` (member roles including plain `user`).

**Ownership:** only the user who posted the event (or admin/superuser) may delete. Others receive **`403`**.

**Success:** `200 OK` — `{ "message": "Event deleted successfully." }`

**App / web:** show delete when **`user_can_edit`** is true (same ownership rule).

**Errors:** `401` · `403` (not owner or missing permission)

### Firebase ↔ website account linking

**Server:** set `FIREBASE_WEB_API_KEY` in `.env` (Firebase Console → Project settings → Web API Key). Run migration `firebase_uid` on `users`.

**Typical mobile flow**

1. Sign in with **username + password** (`POST /api/v1/auth/login`) — same `users.id` as the website.
2. Sign in to **Firebase** in the app (Settings → App account).
3. `POST /api/v1/me/link-firebase` with body `{ "id_token": "<Firebase JWT>" }` and `Authorization: Bearer {sanctum}`.

After link, `GET /api/v1/me` returns `"firebase_linked": true`. You can then use `POST /api/v1/auth/firebase` with only the Firebase ID token to obtain a Sanctum token (auto-links by email if the Firebase email matches an existing user with no `firebase_uid` yet).

**Errors:** `422` email mismatch or UID already on another user · `404` on `/auth/firebase` when no matching website account · `503` when `FIREBASE_WEB_API_KEY` is missing

### `GET /api/v1/me` — pages (May 2026)

| Field | Type | Notes |
|-------|------|--------|
| `owned_pages` | array | Official pages (`claim_status` **approved**): `{ type, id, name, ownership_status, website_url, claim_url, claimable }` |
| `claimable_pages` | array | Unclaimed listings whose `contact_email` matches the user’s account email (same shape) |
| `website_claim_url` | string | Generic register URL on www |
| `email_verified` | boolean | Laravel `email_verified_at` set |

### `POST /api/v1/me/claims/initiate` — app claims (May 2026)

**Auth:** bearer token from login/register.

**Body (optional):**

| Field | Type | Notes |
|-------|------|--------|
| `type` | string | `artist`, `venue`, or `organiser` — required with `id` |
| `id` | integer | Listing id — claim one page; omit both fields to claim all email matches |

**Behaviour:** Same `ClaimService` flow as website register + verify. App registrations are verified immediately, so matching listings are usually **approved** in one step (grace period off by default). Unverified accounts get **pending** claims until they verify on www.

**Response (200):** `{ message, approved[], pending[], skipped[], errors[], owned_pages[], claimable_pages[], roles[] }`

**Errors:** `422` when nothing matches the account email (or the given type/id).

#### Example

```bash
curl -s -X POST "$BASE/api/v1/me/claims/initiate" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"type":"artist","id":42}' | jq .
```

### `POST /api/v1/me/claims/request` — manual claim (May 2026)

**Auth:** bearer token from login/register.

**Body:**

| Field | Type | Notes |
|-------|------|--------|
| `type` | string | `artist`, `venue`, or `organiser` |
| `id` | integer | Listing id |
| `message` | string | Optional — why you should manage this page (max 2000 chars) |

**Behaviour:** Sets `claim_status = pending` and `pending_claim_user_id` — **no** email match required, **no** auto-approve, **no** warning email to listing contact. Admin approves/rejects in **Unclaimed** edit screen.

**Response (200):** `{ message, pending[], errors[], owned_pages[], claimable_pages[] }`

**Errors:** `422` when page is already official, under review, or another user has a pending request.

#### Example

```bash
curl -s -X POST "$BASE/api/v1/me/claims/request" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"type":"artist","id":851,"message":"I am the band manager."}' | jq .
```

### `POST /api/v1/me/web-session` — app → website SSO (May 2026)

**Auth:** bearer token from login/register.

**Body (optional):**

| Field | Type | Notes |
|-------|------|--------|
| `redirect` | string | Path on www after sign-in, e.g. `/dashboard` or `/artists/851` (same host only) |

**Response (200):** `{ url, expires_in, redirect }` — open **`url`** in the device browser within **`expires_in`** seconds (default 300). Link is **single-use**.

**Web route:** `GET /auth/app-session?token=…&redirect=…` sets the normal Laravel **session cookie** and redirects.

#### Example

```bash
curl -s -X POST "$BASE/api/v1/me/web-session" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"redirect":"/dashboard"}'
```

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

### Quick-create artist (`POST /api/v1/artists`)

**Auth:** same as create event (`create-events` permission).

| Field | Required | Notes |
|-------|----------|--------|
| `stage_name` | yes | |
| `genre` | no | defaults to `Unknown` |
| `real_name` | no | |

Returns `201` (new) or `200` with `"existing": true` if stage name matches (case-insensitive).

### Quick-create venue (`POST /api/v1/venues`)

| Field | Required | Notes |
|-------|----------|--------|
| `name` | yes | |
| `address` | yes | |
| `city` | no | |
| `latitude`, `longitude` | no | |

Returns `201` (new) or `200` with `"existing": true` when name + address match.

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
- `posted_events` — **artist `show` only**: gigs this artist Page **listed** (`owner` = that artist), same shape and window as `upcoming_events`. Empty array when none. Distinct from `upcoming_events` (performing at / venue-hosted gigs).

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
