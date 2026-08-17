# FIXR integration notes

**Status:** scaffold only (Jul 2026). Not the same product shape as Quicket.

## What FIXR’s API actually is

From [docs.fixr.co/api](https://docs.fixr.co/api/):

> API for event organisers to read a live feed of events listed on **their** FIXR organiser accounts.

So it is **not** a public “all local gigs in Gauteng” firehose. To use it for My Gig Guide you need either:

1. **Your own FIXR organiser account** + API token from FIXR, and/or  
2. **Partnership** with SA promoters who already sell on FIXR (they share feed / webhooks), or  
3. **Webhooks** from [organiser.fixr.co](https://organiser.fixr.co) → our HTTPS endpoint (`Event published` / `Event created`).

## `.env` (once FIXR gives you credentials)

```env
FIXR_API_TOKEN=
FIXR_EVENTS_URL=
# Optional: verify inbound webhooks
FIXR_WEBHOOK_BEARER=
FIXR_OWNER_USER_ID=5
```

`FIXR_EVENTS_URL` is left blank until FIXR documents/gives the exact feed URL for your token.

## Commands

```bash
php artisan fixr:pull
```

Dry-run preview only — no DB writes until we confirm the JSON shape with a real token.

## Practical SA path

| Path | Effort | Fit |
|------|--------|-----|
| Quicket public events API | Done | Broad SA ticketed music |
| FIXR organiser feed | Need token + URL | One promoter’s catalogue |
| FIXR webhooks | Need endpoint + bearer | Live updates for partners |
| Unofficial scrape | Avoid | ToS / brittle |

## Outreach (when ready)

Contact FIXR support / account manager: ask for **API token** + **events list endpoint** for discovery/partner use (not only self-serve organiser widget). Mention My Gig Guide as a discovery calendar that **links out** to FIXR checkout.
