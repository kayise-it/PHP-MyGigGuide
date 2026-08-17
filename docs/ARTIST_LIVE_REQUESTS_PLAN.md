# Artist & venue live interaction — product discussion

**Draft for review · 23 Jul 2026**

Read on phone / Google Drive. Not a build spec yet — ideas to think through.

**Context:** Sheron (artist) uses [sheron-requests.glideos.app](https://sheron-requests.glideos.app) — browse his song list, request a track, optional SnapScan tip. Dave asked whether and how My Gig Guide should do something similar.

**Direction (Jul 2026):** App-first. Artists share playlists; fans place requests in the app; **live at the gig** is the main aim. Tips/payments are a **kicker** to get artists onto the app — not the foundation of v1.

---

## What Sheron’s app is (Glideos / Glide)

Built with **Glide** (no-code). Not custom Laravel/Flutter.

| Feature | What it does |
|--------|----------------|
| Song catalog | Fans browse a list the artist maintains |
| Pick a song | Tap to request |
| SnapScan tip | Optional tip when requesting (SA-friendly) |
| “Request anyway” | Free-text if the song isn’t in the list |
| Live updates | WebSocket — artist sees requests in real time on their phone |

**Fan flow:** QR at the venue → phone browser → no app install.

**Good for:** one artist, one standalone page, quick to ship on Glide.

**Not:** tied to gigs, discovery, or a wider platform.

---

## What My Gig Guide has today

| Area | Today |
|------|--------|
| **Core** | Gig discovery — events, artists, venues, ratings, claims |
| **Artist page** | Bio, videos, upcoming/recent gigs, rating |
| **919 FM** | `/request` — form → opens WhatsApp (no catalog, no tips, no queue) |
| **Booking** | Commented out on web artist page — “fan → artist action” is a gap |
| **Stack** | Laravel SSOT + Flutter app + API v1 |

Sheron’s use case fills a gap MGG doesn’t cover yet: **interaction during a performance**.

---

## Should MGG build this?

**Yes — but as a live gig layer on discovery, not a Glide clone.**

| Do | Don’t (initially) |
|----|-------------------|
| Playlist + in-app requests + live session at gigs | Full Glide clone with WebSockets on day one |
| Tie requests to **events** and **claimed artists** | In-app card payments / Stripe Connect |
| Tips via SnapScan **link** later (artist’s own merchant account) | Compete feature-for-feature with TipTune / NoSongRequests |
| QR deep link into app (web fallback optional later) | Require perfect venue stranger conversion on v1 |

**MGG’s angle vs standalone request apps:**

- Request tied to **tonight’s gig** on MGG (“Sheron @ The Local, Friday”)
- Fan already uses MGG for gigs / saved artists
- After the gig, fan still sees **next shows**
- One **claimed artist page** — gigs + playlist + live mode

---

## Product vision (Dave’s direction)

Not a jukebox payment product. A **live layer on top of gigs**:

```
Discovery (have)  →  Tonight’s gig  →  Artist goes LIVE  →  Fans interact in-app
```

### Three pillars

| Pillar | Purpose |
|--------|---------|
| **Playlist / repertoire** | Artist shows what they *can* play |
| **Requests** | Structured fan → artist messages tied to a gig |
| **Live session** | “This is happening *now*” — queue, status, maybe “now playing” |

**Tips:** sit on top — “Request + optional tip” is the **artist acquisition hook**, not the core loop.

---

## User journeys (sketch)

### Artist (claimed page, in app)

1. **Build repertoire** — songs (title; optional: original cover, genre)
2. **Before / at gig** — open tonight’s event → **Go live**
3. **During set** — request queue: accept / skip / played
4. Optional: **Now playing** line fans see
5. **After gig** — session ends; stats later (“12 requests, 8 played”)

### Fan (in app)

1. Finds gig (Home / Events / saved artist)
2. Event shows **LIVE** badge when session active
3. Browse artist playlist → **Request** (or free-text if not listed)
4. Optional tip (later) — SnapScan opens with amount + reference
5. Status: queued → accepted → played (or declined)

### Venue (optional, later)

- Resident artist live session on venue page during gig — not required for v1

---

## App-only vs venue reality

**Goal:** all using the **app** (logged-in users, push later, rich queue).

**Reality at the bar:** many people won’t have MGG installed yet.

| Approach | Notes |
|----------|--------|
| **Primary** | In-app — artist pushes regulars / followers |
| **QR at gig** | Deep link → app if installed; else store / thin “open in app” page |
| **v1 audience** | Artist + their crowd + gig regulars — enough to validate |

Don’t need to solve every stranger at the venue on day one.

---

## “Live” — technical meaning

**Live session** = time-boxed record, e.g.:

- `artist_id` + `event_id` (ad-hoc “no event listed” optional later)
- `started_at` / `ended_at`
- `status`: scheduled \| live \| ended
- **One live session per artist** at a time

Fans request when `status = live` (or artist toggle “accepting requests”). Avoids spam on the playlist year-round.

**Real-time:**

- **v1:** polling every few seconds on queue (simple)
- **v2:** Laravel Reverb / websockets if queue feels laggy

3–5 second refresh is fine for acoustic sets.

**Who can request?**

- **v1:** logged-in app user (abuse control, notifications)
- Later: guest + phone verify optional

---

## Data model — keep these separate

| Entity | Role |
|--------|------|
| **Repertoire / playlist** | Long-lived — “songs I know” |
| **Live session** | Short-lived — “accepting requests now” |
| **Request** | One fan ask during a session — song pick or free text |

- Playlist visible on artist page when **not** live (marketing + setlist).
- When **live**, same list becomes the request menu.
- Don’t merge playlist and queue into one table — want history/analytics later.

**Rough tables (future spec):**

- `artist_songs` — see **Repertoire storage & imports** below
- `live_sessions` — artist_id, event_id, status, started_at, ended_at
- `song_requests` — live_session_id, user_id, artist_song_id nullable, message, status, tip_reference nullable

Artist `settings` JSON is too small for songs — use proper tables.

---

## Repertoire storage & imports

**Principle:** Store the repertoire **in MGG (Laravel)** as a song list the artist controls. Use **YouTube or Spotify only to help add songs faster** — not as the live playlist host.

### Where songs are held

| Layer | What |
|-------|------|
| **Database (SSOT)** | `artist_songs` — one row per song per artist |
| **API** | e.g. `GET /api/v1/artists/{id}/repertoire` (fans); `POST/PATCH/DELETE …/me/artist/songs` (owner) |
| **App** | Artist edits in app; fans browse/search when requesting |

**Not** `artist.settings` JSON. **Not** the same as **`youtube_videos`** on the profile (promo clips vs request menu).

### `artist_songs` row shape (draft)

| Field | Purpose |
|-------|---------|
| `artist_id` | Owner |
| `title` | What fans see and request — e.g. “Wonderwall” |
| `original_artist` | Optional — e.g. “Oasis” |
| `is_original` | Artist’s own composition |
| `sort_order` | Manual order / default A–Z display |
| `reference_youtube_id` | Optional — paste-helper / “this version” |
| `reference_spotify_id` | Optional — paste-helper |
| `notes` | Optional — artist-only (“capo 2”) |

Fans request by **title** (+ optional message). MGG does **not** stream audio for requests.

### Why not “just link their Spotify playlist”?

| Link-only | Problem |
|-----------|---------|
| Embed Spotify playlist | Needs Spotify app; list changes silently; no queue or live control |
| YouTube playlist | Same — wrong tool for gig requests |
| External catalog only | No live session, search, or request status in MGG |

**Pattern:** Spotify/YouTube = **import helpers**. MGG DB = **canonical list** for requests.

### How artists build the list

#### v1 — ship first

1. **Type title** (+ optional original artist)
2. **Paste YouTube link** → MGG pulls title via oEmbed (reuse `YoutubeVideoService` pattern — no API key)
3. **Bulk paste** — one song per line, or `Artist - Title`
4. **Reorder / delete** in app

YouTube titles are often messy (`Oasis - Wonderwall (Official Video)`). Artist edits after paste.

#### v2 — nicer add flow

| Source | How |
|--------|-----|
| Paste YouTube URL | Prefill title (feasible on day one) |
| Paste Spotify track URL | Laravel + Spotify Web API (client credentials) → title, artist, `spotify_id` |
| Search | “Add song” → pick from YouTube or Spotify metadata → **Add** |
| Import Spotify playlist | Paste public `open.spotify.com/playlist/…` → import rows into `artist_songs` |

Spotify needs developer app + API key in `.env`. **Paste link / search** does not require fan or artist Spotify login if using server-side client credentials.

#### v3 — optional

- Duplicate last gig’s setlist
- CSV upload
- “Suggest from my YouTube videos” — copy titles from profile videos into repertoire (shortcut only)

### YouTube vs Spotify for choosing

| | **YouTube paste** | **Spotify paste/search** |
|--|-------------------|---------------------------|
| Build effort | Low — oEmbed already used for profile videos | Medium — Spotify API + keys |
| Best for | Covers, live clips, “this version” | Clean song + artist names |
| SA reality | Universal | Very common for artists/fans |
| **v1?** | ✅ Yes | Defer to v2 |

**Recommendation:** v1 = manual + YouTube paste. v2 = Spotify track URL + search. Avoid full-catalog picker on day one.

### What fans see

| Mode | UI |
|------|-----|
| **Not live** | “Repertoire” / “Songs I play” on artist page (browse; optional soft request later) |
| **Live at gig** | Searchable A–Z list + **Request** + “Can’t find it? Request anyway” |

Optional per row: external reference link (opens YouTube/Spotify) — not required to submit a request.

### Three buckets — do not merge

```
Profile YouTube videos     →  promotion / embeds (exists today)
Artist repertoire          →  request menu (new: artist_songs)
Live session + requests    →  tonight’s queue (new)
```

Shortcut allowed: “Add from my YouTube videos” copies a title into repertoire; does not replace the table.

### Build vs external platforms

Repertoire is **always built in-house** in Laravel — same as live requests overall. No Glideos/Spotify playlist as SSOT.

---

## Tips as artist hook (don’t block v1)

| Phase | What |
|-------|------|
| **A** | Requests + live queue — prove artists use it |
| **B** | Optional SnapScan on submit (“Add a tip?” → opens SnapScan with amount + `mgg-request-123`) |
| **C** | Only if needed — webhook / “tip received” (heavy) |

**Pitch to artists:** *“Share your setlist, take live requests at gigs — fans can tip in one tap.”*

They adopt for requests; tips sweeten it.

MGG does **not** process card money in v1. Artist uses their own SnapScan merchant account.

---

## Where it plugs into MGG today

| Existing piece | Extension |
|----------------|-----------|
| `artist_detail_screen.dart` | Playlist + “Live now” |
| Event detail | “Request songs” when artist live at this gig |
| Artist claims | Gate playlist + live mode to **claimed artists** |
| 919 `/request` | Same *idea* for **performing** artists — app-native, richer |
| API v1 + Laravel | New endpoints for repertoire, sessions, requests |

**New app surfaces (conceptual):**

- Artist: **My repertoire** + **Go live** (dashboard / profile)
- Fan: **LIVE** chip on event / artist
- Later: **Live** filter on Home (“Gigs with live requests now”)

---

## Phased roadmap

### Milestone 1 — Repertoire (no live yet)

- Artist CRUD playlist in app
- Public playlist on artist page (app + web)
- **Validates:** artists will maintain a list

### Milestone 2 — Requests (not necessarily live)

- Fan requests from artist page (rate limits if not live)
- Artist inbox in app
- **Validates:** fans will tap request

### Milestone 3 — Live at the gig ⭐ (main aim)

- Go live on an event
- Requests only while live
- Queue: accept / skip / played
- LIVE badge on event/artist
- **Validates:** gig-night interaction

### Milestone 4 — Tips

- SnapScan on request flow
- **Validates:** artist acquisition story

### Milestone 5 — Polish

- Push (“Your request was accepted”)
- Now playing
- QR poster for artist to print
- “Live gigs near me”

---

## Earlier “lite” options (still valid)

If full build waits, minimal steps:

**Phase 0 — link-out (almost no code)**

- Artist settings: external request URL (e.g. Sheron’s Glideos) and/or SnapScan + WhatsApp
- Button on artist + event pages
- Ask Sheron: link **from** MGG to Glideos, or wants everything under mygigguide.co.za?

**Phase 1 web-lite (919 pattern)**

- `mygigguide.co.za/artists/{slug}/requests`
- Catalog + SnapScan + WhatsApp — no app required for fans
- Good bridge; Dave’s priority is **app**, so this is fallback not main path

---

## Scope guardrails

**In scope for “live gig interaction”:**

- Playlist, requests, live session, queue, event linkage

**Out of scope initially:**

- Full payment processing in MGG
- DJ software integrations
- Karaoke / line dance modes
- Competing with Glide feature-for-feature

**Differentiator later:**

- Request tied to post-gig rating
- Fan history: “You requested X at Y venue”

---

## Decisions to make (before spec)

1. **Must fan be logged in?** — Recommend **yes** for v1.
2. **Live only on scheduled MGG events**, or ad-hoc (“pub residency, no event listed”)?
3. **One artist per event** for live, or multiple acts on a bill?
4. **Who can go live** — only claimed artist, or organiser on their behalf?
5. **Sheron as pilot** — Milestones 1–3 without tips first?
6. **QR fallback** — app-only strict, or thin web page for non-installers?

---

## Build our own vs Glideos / API?

**Build your own inside MGG.** No Glideos dependency; no API into Glide.

| Option | Verdict | Why |
|--------|---------|-----|
| **Use Glideos for everyone** | ❌ No | Fans leave MGG; no gig/event link; two systems per artist |
| **API into Glideos/Glide** | ❌ Not realistic | Each artist has their own Glide app; no shared platform API for MGG |
| **Own listing + requests in MGG** | ✅ Yes | Laravel SSOT, Flutter UI, events + claims, one live layer |

Glideos is fine as **Phase 0 link-out** for early adopters (e.g. Sheron) while MGG-native features ship.

**What “build our own” means:** repertoire + live session + request queue + (later) SnapScan link — not cloning Glide.

---

## Venue interaction hooks (parallel to artists)

**Artist hook:** performer ↔ fan at the gig.  
**Venue hook:** **room ↔ everyone in the room tonight.** Same live energy; venue is the **host of the night**.

### Venue angle in one line

Venue = container for multiple acts, one place, one crowd. MGG becomes **“what’s happening here right now”** — not just a static listing.

### Hooks that mirror artist live requests

#### 1. “I’m here tonight” (check-in) — strongest venue hook

Fan taps **Check in** on event or venue page (app; optional geo).

| Fan gets | Venue / platform gets |
|----------|------------------------|
| Tonight’s lineup, live badges | “47 people here” social proof |
| Request songs (if artist is live) | Real attendance signal |
| Post-gig prompt to rate venue + artist | “Busy night” data for map/home |

#### 2. Tonight’s board (venue live hub)

When an event is on, one screen for the venue:

- Who’s on **now** / **next**
- Which acts are **accepting requests**
- Links to each artist’s live session
- Optional venue note (“Acoustic out front · DJ in the beer garden”)

**Artist hook:** requests. **Venue hook:** **curator of the night.**

#### 3. Venue-run queue nights (open mic, karaoke, quiz)

Same tech as artist repertoire; different **owner**:

| Night type | Who runs the queue |
|------------|-------------------|
| Solo artist | Artist |
| Karaoke / open mic | **Venue** (or host account) |
| Quiz / comedy | Venue — signup or vote |

Fan: join queue / request slot / vote encore.  
Venue staff: accept, reorder, “you’re up next.”

Many venues have a **format**, not one star artist — `owner_type = venue` on live sessions.

#### 4. Live micro-feedback (claimed venues)

During or after check-in, one tap: sound · vibe · crowd · bar queue (optional).

Aggregate pulse for the venue — lighter than song requests; good for pubs without setlists.

#### 5. Venue shout-outs / dedications

Venue-branded (like 919 `/request`): birthday shout, message to DJ → staff dashboard or WhatsApp. Low build; good first venue interaction.

#### 6. Resident night + loyalty

Recurring nights (Karaoke Thursdays, jazz Sunday): save venue → notify when **that night** goes live; optional digital stamp / perk. Hooks **venue regulars**.

#### 7. Multi-room / multi-stage (later)

Front room / back room each with own live board. Only when venues ask for it.

### What to skip (for now)

| Idea | Why defer |
|------|-----------|
| Table ordering / tabs | POS product; heavy ops |
| Generic venue jukebox | Conflicts with artist requests; licensing |
| Fan photo wall | Moderation burden |
| Live chat | Noise and abuse |

### How artist + venue fit together

```
Venue page
  └── Tonight's event(s)
        └── Check-in ("I'm here")
        └── Tonight's board (who's live)
        └── Artist A → live requests
        └── Artist B → live requests
        └── OR venue queue (karaoke / open mic)
```

Shared backend: `live_sessions`, requests, queues — **`owner_type` artist vs venue**.

### Venue phasing

| Phase | Venue hook | Effort |
|-------|------------|--------|
| **V0** | “Tonight at {venue}” + links to live artist sessions | Low |
| **V1** | Check-in + tonight’s board | Medium |
| **V2** | Venue shout-outs OR karaoke signup queue | Medium |
| **V3** | Micro-feedback, resident-night notifications | Medium |
| **V4** | Multi-room, loyalty | Later |

**Natural pairing:** Artist Milestone 3 (live at gig) + Venue V1 (check-in + board) — fan opens event → checks in → requests from artist.

### Claim / adoption story

| Who | Why they’d use it |
|-----|-------------------|
| **Artist** | Requests + tips |
| **Venue** | See who’s here, promote tonight’s acts, run karaoke/open mic, look active vs competitors |

Pitch to venues: *“Your listing isn’t dead — on gig night it becomes the live hub for the room.”*

### Venue decisions (when spec’ing)

1. Check-in: honor system vs light geo fence?
2. Venue live mode: auto when any event is on, or staff **Go live** toggle?
3. Who moderates venue queue — claimed venue owner vs host user?
4. **“Live near me”** tab — venues + artists with active sessions?

---

## One-line summary

**Live gig mode in the MGG app:** artists share a repertoire, go live at a listed gig, fans request in-app; venues add **check-in + tonight’s board + house formats** (karaoke, shout-outs); tips via SnapScan come later as the artist hook — all anchored on **events + discovery**, built in-house (not Glideos).

---

## Related docs

- [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md) — three surfaces, SSOT
- [API_V1.md](./API_V1.md) — current API patterns
- [CLAIM_PAGES_GUIDE.md](./CLAIM_PAGES_GUIDE.md) — claimed artist pages
- 919 request page: `resources/views/fm919/request.blade.php` (WhatsApp pattern)

---

## Next steps when ready

- [ ] UX wireflow (artist go-live + fan request screens)
- [ ] UX wireflow (venue check-in + tonight’s board)
- [ ] Data model + API v1 sketch (`artist_songs`, `owner_type` artist vs venue)
- [ ] Pilot plan with Sheron (one Friday gig)
- [ ] Pilot venue (one pub with regular live music or karaoke)
- [ ] Decide app-only vs QR web fallback

*No code committed for this feature yet — discussion doc only.*
