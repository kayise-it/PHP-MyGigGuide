# Expanding outside South Africa

Notes on how My Gig Guide could grow internationally — from a **user**, **database**, and **app** perspective.

**Created:** May 2026 · **Status:** planning / reference (not started)

Related: [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md) · [SESSION_HANDOFF.md](./SESSION_HANDOFF.md)

---

## Summary

**Technically:** the architecture (Laravel as SSOT, Sanctum users, lat/lng events, white-label hostnames like Rogues) fits international expansion better than many apps at this stage.

**Product-wise:** success depends on **local content density**, not opening the map worldwide on day one.

**Pragmatically:** treat it like Rogues — **one market at a time**, same codebase, a bit of DB scoping, and loosening SA-only Places/geocode rules.

---

## What we have today (SA assumptions)

| Layer | What’s SA-specific today |
|--------|---------------------------|
| **Venues** | `city`, lat/lng — **no `country` column** yet |
| **Google Places** | Web + app restrict to **`country:za`** |
| **Geocoding** | App often appends **“, South Africa”** |
| **Map defaults** | Johannesburg-centred when there’s no data |
| **Venue picker (web)** | Popular cities list is SA only |
| **Money** | Paid features default to **ZAR** |
| **Domain / brand** | `mygigguide.co.za`, Rogues tenant on subdomain |
| **Users** | One global `users` table — **not** tied to a country |

Events, artists, venues, claims, and the API are already **geographic** (coordinates). That’s the right foundation.

**Key code touchpoints (when implementing):**

- App: `lib/services/google_places_service.dart` (`components: country:za`)
- App: `lib/services/venue_geocoder.dart`, `lib/services/user_location_helper.dart` (`, South Africa` suffix)
- Web: `resources/views/events/create.blade.php`, venue create/edit (Google `componentRestrictions: { country: 'za' }`)
- Web: `resources/views/components/venue-selector.blade.php` (popular SA cities)
- Web: `resources/views/components/google-map.blade.php` (default centre Johannesburg)

---

## How to think about it (product)

**Don’t flip “international” on everywhere at once.** Gig listings are hyper-local — empty maps in a new city hurt more than not launching there yet.

Suggested sequence:

1. **Pick one pilot market** (e.g. UK, Australia, or a city with a partner).
2. **Prove density** there (posters, venues, a local champion).
3. **Then** widen app discovery / marketing in that region.

### Deployment models

| Option | Description |
|--------|-------------|
| **A — One app, many regions** | User picks “My area” or uses GPS; single `mygigguide` app |
| **B — Regional brands** | Like Rogues: `uk.mygigguide…` or partner domain, own nav colours |
| **C — Both** | One codebase; market from hostname or app flavor |

For a small team: **A first, B when you have a local partner** is usually enough.

---

## Database / Laravel (SSOT)

Minimal, practical changes — **one database**, filtered by region (no separate DB per country at first).

### 1. Add a market / region concept

- **`venues.country_code`** — ISO 2-letter (`ZA`, `GB`, `AU`)
- Optional **`events.timezone`** — or inherit from venue/market (important once not all on SA time)
- **`users.preferred_market`** or **`home_country_code`** — default map, “near me”, empty-state copy

Events can inherit country from venue (most gigs have a venue with coordinates).

**Backfill:** existing venues → `ZA`.

### 2. Scope queries by region

Add optional filters on list/map API endpoints:

- By **country_code**
- By **bounding box** or **lat/lng + radius** (align with existing “near me” patterns)

**Default:** user’s GPS country, saved preference, or **`ZA`** until they change it — so SA users see no change.

### 3. Keep one user account

Same email/username worldwide (like most platforms). Optional:

- **`users.country_code`** at signup (or rough inference from IP)
- Claims/ownership rules unchanged — “this venue in Bristol” works like “this venue in Sandton”

### 4. Payments / boosts (later)

Paid features already have a `currency` column (default ZAR). Per market you’d need:

- **ZAR vs GBP vs AUD** (etc.) packages
- **Stripe (or similar) per region** — often the fiddliest part, not the listings

---

## App (Flutter)

Mostly **configuration + UX**, not a rewrite.

| Area | Change |
|------|--------|
| **Google Places** | Stop hard-coding `country:za`; use user’s market or device locale |
| **Geocoding** | Drop automatic “, South Africa”; use country from preference |
| **Home / map** | Default centre from GPS or chosen city, not Joburg |
| **Browse “Near me”** | Already GPS-based — works anywhere if events exist there |
| **Empty states** | “No gigs near you yet — be the first to post” per region |
| **Settings** | **“My area”** — country + city (or “Use my location”) |
| **API calls** | Pass `country=` or `lat/lng/radius` on event list/map requests |

Build-wise: stay on **one `mygigguide` flavor** with a market setting, unless a partner wants their own icon (then reuse the Rogues flavor pattern).

---

## Content & ops (often underestimated)

Going international is less about code and more about:

- **Who posts the first ~50 gigs** in the new city
- **RSS / WhatsApp / imports** — discovery today is very SA / Facebook-group oriented; each market needs its own sources (see [RSS_EVENT_DISCOVERY.md](./RSS_EVENT_DISCOVERY.md))
- **Moderation / claims** — same admin tools, but time zones and unfamiliar venues
- **Legal** — privacy policy, store listings; **GDPR** if EU/UK

---

## Suggested baby-step roadmap

| Phase | Work | SA impact if default stays ZA |
|-------|------|-------------------------------|
| **1** | DB: `venues.country_code`, backfill `ZA` | None |
| **2** | API: optional `country` / geo filter on event index + map | None |
| **3** | App: Settings → “My area”; remove `country:za` lock when area ≠ ZA | None for SA-only users |
| **4** | Pilot: seed one foreign city (or partner posts there) | — |
| **5** | Marketing, payments, regional branding if it sticks | — |

Phases 1–3 are a **small, safe milestone** before any public “we’re in the UK” announcement.

---

## Open questions (pick when ready)

- Which **pilot market** (city + partner)?
- **Single global app** vs **regional subdomain** vs **partner white-label**?
- **Timezone** on events: store explicitly or derive from venue coordinates?
- **Payments**: defer until listings density exists in that market?

---

## One-line takeaway

Same Laravel core and Flutter app; add **country scoping**, **user “my area”**, and **local content** one market at a time — not a big-bang global launch.
