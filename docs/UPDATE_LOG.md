# Update log — My Gig Guide progression

**Purpose:** One chronological record of what we built, tested, and deployed. Newest entries at the top.

**Related docs (different jobs):**

| Doc | Use for |
|-----|---------|
| [SESSION_HANDOFF.md](./SESSION_HANDOFF.md) | Resume after reboot — *what to do next*, deploy rsync blocks |
| [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md) | Architecture and product rules |
| [API_V1.md](./API_V1.md) | API reference |
| [DEVELOPER_PHASE_ALIGNMENT.md](./DEVELOPER_PHASE_ALIGNMENT.md) | Kayise contract / commercial phases (not day-to-day dev log) |

**How to maintain:** After meaningful work, add a dated entry here (1–5 bullets). Update status. Keep deploy commands in SESSION_HANDOFF — link to the section, don’t duplicate long rsync blocks here.

---

## 5 Aug 2026 — Claim notifications: admin email + WhatsApp (Evolution API)

| Area | What | Status |
|------|------|--------|
| **ClaimNotificationService** | Sends admin email via `ClaimPendingAdminMail` + optional WhatsApp via Evolution API (self-hosted) | Done — local |
| **Email notifications** | Fires for both manual and email-match claims (`initiateClaimsForUser`) | Done |
| **WhatsApp** | Silently skipped unless `EVOLUTION_API_URL`, `EVOLUTION_API_KEY`, and `EVOLUTION_ADMIN_NUMBER` are set in VPS `.env` | Ready — set env vars to activate |
| **Config** | `config/services.php` evolution block + `.env.example` entries for `ARTIST_CLAIM_ADMIN_EMAIL`, `EVOLUTION_API_URL`, `EVOLUTION_INSTANCE`, `EVOLUTION_API_KEY`, `EVOLUTION_ADMIN_NUMBER` | Done |

**To activate on VPS:** Set `ARTIST_CLAIM_ADMIN_EMAIL=dave@...` for email. Set the four `EVOLUTION_*` vars for WhatsApp (uses your existing Evolution API + n8n stack on the VPS).

---

## 5 Aug 2026 — VOW FM (VOW 88.1) flavor scaffold

| Area | What | Status |
|------|------|--------|
| **Flavor** | Gradle `vowfm` · `applicationId` `za.co.mygigguide.vowfm` · `ic_launcher_background` `#1A3870` (dark navy) | **Coded** |
| **Brand** | `isVowFm` · teal `#29B8B0` accent · dark navy `#1A3870` primary · dark chrome · Radio tab | **Coded** |
| **Stream** | iono.fm station 101 · `https://edge.iono.fm/xice/101_medium.aac` · quality helper `vowFmStreamUrlForQuality` | **Coded** |
| **Map** | Default centre Wits campus `-26.1929, 28.0305` (Braamfontein) | **Coded** |
| **Engagement** | WhatsApp placeholder (empty — get number from station) · competition/feedback/podcast URLs · Facebook + Instagram social | **Coded** |
| **Assets** | `store-assets/vow/Logo4-scaled.jpg` → `assets/images/brands/vowfm/header_logo.png` · `flutter_launcher_icons-vowfm.yaml` | **Coded** |
| **Build** | `./scripts/build_apk.sh vowfm --label=v1` (do not build yet — needs square PNG app_icon) | **Dave runs** |

**Note:** `app_icon.png` is the landscape JPEG from `store-assets/vow/`. Dave needs a proper square PNG for the app icon before `dart run flutter_launcher_icons -f flutter_launcher_icons-vowfm.yaml`.

**Missing / Dave to provide:** WhatsApp studio number · square app icon PNG · verify stream plays · station website for schedule scraper (later).

---

| Status | Meaning |
|--------|---------|
| **Planned** | Agreed direction, not started |
| **Coded** | On disk in dev repo(s), not necessarily on VPS/phone |
| **Deployed** | Live on VPS and/or APK installed |
| **Tested** | Dave confirmed on phone or www |

---

## Identity / Pages roadmap (May 2026)

Goal: trust labels on web → richer `/me` → claims from app → SSO.

| Step | What | Status | Notes |
|------|------|--------|-------|
| **1a** | Web ownership badges (Official / Unclaimed / …) | **Deployed + tested** | Badge fix: Official = `claim_status` approved |
| **1b** | Claim CTA on unclaimed artist/venue pages | **Deployed + tested** | Amber banner; register prefill |
| **2** | `GET /me` — `owned_pages`, `claimable_pages` + app banner | **Deployed** (Laravel); app **coded** | Banner when email matches `contact_email` |
| **3** | `POST /me/claims/initiate` + app **Claim now** | **Tested** | Dave claimed Shades1 on phone |
| **4** | App → website SSO (one-time session link) | **Tested** | Settings → Account on website |
| **4b** | Web Google sign-in (Firebase, same project as app) | **Tested + deployed** | www + Rogues; deferred email verify |
| **5** | Manual claim (no email match) | **Tested** | `POST /me/claims/request`; admin approve via disputes |
| **6** | Edit own events (app) | **Planned** | Mirror web edit; API `PUT/PATCH` + app screen — see SESSION_HANDOFF pinned § |

---

## Log (newest first)

### 23 Jul 2026 — Play AD_ID + home gallery week fix

- **Flutter Play fix:** `AndroidManifest.xml` strips Facebook SDK ad permissions (`AD_ID`, `ACCESS_ADSERVICES_*`) and removes Facebook init components; Play Console declaration stays **No** for advertising ID. Rebuild: `./scripts/build_aab.sh mygigguide|fm919 --label=play-no-ad-id`.
- **Laravel home gallery:** `HomeController` week window inclusive 7 days; hero limit 500 for Week (was 60 — truncated busy weeks).
- **Flutter gallery sort:** Home coverflow chronological even when Near me on (radius filter only).
- **Quicket cron:** 3 new overnight = normal delta; gaps = category 64/6 not on cron, province allowlist.
- **Status:** **Coded** — see [SESSION_HANDOFF](./SESSION_HANDOFF.md) pick-up block.

### 22 Jul 2026 — Quicket clear-cards + letterbox restore

- **Laravel:** `--clear-cards` clears all Quicket `poster_card` (non-Quicket untouched); import/backfill skip inventing landscape crops.
- **Flutter:** list thumbs / diary strip use `contain` + `coverflowBackdrop` when no card; `cover` only when `poster_card_url` present.
- **Dave next:** rsync + `quicket:backfill-posters --clear-cards` (dry-run then `--apply`); rebuild `risefm --label=letterbox-posters`.
- **Status:** **Coded** — see [SESSION_HANDOFF](./SESSION_HANDOFF.md) pick-up block.

### 21 Jul 2026 — Rise FM Flutter flavour scaffold

- **Flutter** (`~/development/mygigguide_app`): Gradle flavor `risefm`, `applicationId` `za.co.mygigguide.risefm`, Radio tab (919 pattern), iono stream `73_medium.aac`, Mbombela map default, hosts catalog, accent `#EC1C24`, logo from `store-assets/risefm/Rise-FM.png`.
- **Build:** `./scripts/build_apk.sh risefm --label=v1` (Dave). No Play Store / Laravel tenant yet.
- **Status:** **Coded** locally.

### 20 Jul 2026 — Quicket Family (30) + Free State / Eastern Cape

- **Config:** `category_slug_map` 30 → `family-friendly` + `quicket`; default `QUICKET_PROVINCES` includes Free State + Eastern Cape.
- **Docs / seeder / tests:** `QUICKET_IMPORT.md`, `.env.example`, CategorySeeder, unit test for map entry.
- **Cron:** still Music-only (`QUICKET_CATEGORIES=1`) — do not add 30.
- **Status:** **Coded** locally; VPS deploy + province env + Family dry-run pending (SSH).

### 3 Jun 2026 — Web Google auth aligned with app (tested + deployed)

- **Laravel:** Firebase Google on `/login` + `/register`; `POST /auth/firebase`; `last_login_at` + deferred email verify (first web session OK); signup always `user` role; auth modal real Google button.
- **VPS:** `FIREBASE_WEB_API_KEY` + Firebase authorized domains; `npm run build`; migration `last_login_at`.
- **Status:** **Tested** — www + Rogues Google sign-in; dashboard redirect. App → web SSO unchanged. Cosmetic polish pinned.

### 19 May 2026 — Phase 5: manual claims (coded)

- **Laravel:** `POST /api/v1/me/claims/request`; migration `claim_request_message`; ownership fields on artist/venue API show; admin approve/reject on unclaimed edit.
- **Flutter:** **Request to manage this page** on artist + venue detail screens.
- **Tests:** `tests/Feature/Api/V1/MeManualClaimApiTest.php`.
- **Status:** **Coded** — deploy rsync in [SESSION_HANDOFF § Deploy Phase 5](./SESSION_HANDOFF.md).

### 19 May 2026 — Post sign-up website offer (app)

- After **Create account**, one-time dialog: **Open website** → SSO (same as Settings).
- **Flutter:** `website_sso.dart`, `laravel_account_screen.dart`.
- **Status:** **Tested** — Dave confirmed after Create account.

### 19 May 2026 — Phase 4: app → website SSO (tested)

- **Laravel:** `POST /api/v1/me/web-session` issues single-use link; `GET /auth/app-session` sets web session cookie.
- **Flutter:** Settings **Account on website** opens signed-in when app bearer exists.
- **Tests:** `tests/Feature/Api/V1/MeWebSessionApiTest.php`.
- **Status:** **Tested** — Dave confirmed Settings → Account on website works.

### 19 May 2026 — Phase 3: claims from app API (tested)

- **Laravel:** `POST /api/v1/me/claims/initiate` — optional `{ type, id }` or claim all email matches; uses `ClaimService`; verified users auto-approved (grace period off).
- **Flutter:** `LaravelApi.initiateClaims()`; claim banner **Claim now** + reload after login.
- **Status:** **Tested** — Dave claimed artist Shades1 on phone.

### 23 May 2026 — Dark theme (MGG) + logo glow

- **Flutter:** Always-dark MGG UI — indigo `#6366F1`, black scaffold/coverflow, cards `#12121A`, rounded header logo glow.
- **Rogues:** Same dark nav + detail legibility; cyan accent unchanged.
- **Status:** **Tested** on phone (MGG). Rebuild: `--label=logo-round`.

### 23 May 2026 — Identity Phase 2 (`/me` + app banner)

- **Laravel:** `ApiMeProfileService` — `owned_pages`, `claimable_pages` on `GET /me`.
- **Flutter:** `ClaimPagesBanner`, Settings copy, dismiss per user.
- **Status:** **Deployed** (Laravel); app banner **coded** (`--label=claim-pages`).

### 23 May 2026 — Identity Phase 1a / 1b (web trust + claim CTA)

- **1a:** `page-ownership-badge` on artist/venue show; Official only when `claim_status === approved`; venue import no longer assigns bogus `user_id=1`.
- **1b:** `page-claim-cta` amber banner; register email prefill.
- **VPS data (one-time):** approve pre-linked artists; clear 224 venues with `user_id=1`.
- **Status:** **Deployed + tested** on www.

### 22 May 2026 — Rogues web Phase A + Saved thumbnails

- **Web:** `SiteBrand` hostname skin on `rogues.mygigguide.co.za` (logo, dark nav, cyan).
- **API + app:** `image_url` on favourites; thumbnails in Saved tab.
- **Identity smoke:** Register + sign-in on phone OK.
- **Status:** **Deployed + tested**.

### 21 May 2026 — Home milestone + Rogues parity

- **App:** iTunes-style coverflow browse; map toggle; genre chips; WhatsApp share bar; event gallery coverflow.
- **Rogues:** `SITE_URL=https://rogues.mygigguide.co.za`; APK build 27.
- **Infra:** Rogues subdomain + nginx vhost live.
- **Status:** **Deployed** (Rogues APK built).

### 19 May 2026 — Ratings + crowd posting

- **API:** Ratings submit + paginated reviews; `rating_summary` on show endpoints.
- **App:** Compact rating card on detail screens; browse sort chips (Top rated / Most events) **coded**.
- **Add event:** Gallery uploads, size labels, form clear after post; any member can post (`EnsureApiPermission`).
- **Status:** **Tested** (ratings, gallery, posting).

### 20 May 2026 — Identity decisions (product)

- One Laravel `user` for app + web; app register without email-verify gate; same password for existing web users; guest cannot add events; SSO pinned for later; Rogues same rules as MGG.
- Captured in SESSION_HANDOFF § Identity decisions.

---

## Pinned / not started

| Item | Notes |
|------|--------|
| **Phase 4 SSO** | One-time web session from app |
| **Glow logo → favicon + app icon** | Before store listing |
| **Website dark theme** | Public Tailwind sweep; admin can stay light |
| **Event curation** | After crowd posting stable |
| **Browse sort API deploy** | If not already on VPS — see SESSION_HANDOFF |

---

## App build labels (quick reference)

| Label | Roughly contains |
|-------|------------------|
| `play-no-ad-id` | Facebook SDK ad permissions stripped; Play advertising ID declaration **No** |
| `gallery-chrono` | Home coverflow date order + web week window fix (Laravel rsync separate) |
| `logo-round` | Dark theme + header glow |
| `claim-pages` | Phase 2 banner (website claim link) |
| `claims` | Phase 3 in-app **Claim now** |
| `gallery` | Add-event gallery UX |
| `chips` | Event detail category chip legibility fix |
