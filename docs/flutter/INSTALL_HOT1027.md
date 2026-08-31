# HOT 102.7 — in-app poll + schedule + hosts

Upgrade the standalone **`hot1027`** flavor to match VOW FM listener polls, plus live schedule and presenter photos from the station website API.

**Data source (schedule/hosts):** `https://hot1027.co.za/wp-json/radio/` (official Radio Station Pro REST API — no HTML scraping).

**Polls:** Laravel `GET /api/v1/polls/hot1027` on `https://www.mygigguide.co.za` (same as VOW/Mix).

---

## 1. On your laptop — get the files

If you use the Laravel repo as a reference folder:

```bash
cd ~/development/PHP-MyGigGuide
git fetch origin
git checkout cursor/hot1027-poll-schedule-858a
git pull origin cursor/hot1027-poll-schedule-858a
```

---

## 2. Copy into `mygigguide_app`

```bash
cd ~/development/mygigguide_app

# Poll (same as VOW — skip if already installed)
cp ~/development/PHP-MyGigGuide/docs/flutter/models/station_poll.dart lib/models/
cp ~/development/PHP-MyGigGuide/docs/flutter/services/poll_api_service.dart lib/services/
cp ~/development/PHP-MyGigGuide/docs/flutter/widgets/station_poll_section.dart lib/widgets/
cp ~/development/PHP-MyGigGuide/docs/flutter/screens/station_in_app_poll_screen.dart lib/screens/

# HOT schedule + hosts (new)
cp ~/development/PHP-MyGigGuide/docs/flutter/models/hot1027_schedule.dart lib/models/
cp ~/development/PHP-MyGigGuide/docs/flutter/services/hot1027_radio_repository.dart lib/services/
cp ~/development/PHP-MyGigGuide/docs/flutter/screens/hot1027_hosts_screen.dart lib/screens/

# Updated radio tab (poll inline + HOT schedule section)
cp ~/development/PHP-MyGigGuide/docs/flutter/station_radio_tab_screen.dart lib/screens/station_radio_tab_screen.dart
```

---

## 3. Merge into `lib/brand_config.dart`

Add from `docs/flutter/brand_config_poll_snippet.dart`:

```dart
static String get pollApiSiteUrl {
  const override = String.fromEnvironment('POLL_API_SITE_URL', defaultValue: '');
  if (override.isNotEmpty) return override;
  return 'https://www.mygigguide.co.za';
}

static String get stationPollContext {
  if (isMix938) return 'mix938';
  if (isVowFm) return 'vowfm';
  if (isHot1027) return 'hot1027';
  return '';
}
```

---

## 4. Create a HOT poll on the server (one-time)

```bash
ssh dave@mel55-nix02
cd /var/www/mygigguide
php artisan poll:create
```

Choose **`hot1027`** when prompted, then question + options.

Or use **Admin → Polls** on www.mygigguide.co.za with context **HOT 1027**.

Verify:

```bash
curl -s "https://www.mygigguide.co.za/api/v1/polls/hot1027" | head -c 400
```

---

## 5. Build and install

```bash
cd ~/development/mygigguide_app
./scripts/build_apk.sh hot1027 --label=poll-schedule
adb install -r build/app/outputs/flutter-apk/*hot1027*.apk
```

---

## What you should see in the app

| Feature | Where |
|---------|--------|
| **Listener poll** | Radio tab — full question, tap-to-vote, results bars (same as VOW) |
| **On air now** | Under the live player — current show name from station API |
| **Today’s schedule** | Scroll below action chips — time slots for today |
| **Hosts & shows** | Action chip → presenter list with photos and times |

---

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| “Poll unavailable” | Check `pollApiSiteUrl` points at `https://www.mygigguide.co.za`, not hot1027.co.za |
| No active poll | Run `poll:create` or add poll in admin with context `hot1027` |
| Schedule empty | Phone needs internet; test `curl https://hot1027.co.za/wp-json/radio/schedule/` |
| Compile error on `StationHostsScreen` | You already have `lib/screens/station_hosts_screen.dart` — only HOT uses `Hot1027HostsScreen` |
