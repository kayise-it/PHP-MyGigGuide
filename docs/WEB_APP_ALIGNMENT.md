# Web ↔ app alignment (plan)

Dave’s direction (Jun 2026): bring **www.mygigguide.co.za** closer to the **Flutter home** experience, then add **919 FM** as a hostname tenant (like Rogues).

**Related:** [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md) · Rogues playbook in [SESSION_HANDOFF.md](./SESSION_HANDOFF.md) § Rogues subdomain · `app/Support/SiteBrand.php`

---

## Goal (one sentence)

**Home = gigs first** (gallery/calendar + map), **browse = artists/venues toggle**, **tenants = hostname skin** — same Laravel data until a client pays for a silo.

---

## Current gap

| | **App (Home tab)** | **Website (`/` today)** |
|--|-------------------|-------------------------|
| **Hero** | Coverflow / diary cards + optional map | Large marketing headline + map + 3 big grids below |
| **Events** | Primary focus; `/events` = full diary | Featured events grid on home |
| **Artists / venues** | **Browse** tab — one screen, toggle | Separate “Featured Artists” + “Popular Venues” sections |
| **Map** | Toggle on home | Map in hero, but competing with grids |
| **919 FM** | `fm919` flavor in app | **App:** closed-test ready (May 2026). **Web:** `919fm.mygigguide.co.za` — Phase 2 |

**Already in repo (partial):** `resources/views/components/home-event-calendar.blade.php` — calendar component exists; home blade still uses old layout + three grids.

---

## Decisions (Jun 2026)

| Question | Answer |
|----------|--------|
| **919 hostname** | `919fm.mygigguide.co.za` |
| **Schedule UI** | **Poster coverflow gallery** (scroll-snap + scale) with **Today / Week / Month / 3 months** + **genre chips**; calendar removed (Jun 2026 refresh) |
| **Rogues new home** | Same layout when ready (SiteBrand already styles calendar/map); not blocking Phase 1 |
| **919 logos** | Copy from `mygigguide_app/assets/images/brands/fm919/` when Phase 2 starts |

---

## Phase 1 — www home reshape ✅ coded (Jun 2026, refreshed)

- **Gigs | Map** toggle (Alpine), default Gigs
- **Gigs** — poster **coverflow** (`home-event-coverflow`) + date presets + category chips + focused gig detail
- **Map** — existing `<x-google-map>` + links to full map / event diary
- **Browse** — **Artists | Venues** + **Most gigs | Top rated** sort (`home-browse-section`)
- Removed calendar + “Coming up” strip
- **Files:** `home.blade.php`, `HomeController.php`, `home-event-coverflow.blade.php`, `home-coverflow.js`, `home-browse-section.blade.php`, `SiteBrand.php`, `VenueController.php` (rating sort)

**Deploy:** rsync views + controller + `SiteBrand.php`, then on VPS `npm run build` + `php artisan view:clear`.

---

## Recommended order (baby steps)

### Phase 1 — Reshape **www** home only (do this first)

**Why first:** Validates UX on one hostname before cloning skin work to 919.

1. **Slim or drop** the rotating “Discover Amazing Events/Artists…” hero — app doesn’t lead with marketing copy.
2. **Above the fold:** app-like **Schedule | Map** toggle (Alpine/JS, same pattern as app shell):
   - **Schedule** — horizontal gig cards or month calendar (`home-event-calendar`) fed from existing `$mapEvents` / `$calendarByDate` in `HomeController`.
   - **Map** — keep current `<x-google-map>` (already has poster pins).
3. **Optional row under toggle:** category chips (reuse events index pattern) — matches app genre filter later.
4. **Replace** the three stacked sections (events + artists + venues grids) with **one Browse block**:
   - Toggle: **Artists | Venues**
   - Grid of ~8 cards from existing `$artists` / `$venues` queries
   - “View all” → `/artists` or `/venues`
5. **Events** — link “See all gigs” → `/events` (diary page), don’t duplicate a full grid on home.

**Files (estimate):** `home.blade.php`, `HomeController.php`, maybe one new Blade component `home-browse-toggle.blade.php`, small JS in `resources/js/app.js`.

**Deploy:** rsync views + `npm run build` + `php artisan view:clear` (see [DEPLOY_VPS.md](./DEPLOY_VPS.md)).

**Out of scope for Phase 1:** Near me geolocation on web (nice later; app uses GPS + saved prefs).

---

### Phase 2 — **919 FM** subdomain (copy Rogues)

**Why second:** Mechanical repeat of Rogues Phase A; no home layout dependency.

1. **Hostname** — agree with Kayise/client: e.g. `fm919.mygigguide.co.za` or `919.mygigguide.co.za` (match app `brand_config.dart` / station site `919.co.za`).
2. **DNS** — A record → VPS (`41.61.20.39`).
3. **nginx** — duplicate `rogues.mygigguide.co.za` vhost → new server_name, same `root` `/var/www/mygigguide/public`.
4. **Laravel** — extend `SiteBrand::current()`:
   - `fm919()` method — logo, favicon, accent colours (yellow from app), tagline, footer “Powered by My Gig Guide”.
   - Assets under `public/logos/` (get 919 artwork from client or app `assets/images/brands/fm919/`).
5. **Tests** — add `SiteBrandTest` case like Rogues.
6. **Same event data** as www until Phase B silo is scoped (Rogues is still shared data).

**App alignment:** `--flavor fm919` + `SITE_URL=https://fm919.mygigguide.co.za` when subdomain live.

**Out of scope for Phase 2:** Radio stream embed, WhatsApp links, competition URLs — app already has station tab; web can add a “Listen live” link in nav/footer as a tiny follow-up.

---

### Phase 3 — Polish (when Phase 1 feels good)

- Dark theme on www (pinned elsewhere — ~2–3 weeks when ready).
- Home map pin layer: events vs venues (app has this on map tab).
- Rogues + 919 home use same layout as www (only `SiteBrand` colours/logos differ).

---

## What not to do yet

- **Don’t** split databases per tenant until product says so — skin-only tenants are working (Rogues proof).
- **Don’t** rebuild artists/venues as separate home sections again — defeats the toggle idea.
- **Don’t** block Phase 1 on 919 DNS — they’re independent.

---

## Suggested next session (when coding)

1. Wire `home-event-calendar` + Schedule/Map toggle into `home.blade.php`.
2. Remove or shrink the three featured grids → single Artists/Venues toggle block.
3. Screenshot compare with app Home tab.
4. Only then start 919 `SiteBrand` + nginx.

---

## Open questions for Dave / Kayise

1. **919 hostname** — `fm919.mygigguide.co.za` vs `919.mygigguide.co.za`?
2. **919 logo files** — do you have web-ready PNG/SVG from the station?
3. **Home calendar vs coverflow** — calendar is already built; coverflow-style horizontal scroll is closer to app but more JS — preference?
4. **Rogues home** — should Rogues subdomain get the new home layout at the same time as www?
