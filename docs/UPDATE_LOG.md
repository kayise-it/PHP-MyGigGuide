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

## Status legend

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
| **5** | Manual claim (no email match) | **Tested** | `POST /me/claims/request`; admin approve via disputes |
| **6** | Edit own events (app) | **Planned** | Mirror web edit; API `PUT/PATCH` + app screen — see SESSION_HANDOFF pinned § |

---

## Log (newest first)

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
| `logo-round` | Dark theme + header glow |
| `claim-pages` | Phase 2 banner (website claim link) |
| `claims` | Phase 3 in-app **Claim now** |
| `gallery` | Add-event gallery UX |
| `chips` | Event detail category chip legibility fix |
