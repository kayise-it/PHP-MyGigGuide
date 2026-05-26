# Session handoff — resume after reboot

**Updated:** 23 May 2026 — dark indigo app theme tested; logo glow; product/identity notes; reboot handoff

Use this with **[personal.md](./personal.md)**, **[UPDATE_LOG.md](./UPDATE_LOG.md)** (progression history), and **[PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md)**.

**Prior chats:** Cursor transcripts `97b111b1-f74c-4c01-bb2d-ee5dc7798a89` (ratings/sort/dark) + this session (theme polish, identity advice).

---

## ▶ After reboot — pick up here

**Milestone (23 May 2026):** **My Gig Guide dark theme** tested OK on phone — indigo accent, black coverflow, flat dark cards, rounded **logo glow** in header. **Rogues** gets same dark-nav + legibility fixes (cyan accent unchanged).

**Dave said next:** **Identity walkthrough** — new user vs existing Laravel user; artist pages + venue claims (discussion only so far; see § Product — Pages model below).

**Still to deploy (when ready):**

1. **Browse sort API** — rsync block below (if not already on VPS).
2. Optional: `./scripts/build_apk.sh mygigguide --label=logo-round` if phone not on latest glow build.

**No git commits** unless Dave asks.

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
