# Main app — Mix 93.8 On Air integration

Brings **standalone Mix features** into the vanilla **My Gig Guide** app via the Home **On Air** strip:

- Live stream + play
- Zeno now playing
- WhatsApp studio (**066 417 8469**)
- Website + Facebook + Instagram
- Listener poll (`GET /api/v1/polls/mix938`)
- Call studio

Standalone **mix938** flavor keeps its full Radio tab; main app gets a **trimmed detail screen**.

---

## 1. Copy files into `mygigguide_app`

From this folder (`docs/flutter/main_app/`) → your `lib/` tree:

```bash
cd ~/development/mygigguide_app

# Models & data
cp docs/flutter/main_app/models/radio_station_entry.dart lib/models/
cp docs/flutter/main_app/models/station_poll.dart lib/models/
cp docs/flutter/main_app/data/radio_stations_catalog.dart lib/data/

# Services (zeno files should already exist from mix938 work)
cp docs/flutter/main_app/services/poll_api_service.dart lib/services/

# Widgets & screen
cp docs/flutter/main_app/widgets/station_live_player_card.dart lib/widgets/
cp docs/flutter/main_app/widgets/station_listener_actions.dart lib/widgets/
cp docs/flutter/main_app/widgets/station_poll_section.dart lib/widgets/
cp docs/flutter/main_app/widgets/home_on_air_strip.dart lib/widgets/
cp docs/flutter/main_app/screens/radio_station_detail_screen.dart lib/screens/
```

If you pull from GitHub instead of Cursor cloud, copy from branch `cursor/mix-main-app-integration-858a`.

---

## 2. Add to `lib/brand_config.dart`

Poll API needs your site URL. Add if missing:

```dart
/// From --dart-define=SITE_URL=… (same as existing app config).
static String get siteUrl => /* your existing SITE_URL getter */;
```

**Mix 93.8 standalone flavor** — ensure WhatsApp uses studio number (not 084):

```dart
static const _mix938StudioE164 = '27664178469'; // 066 417 8469

// stationWhatsAppUri for mix938 → Uri.parse('https://wa.me/$_mix938StudioE164')
// stationStudioPhoneE164 for mix938 → _mix938StudioE164
```

Remove any `27848220938` WhatsApp reference for Mix.

---

## 3. Wire Home screen

In your Home tab (e.g. `lib/screens/home_tab_screen.dart` or wherever the coverflow lives), **after** the header and **before** the gig coverflow:

```dart
import '../widgets/home_on_air_strip.dart';

// Inside build → ListView / Column children:
if (BrandConfig.isMyGigGuide) ...[
  const HomeOnAirStrip(),
  const SizedBox(height: 8),
],
```

Use your actual vanilla flavor check (`!BrandConfig.isRogues` etc.) if you don't have `isMyGigGuide`.

---

## 4. Build & verify

```bash
# Main app — tap Mix on Home On Air strip
./scripts/build_apk.sh mygigguide --label=on-air-mix-v1

# Standalone Mix — full Radio tab unchanged
./scripts/build_apk.sh mix938 --label=nowplaying-v4
```

**Checklist (main app):**

- [ ] Home shows **On Air** strip with **Mix 93.8** card
- [ ] Tap card → detail with play, now playing, WhatsApp **066 417 8469**
- [ ] Poll loads (create one: `php artisan poll:create mix938 "Question?" "A" "B"`)
- [ ] VOW FM card on On Air strip (if catalog copied)
- [ ] VOW poll loads (`php artisan poll:create vowfm "Question?" "A" "B"`)
- [ ] Facebook / Instagram open

**Checklist (mix938 flavor):**

- [ ] Radio tab still works
- [ ] WhatsApp shows **066 417 8469**

---

## 5. Laravel poll (one-time on VPS)

```bash
php artisan poll:create mix938 "What's your favourite time to listen?" "Morning" "Drive time" "Evening"
php artisan poll:create vowfm "Which show do you never miss?" "Breakfast" "Drive" "Late night"
```

API: `GET https://www.mygigguide.co.za/api/v1/polls/mix938` and `/polls/vowfm`

See also: `docs/flutter/INSTALL_POLLS.md` for flavor radio tab in-app polls.

---

## Architecture

```
HomeOnAirStrip  →  RadioStationDetailScreen
                         ├── StationLivePlayerCard
                         ├── ZenoNowPlayingBanner
                         ├── StationListenerActions (WhatsApp, web, social…)
                         └── StationPollSection

RadioStationsCatalog.mix938  ← single source for stream, WhatsApp, poll context
```

Add more stations later in `radio_stations_catalog.dart` → `onAirStations`.

---

## GitHub (your repo, not Kayise)

Push Flutter work to **`CambodiaDave/mygigguide_app`** (private). Do not rely on Kayise Laravel repo for app source.

```bash
git add lib/models lib/data lib/services lib/widgets lib/screens
git commit -m "Main app On Air: Mix 93.8 detail with poll, WhatsApp, Zeno"
git push origin main
```
