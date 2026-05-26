# RSS event discovery (n8n → My Gig Guide)

Plan to **replace the venue/artist spider** with monitored feeds and automation.  
**Status:** research + n8n prototype (May 2026).

Cross-ref: [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md), [DEVELOPER_PHASE_ALIGNMENT.md](./DEVELOPER_PHASE_ALIGNMENT.md) item “RSS / RSSHub”, [SESSION_HANDOFF.md](./SESSION_HANDOFF.md).

**Facebook groups:** Meta does not publish group RSS. **FetchRSS.com** (free: 5 feeds, JSON/RSS/Atom, ~24h refresh, 5 posts/feed) is a reasonable **pilot** for a few public pages/groups—not all ~30 groups. **Inoreader** only reads feed URLs you give it. **RSSHub** Facebook routes are unreliable for production.

---

## Three different things (easy to mix up)

| Thing | What it is | Free tier? | Use for MGG |
|-------|------------|------------|-------------|
| **RSS reader app** | Tool for *you* to browse many feeds (Inoreader, Feedly, FreshRSS) | Yes (generous) | **Explore & validate** sources before wiring n8n |
| **Event RSS source** | A URL that *publishes* gigs (e.g. `musicist.co.za/events/feed/`) | Usually free to read | **n8n RSS Read** → normalize → create/review events |
| **RSSHub** | Self-hosted **generator** for sites with no native RSS | Open source; host yourself | Turn Eventbrite/Facebook/etc. into feeds (quality varies) |

Monday’s “RSS looked promising” was likely **Inoreader / Feedly-style readers** (150 / 100 feeds free) to **subscribe and test** sources—not a separate “RSS app” in this repo.

---

## RSS reader apps (monitoring only — generous free tiers)

Use these to paste feed URLs and see if items are useful **before** automating.

| Service | Free tier (approx.) | Notes |
|---------|---------------------|--------|
| **[Inoreader](https://www.inoreader.com/)** | ~150 RSS feeds, rules/filters | Best free headroom; good for power users |
| **[Feedly](https://feedly.com/)** | ~100 feeds, 3 boards | Polished UI; tighter free cap |
| **[FreshRSS](https://freshrss.org/)** | Unlimited (self-host) | Same VPS as n8n; no per-feed vendor limit |
| **[Folo](https://folo.is/)** | Reader + discovery | Often bundled with RSSHub ecosystem |

**Recommendation:** Start with **Inoreader** (fastest) or **FreshRSS** on the VPS if you want everything in-house.

---

## Event sources worth trying (South Africa focus)

### Tier A — Native RSS (test first)

| Source | Feed URL (try) | Verified | Notes |
|--------|----------------|----------|--------|
| **Musicist** | `https://musicist.co.za/events/feed/` | ✅ May 2026 | WordPress **The Events Calendar** (`tribe_events`); title, link, date in `pubDate` |
| **Any WP + Events Calendar** | `https://SITE/events/feed/` | Try per site | Same pattern as Musicist |
| **WordPress site** | `https://SITE/feed/` | Per site | Blog posts only unless events are posts |
| **Jazz Near You** | City pages + [export docs](https://www.jazznearyou.com/world/data-export-feed-xml) | Partner/export | Jazz-focused; may need signup; imports Bandsintown etc. |

**How to find more:** Google `site:.co.za events feed` or view source on listing pages for `<link rel="alternate" type="application/rss+xml"`.

### Tier B — RSSHub (self-host; do not rely on `rsshub.app` in production)

Public `rsshub.app` is for **testing only** (rate limits, shutdown messaging). Run RSSHub on your VPS next to n8n.

| Route (examples) | URL pattern | Caveat |
|------------------|-------------|--------|
| Eventbrite | `/eventbrite/...` | Scraping; regions vary; can break |
| Facebook | `/facebook/...` | Needs tokens/config |
| Mixcloud / YouTube | Various | Shows/radio, not always “gigs” |

Deploy: [RSSHub docker](https://docs.rsshub.app/deploy/) → `http://localhost:1200/eventbrite/...`

### Tier C — APIs (not RSS; optional later)

| Service | Cost | Fit |
|---------|------|-----|
| Eventbrite API | Free tier with limits | Structured events; SA coverage patchy |
| Bandsintown API | Artist-centric | Better for “artist announced dates” than venue listings |
| Quicket / Webtickets | No public RSS found | Scrape or manual; check ToS |

---

## Target architecture

```
[RSS feeds]     →  n8n (Schedule / RSS Read)
[RSSHub]            →  Code: map item → { name, date, venue?, url, source }
                    →  Dedupe (link or hash)
                    →  Staging (Sheet / DB / email digest)
                    →  Later: POST /api/v1/admin/events or organiser flow
```

**Do not** auto-publish to production without a **human or rules** step until quality is proven.

---

## n8n starter workflow (manual build)

1. **Schedule Trigger** — e.g. every 6 hours  
2. **RSS Read** — one node per feed (start with Musicist only)  
3. **Code** — normalize fields:

   - `title` → event name  
   - `link` → `source_url` / dedupe key  
   - `pubDate` or parsed description → `date` / `time`  
   - `contentSnippet` → description (strip HTML)

4. **Remove Duplicates** — on `link`  
5. **Output (pick one):**  
   - **Gmail** / Slack — daily digest for review  
   - **Google Sheets** — staging list  
   - **HTTP Request** — future Laravel ingest endpoint  

Test command on VPS or PC:

```bash
curl -sL "https://musicist.co.za/events/feed/" | head -80
```

---

## Laravel (later, same repo)

| Piece | Purpose |
|-------|---------|
| `GET /feed/events.xml` | **Your** discovery feed for partners (optional) |
| Ingest command or API | Pull configured feeds server-side (alternative to n8n) |
| `event_sources` table | `feed_url`, `last_fetched_at`, `enabled` |

Not started in code yet — n8n prototype first.

---

## Suggested order of work

1. Subscribe to **Musicist** in Inoreader (or FreshRSS).  
2. Add **5–10** SA venue/promoter sites; note which expose `/events/feed/`.  
3. n8n workflow: **one feed** → email digest.  
4. Self-host **RSSHub** on VPS; trial one route (e.g. Eventbrite region if relevant).  
5. Define **dedupe + field mapping** doc for ingest into MGG.  
6. Laravel POST or admin “import from staging” when ready.

---

## Feed registry (maintain this table)

| Label | Feed URL | Active | Notes |
|-------|----------|--------|--------|
| Musicist | `https://musicist.co.za/events/feed/` | yes | Primary SA test feed |
| *(add rows)* | | | |

---

## VPS note

n8n is on the same host as the site (`N8N_HOST` in Evolution/n8n env). RSSHub should run **locally** in Docker (`rsshub:1200`) so n8n calls `http://rsshub:1200/...` on the Docker network—not the public `rsshub.app` instance.
