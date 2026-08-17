# Fix truncated station_radio_tab_screen.dart

Your file was cut off at ~941 lines. The full file is ~1151 lines.

**Note:** Flutter files belong in **your** repo (`CambodiaDave/mygigguide_app`), not the Kayise Laravel repo. The Kayise `raw.githubusercontent.com` URL returns 404 unless you have org access.

## Quick fix on your laptop (no Kayise download)

From `~/development/mygigguide_app`, save `patch_station_radio.py` (see below or copy from this chat), then:

```bash
python3 patch_station_radio.py
./scripts/build_apk.sh mix938 --label=nowplaying-v4
```

| File | Copy to |
|------|---------|
| `station_radio_tab_screen.dart` | `lib/screens/station_radio_tab_screen.dart` |
| `zeno_now_playing_service.dart` | `lib/services/zeno_now_playing_service.dart` |
| `zeno_now_playing_banner.dart` | `lib/widgets/zeno_now_playing_banner.dart` |

## On your laptop

```bash
cd ~/development/mygigguide_app

# Download from GitHub (after Dave pulls this branch), OR copy from Cursor cloud workspace:
# docs/flutter/ in PHP-MyGigGuide repo branch cursor/flutter-station-radio-fix-858a

cp /path/to/station_radio_tab_screen.dart lib/screens/station_radio_tab_screen.dart
cp /path/to/zeno_now_playing_service.dart lib/services/zeno_now_playing_service.dart
cp /path/to/zeno_now_playing_banner.dart lib/widgets/zeno_now_playing_banner.dart

./scripts/build_apk.sh mix938 --label=nowplaying-v1
```

## Verify full file

```bash
wc -l lib/screens/station_radio_tab_screen.dart
tail -1 lib/screens/station_radio_tab_screen.dart
```

Should be **~1140 lines** and last line:
`typedef RoguesRadioTabScreen = StationRadioTabScreen;`
