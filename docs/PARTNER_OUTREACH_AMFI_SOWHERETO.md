# Partner outreach — AMFI & SoWhereTo

Draft emails and context for collaboration conversations.  
**My Gig Guide:** open gig guide (web + app + API), crowd posting, claimed artist/venue pages, map — **not** a ticketing platform (we link out via `ticket_url` where we have it).

**Last updated:** 19 May 2026

---

## How we’re thinking about it (short)

| Partner | Fit | Suggested first step |
|---------|-----|----------------------|
| **AMFI** | Afrikaans / festival / up-and-coming artists — niche we don’t own | Link-out, then explore **one-way ingest** of their public events |
| **SoWhereTo** | Cape Town + **ticketed** events — fills a coverage gap | Link-out to tickets, then explore **read-only feed** into our calendar |

**Avoid early:** merged databases, shared logins, manual double-entry without dedupe rules.

**Questions to ask on any call:** exports/API? update frequency? duplicate policy? ticket flow? geography? business model?

---

## Email — AMFI (Pieter Botes)

**To:** use contact on [amfi.click](https://amfi.click/) or Play Store developer listing  
**Subject:** My Gig Guide × AMFI — complementary gig listings?

---

Hi Pieter,

I’m Dave — I run **My Gig Guide** ([mygigguide.co.za](https://www.mygigguide.co.za)): a national gig guide where anyone can post events, and artists/venues can **claim** their pages on the website and app.

I’ve been looking at **AMFI** and like how clearly it serves Afrikaans festivals and up-and-coming artists — that’s a lane we don’t cover properly yet. We’re stronger on open listings, map/venue discovery, and a shared web + mobile API, but we’re not trying to be an exclusive ticket shop.

I wonder if there’s room to **collaborate rather than compete** — even something small to start:

- **Cross-link** on event pages (“Also on AMFI” / “Also on My Gig Guide”)
- Or, if you’re open to it, a **simple feed or export** of public AMFI events into our calendar (with clear credit and links back to you), so your artists get wider reach without double admin on your side

No pressure for anything heavy — I’d value a 20-minute chat to see if our audiences overlap and what would be fair for both sides (especially where AMFI is the ticket channel).

Would you be open to a call or a reply with how you’d prefer to work with other listing platforms?

Thanks,  
Dave  
My Gig Guide  
https://www.mygigguide.co.za

---

## Email — SoWhereTo

**To:** use contact on [sowhereto.io](https://sowhereto.io/) or app support email  
**Subject:** My Gig Guide × SoWhereTo — discovery + tickets?

---

Hi,

I’m Dave from **My Gig Guide** ([mygigguide.co.za](https://www.mygigguide.co.za)) — we run an open gig guide (website + app) for posting and finding live music and events across South Africa, with claimed artist/venue pages and a map layer.

**SoWhereTo** looks strong for **ticketed** nights and discovery in Cape Town (and beyond) — map, filters, favourites — which is an area we’d rather **complement** than rebuild (we link out for tickets; we don’t run check-in rewards or our own ticket shop).

I’m interested in a **light partnership** to start, for example:

- **Link-out:** our listings point to SoWhereTo (or the ticket URL) when that’s where booking happens
- **Coverage:** if you have a **feed, export, or partner API**, we could ingest public CT/ticketed events into our calendar with attribution and dedupe — you stay canonical for tickets; we improve “what’s on” for users who live in our app

Happy to keep it simple — a short call to see if there’s mutual benefit and what your terms are for data sharing.

Would someone on your team be open to a conversation?

Thanks,  
Dave  
My Gig Guide  
https://www.mygigguide.co.za

---

## After they reply — questions for the call

1. Do you offer **RSS, CSV, JSON, or an API** for public events? How often is it updated?
2. If the same gig exists on both platforms, **who is the source of truth** for edits and cancellation?
3. For **tickets** — always link out to your app/site, or any embed/partner checkout?
4. **Geography** — national, city-specific, or genre-specific (e.g. Afrikaans-only)?
5. **Business model** — free listings, subscription, ticket commission — anything we need to disclose to users?
6. **Branding** — “Powered by / Listed on / Tickets via” — what do you require?

---

## If a pilot goes ahead (technical note for us)

- Store `source` (`amfi` | `sowhereto`) + `external_id` on imported rows  
- Dedupe like existing rules: same `ticket_url`, or venue + date + similar title + time  
- Never overwrite **claimed** MGG-owned listings without admin review  
- See also [RSS_EVENT_DISCOVERY.md](./RSS_EVENT_DISCOVERY.md)

---

## Related docs

- [PRODUCT_CONTINUITY.md](./PRODUCT_CONTINUITY.md) — what MGG is today  
- [INTERNATIONAL_EXPANSION.md](./INTERNATIONAL_EXPANSION.md) — partner / white-label patterns  
- [API_V1.md](./API_V1.md) — public read API if a partner wants to consume our events
