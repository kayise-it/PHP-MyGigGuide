#!/usr/bin/env python3
"""Remove 'Live stream is not available on this device' from On Air / Radio screens."""

from __future__ import annotations

import os
import re
import shutil
import sys

APP_DIR = os.path.expanduser("~/development/mygigguide_app")
if len(sys.argv) > 1:
    APP_DIR = sys.argv[1]
elif os.path.isfile("pubspec.yaml"):
    APP_DIR = os.getcwd()

DETAIL = os.path.join(APP_DIR, "lib/screens/radio_station_detail_screen.dart")
RADIO_TAB = os.path.join(APP_DIR, "lib/screens/station_radio_tab_screen.dart")
PLAYER = os.path.join(APP_DIR, "lib/services/rogues_radio_player.dart")

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
                    },"""

IS_SUPPORTED_SNACK_RE = re.compile(
    r"if\s*\(!RoguesRadioPlayer\.isSupported\)\s*\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}\s*return;\s*\}\s*",
    re.DOTALL,
)

IS_SUPPORTED_SNACK_TAB_RE = re.compile(
    r"if\s*\(!RoguesRadioPlayer\.isSupported\)\s*\{\s*"
    r"_snack\(context,\s*'Live stream is not available on this device\.'\);\s*"
    r"return;\s*\}\s*",
    re.DOTALL,
)


def backup(path: str) -> None:
    shutil.copy2(path, path + ".bak-fix")


def read(path: str) -> str:
    with open(path, encoding="utf-8") as f:
        return f.read()


def write(path: str, content: str) -> None:
    with open(path, "w", encoding="utf-8") as f:
        f.write(content)


def fix_detail_screen() -> bool:
    if not os.path.isfile(DETAIL):
        print(f"✗ Missing {DETAIL}")
        return False

    src = read(DETAIL)
    if "not available on this device" not in src and "RoguesRadioPlayer.isSupported" not in src:
        print("✓ radio_station_detail_screen.dart already OK")
        return True

    new_src = IS_SUPPORTED_SNACK_RE.sub("", src)

    if "not available on this device" in new_src:
        pattern = re.compile(r"onPressed:\s*\(\)\s*async\s*\{.*?\n\s*\},", re.DOTALL)
        for m in pattern.finditer(new_src):
            chunk = new_src[max(0, m.start() - 500) : m.start()]
            if "Listen Live" in chunk or "not available on this device" in m.group(0):
                new_src = new_src[: m.start()] + LISTEN_LIVE_ON_PRESSED + new_src[m.end() :]
                break

    if new_src == src:
        print("✗ Could not patch radio_station_detail_screen.dart — open file ~line 95-120 manually")
        return False

    backup(DETAIL)
    write(DETAIL, new_src)
    print("✓ Fixed radio_station_detail_screen.dart")
    return True


def fix_radio_tab() -> bool:
    if not os.path.isfile(RADIO_TAB):
        print("− station_radio_tab_screen.dart not found (skip)")
        return True

    src = read(RADIO_TAB)
    if "Live stream is not available on this device" not in src:
        print("✓ station_radio_tab_screen.dart already OK")
        return True

    new_src = IS_SUPPORTED_SNACK_TAB_RE.sub("", src)
    new_src = IS_SUPPORTED_SNACK_RE.sub("", new_src)

    if new_src == src:
        print("✗ Could not patch station_radio_tab_screen.dart — edit ~line 167 manually")
        return False

    backup(RADIO_TAB)
    write(RADIO_TAB, new_src)
    print("✓ Fixed station_radio_tab_screen.dart")
    return True


def fix_player() -> None:
    if not os.path.isfile(PLAYER):
        print("− rogues_radio_player.dart not found (skip)")
        return

    src = read(PLAYER)
    if "static bool get isSupported" not in src:
        return

    new_getter = """static bool get isSupported {
    if (kIsWeb) return false;
    return defaultTargetPlatform == TargetPlatform.android ||
        defaultTargetPlatform == TargetPlatform.iOS;
  }"""
    new_src, n = re.subn(
        r"static bool get isSupported \{.*?\}",
        new_getter,
        src,
        count=1,
        flags=re.DOTALL,
    )
    if n == 0:
        return
    if "package:flutter/foundation.dart" not in new_src:
        new_src = new_src.replace(
            "import 'package:flutter/material.dart';",
            "import 'package:flutter/material.dart';\nimport 'package:flutter/foundation.dart';",
            1,
        )
    backup(PLAYER)
    write(PLAYER, new_src)
    print("✓ Fixed rogues_radio_player.dart isSupported")


def main() -> int:
    print(f"App: {APP_DIR}\n")
    ok = fix_detail_screen() and fix_radio_tab()
    fix_player()

    print("\nVerify:")
    os.system(f'grep -rn "not available on this device" "{APP_DIR}/lib/" | grep -v firebase_auth || true')

    if ok:
        print("\nNext:")
        print("  cd ~/development/mygigguide_app")
        print("  flutter analyze lib/screens/radio_station_detail_screen.dart lib/screens/station_radio_tab_screen.dart")
        print("  ./scripts/build_apk.sh mygigguide --label=onair-listen-live-v3")
        print("  adb install -r build/app/outputs/flutter-apk/app-mygigguide-release.apk")
    return 0 if ok else 1


if __name__ == "__main__":
    raise SystemExit(main())
