# Product continuity — My Gig Guide (May 2026)

Short-lived **memory for humans and agents**: what exists, how it fits together, and what is still open. Update when major pieces move.

**Last updated:** 7 Jun 2026

**Resume after reboot:** [SESSION_HANDOFF.md](./SESSION_HANDOFF.md) · **Dave’s preferences:** [personal.md](./personal.md) · Cursor rule: `.cursor/rules/dave-context.mdc`

**Architecture diagrams (Jun 2026):** [Client-facing](./architecture-client.png) · [Technical / Kayise](./architecture-technical.png) · [AI usage — build, product & future](./ai-usage-infographic.png) · [AI features — client](./ai-usage-client.png) — horizontal flow; WhatsApp, Flutter (MGG + Rogues), website → Laravel SSOT. *(Technical AI infographic omits WhatsApp speech-to-text via Grok — see WhatsApp row below.)*

---

## Three surfaces (one Laravel data core)

| Surface | Role | Notes |
|--------|------|--------|
| **Website (Laravel)** | Public MVP: listings, event/artist/venue detail, admin. | **API v1** on same app (`/api/v1/...`). Session auth for web; **Sanctum** bearer for programmatic clients. **Home** hero: **split gig calendar + map**; embedded map JSON now includes **`poster_url`** + **`images`** (gallery) so clients can show thumbnails. **`/map`** is public (guests). |
| **Mobile app (Flutter)** | `mygigguide_app` — separate repo. | **My Gig Guide** (`--flavor mygigguide`): **dark theme (May 2026)** — black scaffold, indigo `#6366F1`, flat cards `#12121A`, **black coverflow**, rounded **logo glow** (`header_logo_glow.png`). Tabs: **Home**, **Events**, **Browse**, **Add**, **Saved**, **Settings**. Browse sort: All, Gigs Near Me, **Top rated**, **Most events**. **Ratings** on detail (compact). **Rogues** (`--flavor rogues`): noir + **cyan**; dark bottom nav; **Radio** tab; Add under Settings. Home: coverflow + map + genre chips; Events tab = diary; WhatsApp share; Saved = Sanctum favorites. Native API detail screens. See **SESSION_HANDOFF** for glow → favicon/app icon (pinned). |
| **WhatsApp** | Lite touch: queries, voice, light flows. | **Evolution** → **n8n** → **miggs-bridge** (Python on VPS) → Groq / live site scraping. **Voice notes:** speech-to-text via **Grok (xAI)** in the WhatsApp path (Dave — note for future docs/infographic). Not a second source of truth for core DB data. |

### Add event poster (mobile)

- **Choose image** or **Paste image** (clipboard → temp file) on Add event; poster goes up with `POST /api/v1/events` when you post.
- **Read poster** — **live (May 2026):** app → Laravel `POST /api/v1/events/parse-poster` → internal miggs-bridge → Groq. Config: `MIGGS_BRIDGE_*` in Laravel `.env`. See **API_V1.md**.

**Canonical identity:** Laravel **`users.id`** is SSOT. Web = **session**; mobile = **Sanctum bearer** (not auto SSO to web). App signup → **`user`** immediately; web signup → email verify + **claim match** by email for artist/venue/organiser **Pages**. Posting a gig links to listings; **ownership = claim + verification** (web/admin today). See **SESSION_HANDOFF** § Product — Pages model.

<a id="event-categories"></a>

### Event categories (web ↔ mobile)

- **API:** `GET /api/v1/categories` lists active site categories (`id`, `name`, `slug`). Details in **[API_V1.md](./API_V1.md)**.
- **Website:** category dropdown on **home** and **events** filters listings by category slug (same taxonomy as the API).
- **Mobile (`mygigguide_app`):** **`EventsCategoryFilterBar`** — horizontal **All** + category chips from `GET /api/v1/categories`. On **Home** (Browse + Map) and **Settings**, choice persists in **`SearchPrefs.events_category_slug`**. **Events tab** uses its **own** local category filter (does not drive Home/map). Map pins filtered client-side by category slugs on the payload; **genre chips** on Home Map share the same prefs as Browse.

---

## Repos and deploy

- **`PHP-MyGigGuide`** — Laravel web + **API v1** + admin + migrations. Production updated **May 2026** via **rsync** to `/var/www/mygigguide` (`main` on GitHub). Deploy steps: **[DEPLOY_VPS.md](./DEPLOY_VPS.md)**. Nginx must route **`/api/*`** to Laravel. `git pull` on VPS still needs deploy key for `dave`.
- **`mygigguide_app`** — Flutter client; ships on its own cadence. Launcher icons: per-flavor `flutter_launcher_icons-<flavor>.yaml` in app root, then `dart run flutter_launcher_icons` (writes `android/app/src/<flavor>/res/`). See `assets/images/brands/README.md`.

API reference: **[API_V1.md](./API_V1.md)**.

---

## VPS / integrations (not in Laravel repo)

- Docker: **n8n**, **evolution-api**, **miggs-bridge**, Postgres, etc. Compose project historically under **`/home/limpho/miggs-bot`** (access may be restricted).
- **miggs-bridge** image should include **`lxml`** (BeautifulSoup); align container Python with **`local-agent-grok`** when Thando rebuilds.
- Evolution → n8n webhooks: use hostname **`evolution-api`** (not `evolution`) inside Docker; **`sendMedia`** for images vs **`sendText`** for text.

---

## Still open / backlog (named elsewhere too)

- **Auth** — Sanctum + app signup/login **live**; app skips email verify; **claims on web only**; **app→website SSO pinned**.
- **Ratings** — API + mobile detail UI **live**; moderation later.
- **Browse sort** — coded; **Top rated** / **Most events** (`sort=`); deploy if not on VPS.
- **Mobile dark theme** — **MGG tested (23 May)**; Rogues nav/legibility fixes; **web dark ~2–3 wk** pinned.
- **Brand assets** — `header_logo_glow.png` in app; **favicon + launcher icon** from same asset pinned.
- **RSS / n8n** — event discovery via n8n (+ optional RSSHub on VPS). Most gigs from **FB groups**; **FetchRSS** 5-feed pilot OK to start. **n8n issue on backlog** (WhatsApp + RSS workflows) — discuss with Dave when ready. See **[RSS_EVENT_DISCOVERY.md](./RSS_EVENT_DISCOVERY.md)**.
- **Repeat / multi-day events** — **when required:** (1) weekly repeat e.g. Karaoke Thursdays — Laravel + web + API, not app-only; (2) multi-day festivals via `end_date`; (3) full iCal/RRULE — **unlikely**. Tracked in **SESSION_HANDOFF.md** § Repeat / multi-day events.
- **Hermes** — local experimentation; not a product dependency.
- **White-label** — **Rogues subdomain live:** https://rogues.mygigguide.co.za. **Web Phase A (May 2026):** hostname → `SiteBrand` (Rogues logo, dark nav, cyan accents, favicon, footer “Powered by My Gig Guide”); **same event data** as www until content silo. Mobile Rogues uses `SITE_URL` / `brand_config.dart`. **919 FM web:** planned — see **[WEB_APP_ALIGNMENT.md](./WEB_APP_ALIGNMENT.md)**. FM tenants TBD in app stores.
- **Mobile push** — OneSignal wired in app; per-brand keys and notification centre UI **later** (Rogues / FM tenants).
- **Nav** — optional merge of **Artists + Venues** into one **Browse** tab (segment toggle) to reduce bottom-bar count after **Saved** added.
- **App stores** — daily dev on **`mygigguide`** flavor; **Rogues** rebuilt at major milestones; Rogues may still be first store listing (paying client). Tracked in **SESSION_HANDOFF.md** § App store release.

---

## One-line summary

We have a **mobile app in development**, a **mostly MVP website** (same Laravel app powers **API v1**; full **auth/sync story** for mobile vs web is the main remaining product thread), and a **WhatsApp lite-touch** path via **Evolution + n8n + miggs-bridge** — all anchored on **Laravel as data and identity SSOT**.
