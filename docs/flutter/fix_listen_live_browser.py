#!/usr/bin/env python3
"""
On Air Listen Live: open iono/Zeno in browser (reliable on main mygigguide APK).

In-app play needs foreground-service permissions stripped from mygigguide flavor.
The v2 handler tried in-app first, got a non-error idle state, and returned
before opening the browser — so the button appeared to do nothing.

Run:
  python3 ~/development/PHP-MyGigGuide/docs/flutter/fix_listen_live_browser.py
"""

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
CATALOG = os.path.join(APP_DIR, "lib/data/radio_stations_catalog.dart")

# Browser-first — same as original working behaviour.
BROWSER_ON_PRESSED = """                    onPressed: () {
                      final url = (station.webPlayerUrl ?? station.streamUrl).trim();
                      if (url.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Stream URL not configured.')),
                        );
                        return;
                      }
                      _open(context, url);
                    },"""


def backup(path: str) -> None:
    shutil.copy2(path, path + ".bak-browser")


def read(path: str) -> str:
    with open(path, encoding="utf-8") as f:
        return f.read()


def write(path: str, content: str) -> None:
    with open(path, "w", encoding="utf-8") as f:
        f.write(content)


def fix_detail_listen_live() -> bool:
    if not os.path.isfile(DETAIL):
        print(f"✗ Missing {DETAIL}")
        return False

    src = read(DETAIL)
    pattern = re.compile(r"onPressed:\s*\(\)\s*(?:async\s*)?\{.*?\n\s*\},", re.DOTALL)

    replaced = False
    new_src = src
    for m in pattern.finditer(src):
        before = src[max(0, m.start() - 600) : m.start()]
        if "Listen Live" not in before:
            continue
        new_src = src[: m.start()] + BROWSER_ON_PRESSED + src[m.end() :]
        replaced = True
        break

    if not replaced:
        print("✗ Could not find Listen Live onPressed — paste lines 85-120 from the file")
        return False

    backup(DETAIL)
    write(DETAIL, new_src)
    print("✓ Listen Live now opens web player in browser")
    return True


def fix_catalog_urls() -> None:
    if not os.path.isfile(CATALOG):
        print("− catalog not found")
        return

    src = read(CATALOG)
    changed = False

    replacements = [
        ("'https://streaming.iono.fm/s/101'", "'https://iono.fm/s/101'"),  # web player
        ("streamUrl: 'https://iono.fm/s/101'", "streamUrl: 'https://edge.iono.fm/xice/101_medium.aac'"),
    ]
    for old, new in replacements:
        if old in src and new not in src:
            src = src.replace(old, new, 1)
            changed = True
            print(f"  catalog: {old[:40]}…")

    if changed:
        backup(CATALOG)
        write(CATALOG, src)
        print("✓ Updated radio_stations_catalog.dart URLs")
    else:
        print("✓ Catalog URLs look OK")


def main() -> int:
    print(f"App: {APP_DIR}\n")
    ok = fix_detail_listen_live()
    fix_catalog_urls()
    if ok:
        print("\nNext:")
        print("  cd ~/development/mygigguide_app")
        print("  grep -A6 -i vow lib/data/radio_stations_catalog.dart | head -20")
        print("  ./scripts/build_apk.sh mygigguide --label=onair-browser-v4")
        print("  adb install -r build/app/outputs/flutter-apk/app-mygigguide-release.apk")
    return 0 if ok else 1


if __name__ == "__main__":
    raise SystemExit(main())
