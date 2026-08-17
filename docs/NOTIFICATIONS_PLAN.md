# Notifications plan — Saved pages & new gigs

**Purpose:** Roadmap from **Phase A (Posted by)** to push alerts when followed artists, venues, or poster Pages list new gigs.

**Related:** [API_V1.md](./API_V1.md) (`posted_by` on events) · [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md) (OneSignal wired in app, UI later)

---

## How the pieces connect

| Layer | Today | Next |
|-------|--------|------|
| **Attribution** | `posted_by` on event detail (web + API + app) with optional **Page link** (artist / organiser) | Phase C: **Follow** Pages + push when new gigs |
| **Interest** | **Saved** (Sanctum favorites: events, artists, venues) | Rename/extend to **Follow** on **Pages** only (not user-to-user) |
| **Alerts** | None productised | Push when a followed **artist**, **venue**, or **organiser Page** gains a new upcoming gig |

**Yes — venue gigs and artist gigs are the core notification triggers.**  
`posted_by.page` is the third trigger (promoter / curator / Rogues host) once that Page is followable.

---

## Phase A — Posted by ✅

- **API:** `GET /api/v1/events/{id}` → `posted_by`: `{ name, username, via, owner_type, page? }`
- **Web:** Event detail — “Posted by … · via [Page]” with link when `page.url` exists
- **App:** Same line; artist Page opens native detail; organiser opens web URL until organiser detail exists
- **Privacy:** Admin/superuser posters hidden; no emails exposed

---

## Phase B — More from this poster (small) ✅

- Artist / organiser show pages: block **“Gigs they posted”** (events where `owner` matches that Page)
- **API:** `posted_events` on `GET /api/v1/artists/{id}`; **App:** section on artist detail
- **Web:** `page-posted-events` component on artist + organiser show
- Home/marketing: optional **curator strip** for Rogues partners (manual config first) — not built yet

---

## Phase C — Notification preferences (backend)

**New table (sketch):** `notification_subscriptions`

| Column | Notes |
|--------|--------|
| `user_id` | Subscriber |
| `subscribable_type` | `artist` \| `venue` \| `organiser` |
| `subscribable_id` | Page id |
| `channel` | `push` (later `email`) |
| `enabled` | bool |

**Migration path from Saved:** When user favorites an artist/venue, offer **“Notify me about new gigs”** toggle (default off). Favorites table stays; subscriptions are additive.

**On `Event` created/approved (upcoming):**

1. Match event `venue_id` → notify venue subscribers  
2. Match `event_artist` → notify artist subscribers  
3. Match `owner` when artist/organiser Page → notify Page subscribers  

Debounce: one push per event per user even if they follow both venue and headliner.

---

## Phase D — App push (OneSignal)

**Already in app:** OneSignal dependency (per PRODUCT_CONTINUITY); per-brand keys **later**.

| Step | Work |
|------|------|
| 1 | Settings → **Notifications**: toggles per follow, master on/off |
| 2 | On follow + notify opt-in → register device token with Laravel (`POST /api/v1/me/notification-devices`) |
| 3 | Laravel job sends OneSignal REST payload: title, body, deep link `mygigguide://events/{id}` |
| 4 | Rogues flavor: separate OneSignal app id |

**Deep links:** Event detail (existing). Later: artist/venue detail from push action.

---

## Phase E — In-app notification centre (optional)

- Bell icon + list of recent alerts (read/unread)
- Can follow after push MVP if store listing needs it

**Phase 1 (in-app, no OneSignal) — coded May 2026:**

- `GET /api/v1/me/favorites/updates` — upcoming gigs for saved artists/venues/events (optional `since`, `days`)
- App: home banner **New for your saves** + **Me → Favs → Upcoming for your saves**
- “Dismiss” stores `last_seen` on device (SharedPreferences per user id)

---

## What we are *not* building

- Follow random **users** (only **Pages**)
- Influencer feed ranked by follower count
- DMs or comments on posts

---

## Parked — “My pages” alerts (Jul 2026)

**Idea:** When someone creates a **user-posted** event that tags a **claimed** artist or venue you own, show an in-app alert (extend existing 🔔, tab **“My pages”** — not a second envelope icon).

**Rules when built:**
- Claimed pages only; **no** Quicket/import cron spam
- User-created events only for v1
- One notification per event; “since last seen” badge like fav updates

**UI:** Single bell → sections `Saves` | `My pages` (reuse Phase E in-app centre).

**Backend sketch:** On `Event` create → notify claim owners via `GET /api/v1/me/page-alerts` + mark seen.

**Priority:** After Rise FM polish / Mix FM / AMFI — not blocking station launches.

---

## Suggested order for Dave

1. **Deploy Phase A** (Laravel + app `posted_by` with Page links)  
2. **Phase B** when a promoter asks “where’s my other gigs?”  
3. **Phase C + D** when Saved/follow usage justifies push (or Rogues launch needs “notify me for Rogues listings”)

EOF
