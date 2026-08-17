# Fix truncated station_radio_tab_screen.dart

Your file was cut off at ~941 lines. The full file is ~1470 lines.

## Files in this folder

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
