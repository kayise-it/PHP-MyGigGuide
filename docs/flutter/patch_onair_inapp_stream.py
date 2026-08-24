#!/usr/bin/env python3
"""
Patch the main-app On Air station detail screen so "Listen Live"
plays the stream inside the app instead of opening the browser.

Run from ~/development/mygigguide_app:
    python3 ~/development/PHP-MyGigGuide/docs/flutter/patch_onair_inapp_stream.py
"""

import os
import re
import sys
import shutil

APP_DIR = os.path.dirname(os.path.abspath(__file__))
# Script is in PHP-MyGigGuide/docs/flutter/ — app is at ~/development/mygigguide_app
# If run from the app dir, use cwd; otherwise fall back to ~/development/mygigguide_app
if os.path.isfile('pubspec.yaml'):
    APP_DIR = os.getcwd()
else:
    APP_DIR = os.path.expanduser('~/development/mygigguide_app')

DETAIL_SCREEN = os.path.join(APP_DIR, 'lib/screens/radio_station_detail_screen.dart')
CATALOG       = os.path.join(APP_DIR, 'lib/data/radio_stations_catalog.dart')
RADIO_PLAYER  = os.path.join(APP_DIR, 'lib/services/rogues_radio_player.dart')

# In-app first; if playback fails, open iono/Zeno in browser (works on all flavors).
LISTEN_LIVE_ON_PRESSED = """                    onPressed: () async {
                      final streamUrl = station.streamUrl.trim();
                      final webUrl = (station.webPlayerUrl ?? station.streamUrl).trim();

                      if (streamUrl.isNotEmpty) {
                        try {
                          await ref.read(roguesRadioPlayerProvider).setStreamUrl(streamUrl);
                          await ref.read(roguesRadioPlayerProvider).toggle();
                          if (!context.mounted) return;
                          final state = ref.read(roguesRadioPlayerProvider).uiState;
                          if (state != RoguesRadioUiState.error) return;
                        } catch (_) {
                          // Fall through to browser player.
                        }
                      }

                      if (webUrl.isNotEmpty) {
                        _open(context, webUrl);
                        return;
                      }

                      if (!context.mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Stream URL not configured.')),
                      );
                    },"""

def backup(path):
    bak = path + '.bak'
    shutil.copy2(path, bak)
    print(f'  Backed up → {bak}')

def read(path):
    with open(path, 'r') as f:
        return f.read()

def write(path, content):
    with open(path, 'w') as f:
        f.write(content)

def abort(msg):
    print(f'\n✗ {msg}')
    sys.exit(1)

# ─────────────────────────────────────────────────────────────────────────────
# 1. Patch radio_station_detail_screen.dart
# ─────────────────────────────────────────────────────────────────────────────

def patch_detail_screen():
    print(f'\n[1] Patching {DETAIL_SCREEN}')
    if not os.path.isfile(DETAIL_SCREEN):
        abort(f'File not found: {DETAIL_SCREEN}\n'
              f'    Make sure you run this from ~/development/mygigguide_app\n'
              f'    or that the path above is correct.')

    src = read(DETAIL_SCREEN)
    changed = False

    # ── 1a. Add Riverpod / player imports if missing ──────────────────────────
    riverpod_import = "import 'package:flutter_riverpod/flutter_riverpod.dart';"
    player_provider = "import '../providers/rogues_radio_player_provider.dart';"
    rogues_player   = "import '../services/rogues_radio_player.dart';"

    imports_to_add = []
    for imp in [riverpod_import, player_provider, rogues_player]:
        if imp not in src:
            imports_to_add.append(imp)

    if imports_to_add:
        # Insert after the first import line
        first_import_end = src.index('\n', src.index('import ')) + 1
        src = src[:first_import_end] + '\n'.join(imports_to_add) + '\n' + src[first_import_end:]
        print(f'  Added imports: {", ".join(imports_to_add)}')
        changed = True
    else:
        print('  Imports already present.')

    # ── 1b. Change StatefulWidget → ConsumerStatefulWidget if needed ──────────
    if 'ConsumerStatefulWidget' not in src:
        if 'extends StatefulWidget' in src:
            src = src.replace('extends StatefulWidget', 'extends ConsumerStatefulWidget', 1)
            print('  Changed StatefulWidget → ConsumerStatefulWidget')
            changed = True
        # State class
        # matches: extends State<RadioStationDetailScreen> or State<...>
        src = re.sub(
            r'extends State<(\w+)>',
            r'extends ConsumerState<\1>',
            src,
            count=1
        )
        print('  Changed State → ConsumerState')
        changed = True
    else:
        print('  Already ConsumerStatefulWidget.')

    # ── 1c. Replace the browser Listen Live button with in-app player ─────────
    # Match the exact block we saw in the session output.
    OLD_BUTTON = r'''                  // Listen Live — opens iono\.fm web player in external browser\.
                  FilledButton\.icon\(
                    style: FilledButton\.styleFrom\(
                      backgroundColor: accent,
                      foregroundColor: _contrastColor\(accent\),
                      padding: const EdgeInsets\.symmetric\(vertical: 14\),
                      shape: RoundedRectangleBorder\(borderRadius: BorderRadius\.circular\(10\)\),
                    \),
                    onPressed: \(\) \{
                      final url = station\.webPlayerUrl \?\? station\.streamUrl;
                      _open\(context, url\);
                    \},
                    icon: const Icon\(Icons\.play_circle_filled_rounded\),
                    label: const Text\(
                      \'Listen Live\',
                      style: TextStyle\(fontWeight: FontWeight\.w700, fontSize: 16\),
                    \),
                  \),'''

    NEW_BUTTON = '''                  // Listen Live — in-app stream, browser fallback if needed.
                  FilledButton.icon(
                    style: FilledButton.styleFrom(
                      backgroundColor: accent,
                      foregroundColor: _contrastColor(accent),
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    onPressed: () async {
                      final streamUrl = station.streamUrl.trim();
                      final webUrl = (station.webPlayerUrl ?? station.streamUrl).trim();

                      if (streamUrl.isNotEmpty) {
                        try {
                          await ref.read(roguesRadioPlayerProvider).setStreamUrl(streamUrl);
                          await ref.read(roguesRadioPlayerProvider).toggle();
                          if (!context.mounted) return;
                          final state = ref.read(roguesRadioPlayerProvider).uiState;
                          if (state != RoguesRadioUiState.error) return;
                        } catch (_) {
                          // Fall through to browser player.
                        }
                      }

                      if (webUrl.isNotEmpty) {
                        _open(context, webUrl);
                        return;
                      }

                      if (!context.mounted) return;
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Stream URL not configured.')),
                      );
                    },
                    icon: const Icon(Icons.play_circle_filled_rounded),
                    label: const Text(
                      'Listen Live',
                      style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
                    ),
                  ),'''

    new_src, count = re.subn(OLD_BUTTON, NEW_BUTTON.replace('\\', '\\\\'), src, flags=re.DOTALL)
    if count == 0:
        # Try simpler match — just the onPressed block
        OLD_SIMPLE = (
            "                    onPressed: () {\n"
            "                      final url = station.webPlayerUrl ?? station.streamUrl;\n"
            "                      _open(context, url);\n"
            "                    },"
        )
        NEW_SIMPLE = LISTEN_LIVE_ON_PRESSED
        if OLD_SIMPLE in src:
            new_src = src.replace(OLD_SIMPLE, NEW_SIMPLE, 1)
            print('  Replaced Listen Live button (simple match).')
            changed = True
        else:
            print('\n  ⚠  Could not automatically patch Listen Live button.')
            print('     The button code in your file differs from the expected pattern.')
            print('     Run the manual Step 2b from the Cursor chat and paste any errors.')
            new_src = src
    else:
        print('  Replaced Listen Live button (regex match).')
        changed = True
        src = new_src

    # ── 1d. Also update comment on Listen Live line ────────────────────────────
    new_src = new_src.replace(
        '// Listen Live — opens iono.fm web player in external browser.',
        '// Listen Live — direct in-app stream (streamUrl).'
    )

    if changed:
        backup(DETAIL_SCREEN)
        write(DETAIL_SCREEN, new_src)
        print('  ✓ Saved.')
    else:
        print('  No changes needed (already patched?).')


def patch_v1_is_supported_block():
    """Replace v1 patch that blocked main-app flavor via RoguesRadioPlayer.isSupported."""
    print(f'\n[1b] Fixing v1 isSupported block in {DETAIL_SCREEN}')
    if not os.path.isfile(DETAIL_SCREEN):
        return

    src = read(DETAIL_SCREEN)
    if 'Live stream is not available on this device' not in src:
        print('  No v1 isSupported block found.')
        return

    # Replace the whole onPressed async block that starts with isSupported check.
    pattern = re.compile(
        r"onPressed: \(\) async \{\s*"
        r"if \(!RoguesRadioPlayer\.isSupported\) \{.*?\}\s*"
        r"return;\s*\}\s*"
        r"final url = station\.streamUrl\.trim\(\);.*?"
        r"\},",
        re.DOTALL,
    )
    new_src, count = pattern.subn(LISTEN_LIVE_ON_PRESSED, src, count=1)
    if count:
        backup(DETAIL_SCREEN)
        write(DETAIL_SCREEN, new_src)
        print('  ✓ Removed isSupported gate; added browser fallback.')
        return

    print('  ⚠  Found error message but could not auto-replace — edit manually.')


def patch_radio_player():
    """Allow in-app playback on Android/iOS even when flavor has no Radio tab."""
    print(f'\n[3] Patching {RADIO_PLAYER}')
    if not os.path.isfile(RADIO_PLAYER):
        print('  File not found — skip (On Air may still work via browser fallback).')
        return

    src = read(RADIO_PLAYER)
    if 'static bool get isSupported' not in src:
        print('  No isSupported getter — skip.')
        return

    # Replace entire isSupported getter body with mobile-friendly check.
    new_getter = """static bool get isSupported {
    if (kIsWeb) return false;
    return defaultTargetPlatform == TargetPlatform.android ||
        defaultTargetPlatform == TargetPlatform.iOS;
  }"""

    new_src, count = re.subn(
        r'static bool get isSupported \{.*?\}',
        new_getter,
        src,
        count=1,
        flags=re.DOTALL,
    )
    if count == 0:
        print('  ⚠  Could not patch isSupported — check lib/services/rogues_radio_player.dart manually.')
        return

    if 'foundation.dart' not in new_src and 'defaultTargetPlatform' in new_getter:
        if "import 'package:flutter/foundation.dart';" not in new_src:
            first_import_end = new_src.index('\n', new_src.index('import ')) + 1
            new_src = (
                new_src[:first_import_end]
                + "import 'package:flutter/foundation.dart';\n"
                + new_src[first_import_end:]
            )

    backup(RADIO_PLAYER)
    write(RADIO_PLAYER, new_src)
    print('  ✓ isSupported now true on Android/iOS (not web).')


# ─────────────────────────────────────────────────────────────────────────────
# 2. Patch radio_stations_catalog.dart — ensure VOW uses edge.iono.fm
# ─────────────────────────────────────────────────────────────────────────────

def patch_catalog():
    print(f'\n[2] Patching {CATALOG}')
    if not os.path.isfile(CATALOG):
        abort(f'File not found: {CATALOG}')

    src = read(CATALOG)
    changed = False

    # Fix comment if it says "Audio is opened via webPlayerUrl in external browser"
    OLD_COMMENT = "/// Audio is opened via [webPlayerUrl] in the external browser — no in-app"
    NEW_COMMENT = "/// [streamUrl] = in-app play; [webPlayerUrl] = optional full web player (iono/Zeno)."
    if OLD_COMMENT in src:
        src = src.replace(OLD_COMMENT, NEW_COMMENT, 1)
        print('  Updated catalog doc comment.')
        changed = True

    # Fix VOW streamUrl if still pointing to the web player URL
    OLD_VOW_STREAM = "    streamUrl: 'https://streaming.iono.fm/s/101',"
    NEW_VOW_STREAM = "    streamUrl: 'https://edge.iono.fm/xice/101_medium.aac',"
    if OLD_VOW_STREAM in src:
        src = src.replace(OLD_VOW_STREAM, NEW_VOW_STREAM, 1)
        print('  Fixed VOW streamUrl → edge.iono.fm/xice/101_medium.aac')
        changed = True
    elif 'edge.iono.fm/xice/101_medium.aac' in src:
        print('  VOW streamUrl already correct.')
    else:
        print('  ⚠  Could not find VOW streamUrl to fix. Check lib/data/radio_stations_catalog.dart manually.')

    # Fix VOW 101_high if present (high tier doesn't exist for community stations)
    if '101_high.aac' in src:
        src = src.replace("'https://edge.iono.fm/xice/101_high.aac'",
                          "'https://edge.iono.fm/xice/101_medium.aac'")
        print('  Replaced 101_high.aac → 101_medium.aac (no high tier for VOW)')
        changed = True

    if changed:
        backup(CATALOG)
        write(CATALOG, src)
        print('  ✓ Saved.')
    else:
        print('  No changes needed.')


# ─────────────────────────────────────────────────────────────────────────────
# Run
# ─────────────────────────────────────────────────────────────────────────────

print('=== MGG On Air in-app stream patch ===')
print(f'App dir: {APP_DIR}')

patch_detail_screen()
patch_v1_is_supported_block()
patch_catalog()
patch_radio_player()

print('\n=== Done. Next steps: ===')
print('  1. flutter analyze lib/screens/radio_station_detail_screen.dart')
print('     lib/services/rogues_radio_player.dart')
print('  2. flutter run --flavor mygigguide \\')
print('       --dart-define=BRAND=mygigguide \\')
print('       --dart-define=SITE_URL=https://www.mygigguide.co.za')
print('  3. On Air → VOW or Mix → Listen Live → in-app audio OR browser fallback')
print('  4. ./scripts/build_apk.sh mygigguide --label=onair-inapp-stream-v2')
