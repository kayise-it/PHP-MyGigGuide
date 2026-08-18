# In-app listener polls — Mix 93.8 & VOW FM

Station flavors open an **in-app poll screen** (no external survey URL needed) when `BrandConfig.stationPollContext` is set. Polls are served from Laravel:

- `GET /api/v1/polls/mix938`
- `GET /api/v1/polls/vowfm`
- `POST /api/v1/polls/{id}/vote` with `{ device_id, option_index }`

---

## 1. Copy files into `mygigguide_app`

From this repo (`docs/flutter/`) → your `lib/` tree:

```bash
cd ~/development/mygigguide_app

cp docs/flutter/models/station_poll.dart lib/models/
cp docs/flutter/services/poll_api_service.dart lib/services/
cp docs/flutter/widgets/station_poll_section.dart lib/widgets/
cp docs/flutter/screens/station_in_app_poll_screen.dart lib/screens/

# Updated radio tab (Mix + VOW poll tile opens in-app screen)
cp docs/flutter/station_radio_tab_screen.dart lib/screens/station_radio_tab_screen.dart
```

Branch: `cursor/poll-vow-mix-858a` on the Laravel repo (or pull from `CambodiaDave/mygigguide_app` once pushed).

---

## 2. Add to `lib/brand_config.dart`

Merge from `docs/flutter/brand_config_poll_snippet.dart`:

```dart
static String get stationPollContext {
  if (isMix938) return 'mix938';
  if (isVowFm) return 'vowfm';
  return '';
}

static String get siteUrl {
  // Your existing SITE_URL getter / dart-define
}
```

Flavors without `stationPollContext` keep the old behaviour (survey URL, Rogues/Rise demo screens).

---

## 3. Create polls on the VPS (one-time)

```bash
cd ~/development/PHP-MyGigGuide   # or your app path on mel55-nix02

php artisan migrate   # if polls tables not yet applied

php artisan poll:create mix938 "What's your favourite time to listen?" "Morning" "Drive time" "Evening"
php artisan poll:create vowfm "Which show do you never miss?" "Breakfast" "Drive" "Late night"
```

Or use **Admin → Polls** on www.mygigguide.co.za.

Verify:

```bash
curl -s "https://www.mygigguide.co.za/api/v1/polls/mix938" | jq .
curl -s "https://www.mygigguide.co.za/api/v1/polls/vowfm" | jq .
```

---

## 4. Build & test

```bash
./scripts/build_apk.sh mix938 --label=poll-v1
./scripts/build_apk.sh vowfm --label=poll-v1
```

**Checklist (each flavor):**

- [ ] Radio tab → **Listener poll** → subtitle says **Vote in the app**
- [ ] Poll question loads from API
- [ ] Tap an option → vote recorded, results show
- [ ] Second vote on same device → already voted (409 handled)

**Main app On Air** (optional — see `docs/flutter/main_app/INSTALL_MAIN_APP_MIX.md`):

- Mix and VOW cards on Home strip with embedded poll section

---

## Architecture

```
StationRadioTabScreen
  └── Listener poll tile
        └── StationInAppPollScreen
              └── StationPollSection
                    └── PollApiService → /api/v1/polls/{context}
```

Poll context values match Laravel admin: `mix938`, `vowfm`.
