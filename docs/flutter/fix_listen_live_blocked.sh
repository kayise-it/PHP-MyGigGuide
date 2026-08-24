#!/usr/bin/env bash
# Fix "Live stream is not available on this device" on main mygigguide APK.
# Run from laptop:
#   bash ~/development/PHP-MyGigGuide/docs/flutter/fix_listen_live_blocked.sh

set -euo pipefail

APP_DIR="${1:-$HOME/development/mygigguide_app}"

if [[ ! -f "$APP_DIR/pubspec.yaml" ]]; then
  echo "✗ Flutter app not found at: $APP_DIR"
  echo "  Usage: bash fix_listen_live_blocked.sh [/path/to/mygigguide_app]"
  exit 1
fi

cd "$APP_DIR"
echo "=== Listen Live fix — app: $APP_DIR ==="
echo

echo "--- Step 1: Where is the error message? ---"
if rg -n "not available on this device|isSupported" lib/ 2>/dev/null; then
  :
else
  echo "(no matches — maybe already fixed in source; check you installed the latest APK)"
fi
echo

echo "--- Step 2: Run Python patch ---"
python3 "$HOME/development/PHP-MyGigGuide/docs/flutter/patch_onair_inapp_stream.py" || true
echo

echo "--- Step 3: Force-remove isSupported gate in detail screen (if still present) ---"
DETAIL="lib/screens/radio_station_detail_screen.dart"
if [[ -f "$DETAIL" ]] && grep -q "not available on this device" "$DETAIL"; then
  cp "$DETAIL" "${DETAIL}.bak-manual"
  python3 - <<'PY'
from pathlib import Path
import re

path = Path("lib/screens/radio_station_detail_screen.dart")
src = path.read_text()
replacement = """onPressed: () async {
                      final streamUrl = station.streamUrl.trim();
                      final webUrl = (station.webPlayerUrl ?? station.streamUrl).trim();

                      if (streamUrl.isNotEmpty) {
                        try {
                          await ref.read(roguesRadioPlayerProvider).setStreamUrl(streamUrl);
                          await ref.read(roguesRadioPlayerProvider).toggle();
                          if (!context.mounted) return;
                          final state = ref.read(roguesRadioPlayerProvider).uiState;
                          if (state != RoguesRadioUiState.error) return;
                        } catch (_) {}
                      }

                      if (webUrl.isNotEmpty) {
                        _open(context, webUrl);
                        return;
                      }

                      if (!context.mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Stream URL not configured.')),
                      );
                    }"""

# Replace any onPressed that mentions isSupported or the error snackbar
pattern = re.compile(
    r"onPressed:\s*\(\)\s*async\s*\{[^}]*RoguesRadioPlayer\.isSupported[^}]*\}[^}]*\},?",
    re.DOTALL,
)
new_src, n = pattern.subn(replacement + ",", src, count=1)
if n == 0:
    # Strip just the isSupported if-block, keep rest
    new_src = re.sub(
        r"if\s*\(!RoguesRadioPlayer\.isSupported\)\s*\{[^}]+\}\s*return;\s*\}\s*",
        "",
        src,
        count=1,
    )
path.write_text(new_src)
print("Patched", path)
PY
else
  echo "Detail screen OK (no blocked message)."
fi
echo

echo "--- Step 4: Fix rogues_radio_player isSupported (all lib dart files) ---"
PLAYER="lib/services/rogues_radio_player.dart"
if [[ -f "$PLAYER" ]]; then
  cp "$PLAYER" "${PLAYER}.bak-manual"
  python3 - <<'PY'
from pathlib import Path
import re

path = Path("lib/services/rogues_radio_player.dart")
src = path.read_text()
new_getter = """static bool get isSupported {
    if (kIsWeb) return false;
    return defaultTargetPlatform == TargetPlatform.android ||
        defaultTargetPlatform == TargetPlatform.iOS;
  }"""
src2, n = re.subn(r"static bool get isSupported \{.*?\}", new_getter, src, count=1, flags=re.DOTALL)
if n:
    if "package:flutter/foundation.dart" not in src2:
        src2 = src2.replace("import 'package:flutter/material.dart';",
                            "import 'package:flutter/material.dart';\nimport 'package:flutter/foundation.dart';", 1)
    path.write_text(src2)
    print("Patched isSupported in", path)
else:
    print("Could not patch isSupported — edit manually:", path)
PY
fi

echo
echo "--- Step 5: Remove error text anywhere else in lib/ ---"
while IFS= read -r file; do
  [[ "$file" == *radio_station_detail_screen.dart ]] && continue
  [[ "$file" == *rogues_radio_player.dart ]] && continue
  echo "  Also patching: $file"
  cp "$file" "${file}.bak-manual"
  python3 - <<PY
from pathlib import Path
p = Path("$file")
t = p.read_text()
t = t.replace("Live stream is not available on this device.", "Could not start live stream.")
t = t.replace("if (!RoguesRadioPlayer.isSupported) return;", "")
p.write_text(t)
PY
done < <(rg -l "not available on this device|RoguesRadioPlayer.isSupported" lib/ 2>/dev/null || true)

echo
echo "--- Step 6: Verify ---"
if rg -n "not available on this device" lib/ 2>/dev/null; then
  echo "✗ Message still in source — paste output above back to Cursor"
  exit 1
else
  echo "✓ Error string removed from lib/"
fi

echo
echo "--- Step 7: Analyze ---"
flutter analyze lib/screens/radio_station_detail_screen.dart lib/services/rogues_radio_player.dart 2>/dev/null || true

echo
echo "=== Rebuild APK (required — old install will still show the error) ==="
echo "  cd $APP_DIR"
echo "  ./scripts/build_apk.sh mygigguide --label=onair-listen-live-v3"
echo "  adb install -r build/app/outputs/flutter-apk/app-mygigguide-release.apk"
