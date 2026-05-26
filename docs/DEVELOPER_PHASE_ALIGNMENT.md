# Alignment with Kayise / developer scope

**Meeting brief — 16 May 2026** (for Thando / Limpho).  
Source PDF: `docs/Alignment with Kayise _ developer scope (1).pdf` · this file is the **live** status.

---

## 60-second headline (say first)

1. **Phase 1 web MVP is live** on VPS (auth, listings, favourites, admin, capture forms).
2. **Laravel API v1 + Flutter app** were not in the original Phase 1 quote — **built by Dave**; API is **partly live on production** (read + login + favourites + **create event**); mobile app is **usable on device** (native detail, favourites, post events). Formal **git → production** process still needed.
3. **Kayise decisions today:** written deploy/branch process, production auth support (Sanctum/CORS), DNS spec for white-label subdomains, Phase 3 maintenance scope.

---

## What changed since the PDF you have

| Area | Then (PDF) | Now (May 2026) |
|------|------------|----------------|
| Laravel API | “Not catered for; needs go live” | **Partially live** on `mygigguide.co.za` — `GET` listings/detail/categories/venues; **Sanctum** login/logout; favourites; **`POST /api/v1/events`** (create gig from app). Deploy so far: **file copy + migrate**, not full git pipeline. |
| Flutter app | “Separate repo / in progress” | **Working builds** (My Gig Guide + **Rogues** flavor): Home coverflow browse + map, WhatsApp share, native detail + gallery coverflow, category filters, **Add event** via API, hearts when logged in. Rogues `SITE_URL` → `rogues.mygigguide.co.za`. Release APK via Drive. |
| Identity | “Needs discussion” | **Agreed model:** Laravel `users.id` = source of truth. Web = session; mobile = **Sanctum bearer**. **Firebase ↔ Laravel link** coded (not finished on phone — see pin below). |
| Spider (#7) | Not started | **Direction:** RSS + **n8n** (FetchRSS pilot for FB groups) — replaces spider; **not built yet**. |
| Advertising (#5) | Partial / mobile | **Rogues** white-label in app (branding, radio tab, stream); full ad portal still not started. |

---

## 📌 PIN — resume later (not for this meeting)

**Paused:** Link Firebase app account ↔ website user (Settings → “One account (app + website)”).

**Resume when:** (1) new APK installed with linking UI, (2) VPS has `firebase_uid` migration + `FIREBASE_WEB_API_KEY`, (3) API link routes deployed.  
**Handoff:** [SESSION_HANDOFF.md](./SESSION_HANDOFF.md) · say in Cursor: *“Continue from SESSION_HANDOFF.md — Firebase linking”*.

---

## Phase 1 — Core web app

| # | Item | Status |
|---|------|--------|
| 1 | Authentication & sign-up (role-based) | **Delivered** — Laravel + Laratrust (user, artist, venue, organiser, admin, etc.). |
| 2 | Landing / gallery of upcoming events | **Delivered** — home + listings; mobile Home = coverflow browse + full-screen map (May 2026); web calendar/map on home; minor UX / white-label tweaks ongoing. |
| 3 | App deployment | **Delivered; live** — VPS; DNS / SSL per hosting. |
| 4 | Database & capture forms | **Delivered** — events, venues, artists, admin flows. |
| 5 | Advertising portal (white label) | **Not started / partial** — Rogues-style branding + radio in **mobile**; full portal TBD. |
| 6 | Ratings, favourites, reviews | **Delivered** — favourites on **web + API + mobile**; ratings present; reviews depth limited. |
| 7 | Spider for venues/artists | **Not started** — direction: **RSS + n8n** (see Phase 2); Excel/import scripts used ad hoc for venues. |
| 8 | Event sponsorship integration | **Partial / started** — paid-feature building blocks. |
| 9 | Move to VPS | **Done** (extra unforeseen work). |
| 10 | Laravel API | **Live (May 2026)** — public JSON + Sanctum + **POST /api/v1/events** on VPS. Deployed via **rsync**; `main` on GitHub. Ongoing: `git pull` on server, Kayise process. See [API_V1.md](./API_V1.md). |

---

## Phase 2 — Additional scope (beyond core Phase 1)

### A. Self-developed or parallel work (not in Phase 1 PDF)

| # | Item | Status |
|---|------|--------|
| 1 | WhatsApp assistant (Evolution → n8n → bridge → LLM / site) | **Live** — Dave developed; Kayise assisted go-live; **n8n script to push updates** still to align. |
| 2 | Flutter mobile app + Laravel API v1 + Sanctum | **Advanced — separate repo** (`mygigguide_app`). Native detail, favourites, categories, **create event via API**, Rogues flavor. **Not in stores yet**; APK testing. **Firebase ↔ Laravel link:** coded, deploy/APK pending (pinned above). |
| 3 | RSS / event-discovery automation | **Planned** — FetchRSS pilot (5 feeds) + n8n; doc [RSS_EVENT_DISCOVERY.md](./RSS_EVENT_DISCOVERY.md); **workflow not built**. |

### B. Kayise IT optional add-ons (next commercial scope)

| # | Item (as in PDF) | Price (PDF) | Status |
|---|------------------|-------------|--------|
| 1 | Advanced user analytics & insights | R2 500 | Not started — on request. |
| 2 | Subscription-based artist & venue profiles | R1 500 / month | Not started — on request. |
| 3 | Event ticketing system | R3 500 | Not started — on request. |
| 4 | Payment gateway integration | R3 000 | Not started — on request. |
| 5 | Custom branding & white labelling (venues/artists) | R5 000 | **Partial** — Rogues mobile flavor; full web white-label TBD. |
| 6 | Advanced search filters & sorting | R1 500 | **Partial** — category filters web + mobile; advanced sort TBD. |
| 7 | Push notifications & alerts | R1 000 | Not started — on request. |

**Phase 3 (PDF):** maintenance R1 000 / month; custom features R2 000 each — **discuss today** (who owns API/mobile deploys, SLA).

---

## Ask Kayise today (concrete)

| # | Topic | What we need |
|---|--------|----------------|
| 1 | **Deploy / git** | Written process: production branch, who merges, who runs `migrate` / queue, rollback, env template, repo + SSH access. **Goal:** API/mobile go live without ad-hoc `scp` calls. |
| 2 | **Auth on production** | Confirm **Sanctum bearer** for mobile is OK (CORS, `SANCTUM_STATEFUL_DOMAINS` if any web SPA later). Who implements server config vs app. |
| 3 | **White-label DNS** | **Rogues live:** `rogues.mygigguide.co.za` (Dave, May 2026). Pattern for FM919/HOT1027 TBD with Kayise. |
| 4 | **WhatsApp / n8n** | Access to deploy script or agreed path to update bridge/n8n on VPS. |
| 5 | **Commercial** | Phase 3 maintenance: does it cover **API + mobile** releases, or web-only? |

---

## Current development (detail for Thando / Limpho)

| # | Item | What / where |
|---|------|----------------|
| 1 | Laravel API, Sanctum | **On VPS** (`/api/v1/...`, create event). PC: **`main`** commit `5860450`. Deploy: rsync worked 18 May 2026; enable `git pull` when deploy key added. |
| 2 | Subdomains for white-labelling | **Rogues:** `rogues.mygigguide.co.za` live (HTTPS). Other tenants — spec TBD. |
| 3 | Authentication web & mobile | **Model agreed:** `users.id` SSOT; web session; mobile Sanctum. Firebase optional on device; **link to Laravel** in progress (pinned). Confirm production support. |
| 4 | GitHub + go live | **Blocking item** — see table above. Target: no more one-off file copies for API. |
| 5 | Python / WhatsApp update script | Access to push bridge files live; Dave can work in n8n. |

---

## Optional: export PDF for Kayise

Regenerate from this markdown when you want an updated PDF beside the original in `docs/`.

*Internal continuity:* [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md) · [SESSION_HANDOFF.md](./SESSION_HANDOFF.md)
