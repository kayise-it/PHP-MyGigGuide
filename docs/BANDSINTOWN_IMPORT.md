# Bandsintown import

Artist-centric tour dates → My Gig Guide events (`ticket_url` link-out).

## Access (important)

| Access | What you get |
|--------|----------------|
| **Bandsintown for Artists** key | Events for **one** artist only |
| **Partner `app_id`** | Multi-artist / platform use — email **API@bandsintown.com** |

Without a valid `app_id`, the API returns an authorization deny (seen Jul 2026).

## `.env`

```env
BANDSINTOWN_APP_ID=your-app-id
BANDSINTOWN_COUNTRIES=ZA
# Optional city filter (comma-separated):
# BANDSINTOWN_CITIES=Johannesburg,Pretoria,Centurion,Sandton
# Pipe-separated watchlist when no CLI artist:
# BANDSINTOWN_ARTISTS=Black Coffee|Tyla|Jeremy Loops
# Owner for --apply (falls back to QUICKET_OWNER_USER_ID):
BANDSINTOWN_OWNER_USER_ID=5
```

## Commands

```bash
# Dry-run one artist (ZA only by default)
php artisan bandsintown:pull "Artist Name"

# Wider geography
php artisan bandsintown:pull "Artist Name" --all-countries

# Write (needs owner user id + working app_id)
php artisan bandsintown:pull "Artist Name" --apply
```

## Mapping

| Bandsintown | Ours |
|-------------|------|
| `title` / lineup[0] | `events.name` |
| `datetime` | `date` + `time` |
| `offers[].url` or event `url` | `ticket_url` (dedupe) |
| `venue.*` | match/create unclaimed venue |

## Next

- Partner email for multi-artist `app_id`
- Attach artist rows when name matches MGG artists
- Nightly pull over `BANDSINTOWN_ARTISTS` watchlist
