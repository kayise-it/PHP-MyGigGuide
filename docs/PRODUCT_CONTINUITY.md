# Product continuity — My Gig Guide (May 2026)

Short-lived **memory for humans and agents**: what exists, how it fits together, and what is still open. Update when major pieces move.

---

## Three surfaces (one Laravel data core)

| Surface | Role | Notes |
|--------|------|--------|
| **Website (Laravel)** | Public MVP: listings, event/artist/venue detail, admin. | **API v1** on same app (`/api/v1/...`). Session auth for web; **Sanctum** bearer for programmatic clients. **Home** hero: **split gig calendar + map**; embedded map JSON now includes **`poster_url`** + **`images`** (gallery) so clients can show thumbnails. **`/map`** is public (guests). |
| **Mobile app (Flutter)** | `mygigguide_app` — separate repo. | **My Gig Guide** (`--flavor mygigguide`): six tabs including **Add**. **Rogues** (`--flavor rogues`, `BRAND=rogues`): **noir + cyan** theme, **Rogues logo** (launcher + in-app header), bottom **Radio** tab (**live stream** + native **Our Hosts & Shows**); **Add** under **Settings**. Diary day-range uses **pills** (no segment checkmark). Lazy **`data-src`** card images supported when merging posters. **Native detail (Phase 1–2):** Events / Artists / Venues / home calendar open **API-backed read-only screens**; artist/venue detail include **`upcoming_events`** on `show`. Website via **Open on website** only. Plan: `mygigguide_app/docs/MOBILE_NATIVE_DETAIL.md`. Skim: [**Event categories**](#event-categories) (web ↔ mobile). |
| **WhatsApp** | Lite touch: queries, voice, light flows. | **Evolution** → **n8n** → **miggs-bridge** (Python on VPS) → Groq / live site scraping. Not a second source of truth for core DB data. |

**Canonical identity:** Laravel **`users.id`** is the single source of truth for accounts. Web proves identity with **session**; mobile with **Sanctum Bearer token**; same row for both. **Firebase** on mobile remains optional / parallel until explicitly linked to Laravel users.

<a id="event-categories"></a>

### Event categories (web ↔ mobile)

- **API:** `GET /api/v1/categories` lists active site categories (`id`, `name`, `slug`). Details in **[API_V1.md](./API_V1.md)**.
- **Website:** category dropdown on **home** and **events** filters listings by category slug (same taxonomy as the API).
- **Mobile (`mygigguide_app`):** **`EventsCategoryFilterBar`** — horizontal **All** + category chips, loaded from that endpoint. Shown on **Home**, **Events** (API directory), and **Settings**. Choice is persisted as **`events_category_slug`** (`SearchPrefs` / `AppPrefs`). **`GET /api/v1/events`** and in-app **events** web URLs include a **`category`** query parameter when a slug is set; **artist/venue** directory calls and URLs stay unfiltered by category. On the **home map**, the bar filters map pins **client-side** using each event’s category slugs from map/API payload — separate from **genre** filters on the map sheet (labeled **Genre** there to avoid mixing the two).

---

## Repos and deploy

- **`PHP-MyGigGuide`** — Laravel web + **API v1** + admin + migrations. Branch for rolling work: **`Dave_Dev`** (or current branch name). Deploy: Thando / VPS — `composer`, `migrate` (incl. `personal_access_tokens`), `npm run build`, `storage:link`, Nginx must route **`/api/*`** to Laravel.
- **`mygigguide_app`** — Flutter client; ships on its own cadence. After changing launcher assets: `dart run flutter_launcher_icons`.

API reference: **[API_V1.md](./API_V1.md)**.

---

## VPS / integrations (not in Laravel repo)

- Docker: **n8n**, **evolution-api**, **miggs-bridge**, Postgres, etc. Compose project historically under **`/home/limpho/miggs-bot`** (access may be restricted).
- **miggs-bridge** image should include **`lxml`** (BeautifulSoup); align container Python with **`local-agent-grok`** when Thando rebuilds.
- Evolution → n8n webhooks: use hostname **`evolution-api`** (not `evolution`) inside Docker; **`sendMedia`** for images vs **`sendText`** for text.

---

## Still open / backlog (named elsewhere too)

- **Auth unification** — Laravel-first SSO / linking later; mobile already caches `users.id` + username beside token (`AppPrefs` + `LaravelApi`) for future link flows.
- **RSS** — event discovery feed (todo).
- **Hermes** — local experimentation; not a product dependency.
- **White-label** — site home/footer placeholders and “Powered by My Gig Guide” copy direction; tenant logos TBD.

---

## One-line summary

We have a **mobile app in development**, a **mostly MVP website** (same Laravel app powers **API v1**; full **auth/sync story** for mobile vs web is the main remaining product thread), and a **WhatsApp lite-touch** path via **Evolution + n8n + miggs-bridge** — all anchored on **Laravel as data and identity SSOT**.
