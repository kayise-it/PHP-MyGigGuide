# My Gig Guide — product roadmap

**Updated:** 27 Jul 2026 (evening)  
**Repos:** Laravel `PHP-MyGigGuide` · Flutter `mygigguide_app`  
**Companion docs:** [SESSION_HANDOFF.md](./SESSION_HANDOFF.md) · [NOTIFICATIONS_PLAN.md](./NOTIFICATIONS_PLAN.md) · [ARTIST_LIVE_REQUESTS_PLAN.md](./ARTIST_LIVE_REQUESTS_PLAN.md)

Dave’s original feature checklist lives in `mygigguide_app/markDown1785133658918.md`. This file is the **merged, up-to-date** list with status and **what to build next**.

---

## ▶ Pick up here (27 Jul 2026 — evening)

| Priority | Task | Repo | Status |
|----------|------|------|--------|
| **1** | **Play Store — My Gig Guide** — upload AAB, finish Console declarations, closed testing | App + Console | **Next** — venue check-in V1 was pre-store gate; **done** |
| **2** | **Sheron pilot** — one real gig: repertoire + go live + fan requests + check-in | Both | **When ready** — stack coded & deployed; needs field test |
| **3** | **Page alerts (“My pages”)** — notify claimed page when someone tags them on a user-posted event | Laravel + App | **After Play or parallel** — [NOTIFICATIONS_PLAN.md](./NOTIFICATIONS_PLAN.md) |
| **4** | **Admin login stats** — dashboard cards: active 7d / 30d / never (`last_login_at` on Users list) | Laravel admin | **Quick win** — no app |
| **5** | **Repertoire polish** — Spotify-only add UI (drop YouTube field); edit/reorder songs | App (+ API) | **Optional** — deferred unless Sheron asks |
| **6** | **Live M4** — SnapScan tip link | Both | **Later** — after pilot |
| **7** | **VPS migrations table** — mark `venue_owners` etc. as migrated (use `--path=` only until fixed) | Ops | **Parked** — check-in deployed via targeted migrate |

**Latest APK labels:** `venue-checkin-v3` (check-in date UX) · prior `venue-checkin-v2` (check-in outside artists block) · `live-request-fix`

**Poster display (Jul 2026):** App ignores `poster_card_url`; full `poster_url` letterbox. Play AAB: `play-poster-fix`.

---

## Recently completed (Jul 2026 — live gig stack)

| Area | What | Tested |
|------|------|--------|
| **Repertoire M1** | `artist_songs`, bulk paste, public list (web + API + app) | ✅ Danger Dave |
| **Spotify v2** | Search, paste track/playlist, import by id | ✅ Dev keys on VPS |
| **Live M3** | Go live, queue, end live (artist); fan song requests | ✅ App |
| **Venue check-in V1** | Honor-system check-in, tonight board, venue tonight | ✅ App + VPS (`event_check_ins`) |
| **Check-in UX** | Future/past gigs show hint, no Bad State; all events (not only with artists) | ✅ v3 |
| **Posters in app** | Full poster in home / diary / list | ✅ |
| **Play manifest (MGG)** | No FGS media playback, no AD_ID / Facebook ad permissions in AAB | ✅ |
| **Home gallery** | Chronological sort; week window 500 cap (web) | ✅ |
| **Favourites alerts** | In-app badge + “New for your saves” | ✅ |

---

## Feature checklist (from Dave’s list — updated)

Legend: ✅ done · 🔄 in progress · ⬜ not started · ⏸ parked

### Discovery & home

| | Feature |
|---|---------|
| ✅ | Date range main screen & sort order |
| ✅ | Near me refresh / carousel filter (radius only, date sort) |
| ✅ | Load 2 more weeks / events 2 weeks more |
| ✅ | Preferred location match |
| ✅ | Default map / near me behaviour (mostly — see ⬜ default home) |
| ⬜ | Default home (tab / landing preference) |
| ⬜ | Date restriction on events calendar |
| ⬜ | Colour code map pins |
| ⬜ | Neighbouring countries |

### Posters & images

| | Feature |
|---|---------|
| ✅ | Poster trim / display (full poster like old APK) |
| ✅ | 2 photo issue in cards (ignore `poster_card` in app) |
| ✅ | Hero image popout / poster popup |
| ✅ | No poster image placeholder |
| ✅ | Image downsizer and hint |
| ✅ | Quicket layout hero / poster letterbox (Quicket imports) |
| ✅ | Gallery upload fix (no re-compress on submit) |

### Quicket & imports

| | Feature |
|---|---------|
| ✅ | Quicket tag events page |
| ✅ | Quicket issues (cron, categories, clear-cards) |
| ✅ | Rise fm (flavor) |
| ⬜ | Artist pick up from Quicket |
| ⬜ | Venue overwrite with blank if not matched |
| ⬜ | Venue match not good yet |
| ⬜ | Duplicate venues |
| ⬜ | Fuzzy match & duplicate (venues/artists) |

### Add event / forms

| | Feature |
|---|---------|
| ✅ | Add event bleed / title before date time |
| ✅ | Add venue match (Google Places fuzzy) |
| ✅ | Add picture with artist optional |
| ✅ | Add artist, venue |
| ✅ | Add video link in artist, venue add/edit |
| ✅ | Duplicate check in form (partial — ⬜ override duplicate UX) |
| ⬜ | Zero default blank |
| ⬜ | First name blocks new artists |
| ⬜ | 2× Bailey’s (duplicate venue case) |

### Artists, venues, claims

| | Feature |
|---|---------|
| ✅ | Venue length in app |
| ✅ | Venue fuzzy match — add new from Google |
| ✅ | YT in events from artists and venues |
| ✅ | Origin of YT video — artist / venue / event & heading |
| ✅ | Past 10 gigs (posted events) |
| ⬜ | Claims sign-up polish |
| ⬜ | Artist/venue **owner** notification → **page alerts (next)** |
| ⬜ | Upvote / Interested |

### Notifications & alerts

| | Feature |
|---|---------|
| ✅ | Timer notification |
| ✅ | Notifications in settings |
| ✅ | Alerts & notifications from favourites badge |
| ✅ | Notifications for upcoming favourites |
| ⬜ | **Page alerts** — “someone posted a gig tagging your claimed page” |
| ⬜ | Push (OneSignal) — after page alerts + follow growth |

### Categories & content

| | Feature |
|---|---------|
| ✅ | Category rework (A–Z) |
| ⬜ | Multi level categories |
| ⬜ | 25 July wall… (clarify / TBD) |

### Admin & ops

| | Feature |
|---|---------|
| ✅ | GitHub backup |
| ✅ | Backup of VPS/site |
| 🔄 | Users last login — **per-user in Admin → Users**; ⬜ dashboard summary cards |
| ⬜ | Advert insert (demo ads / Picolinos assets exist as mock) |

### App UX & theme

| | Feature |
|---|---------|
| ✅ | Back button |
| ✅ | Share app in events WhatsApp |
| ✅ | App link vs website link |
| ✅ | Settings and account split |
| ✅ | Light theme (dark MGG default shipped; ⬜ light theme option if still wanted) |
| ⬜ | Light theme (user-selectable?) |

### Live gig interaction (new track — not in original list)

| | Feature |
|---|---------|
| ✅ | **M1** Artist repertoire (`artist_songs`, bulk paste) |
| ✅ | **M2** Fan requests (logged in, app-only) |
| ✅ | **M3** Go live at listed event + queue |
| ✅ | **V1** Venue check-in + tonight’s board (honor system) |
| ✅ | Spotify search + playlist import for repertoire (v2) |
| ⬜ | **M4** SnapScan tip link |
| ⬜ | Repertoire edit / reorder in app |
| ⬜ | Geo check-in (V2) |
| ⬜ | Web live + check-in |
| ⬜ | Current live vibe |
| ⏸ | SeatMe / external integrations |

### Play Store & brands

| | Feature |
|---|---------|
| 🔄 | **My Gig Guide** — first production AAB (`play-poster-fix`) |
| ⬜ | 919 FM / Rogues AAB when vanilla confirmed |
| ⬜ | iOS (later) |

---

## Build labels (recent)

| Label | Contains |
|-------|----------|
| `venue-checkin-v3` | Check-in date hints; no Bad State on future gigs |
| `venue-checkin-v2` | Check-in on all events (not only with artists) |
| `venue-checkin-v1` | First check-in + tonight board |
| `live-request-fix` | Fan request screen layout fix |
| `play-poster-fix` | Play AAB: clean manifest + full-poster display |
| `poster-full-like-old` | APK: ignore `poster_card`, letterbox full poster |

---

## Suggested order (next few sprints)

```
1. Play Store MGG — closed testing → production (venue check-in gate cleared)
2. Sheron pilot — one gig end-to-end (repertoire + live + check-in)
3. Page alerts (Laravel + app) — owner notification
4. Admin dashboard active-user cards (optional same week)
5. Repertoire polish (Spotify-only UI) + edit/reorder if pilot needs it
6. SnapScan tips (M4) after pilot feedback
7. Fan push (OneSignal) when justified
```

---

## Decisions log (artist/venue live — 26 Jul 2026)

| Question | Decision |
|----------|----------|
| Fans must log in? | **Yes** (v1) |
| Live only on listed MGG events? | **Yes** |
| Multiple artists on one event? | **Yes** |
| Who can go live? | **Claimed artist or organiser** |
| Sheron pilot? | **Yes** — repertoire + live + check-in before tips |
| Check-in V1? | **Honor system** — logged in, event day only, app-only |
| Before Play Store? | **Venue check-in V1** — **done Jul 2026** |
| QR web fallback? | **No** — app only |

---

*Replace or archive `mygigguide_app/markDown1785133658918.md` when this file looks right — keep one checklist SSOT here.*
