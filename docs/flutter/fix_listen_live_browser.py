#!/usr/bin/env python3
"""
Fix On Air Listen Live — open web player (iono / Zeno), not raw .aac URLs.

Bug in v4 script: it searched for 'Listen Live' BEFORE onPressed, but
FilledButton.icon puts the label AFTER onPressed — so the patch never ran.

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

BROWSER_ON_PRESSED = """                    onPressed: () async {
                      // Browser only on main MGG — use web player page, not .aac stream.
                      final webUrl = station.webPlayerUrl?.trim();
                      final url = (webUrl != null && webUrl.isNotEmpty)
                          ? webUrl
                          : station.streamUrl.trim();
                      if (url.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(content: Text('Stream URL not configured.')),
                        );
                        return;
                      }
                      await _open(context, url);
                    },"""


def backup(path: str) -> None:
    shutil.copy2(path, path + ".bak-browser2")


def read(path: str) -> str:
    with open(path, encoding="utf-8") as f:
        return f.read()


def write(path: str, content: str) -> None:
    with open(path, "w", encoding="utf-8") as f:
        f.write(content)


def replace_listen_live_on_pressed(src: str) -> tuple[str, bool]:
    """Find FilledButton.icon Listen Live block — label is AFTER onPressed."""
    marker = "'Listen Live'"
    pos = 0
    while True:
        idx = src.find(marker, pos)
        if idx < 0:
            return src, False

        # Search backward from label for onPressed: () { ... },
        chunk_start = max(0, idx - 2000)
        chunk = src[chunk_start:idx]
        matches = list(re.finditer(r"onPressed:\s*\(\)\s*(?:async\s*)?\{", chunk))
        if not matches:
            pos = idx + 1
            continue

        m = matches[-1]
        abs_start = chunk_start + m.start()

        # Find closing }, of onPressed (first balanced close at line indent)
        depth = 0
        i = chunk_start + m.end() - 1
        while i < len(src):
            c = src[i]
            if c == "{":
                depth += 1
            elif c == "}":
                depth -= 1
                if depth == 0:
                    end = i + 1
                    if src[end : end + 1] == ",":
                        end += 1
                    new_src = src[:abs_start] + BROWSER_ON_PRESSED + src[end:]
                    return new_src, True
            i += 1

        pos = idx + 1

    return src, False


def fix_detail_listen_live() -> bool:
    if not os.path.isfile(DETAIL):
        print(f"✗ Missing {DETAIL}")
        return False

    src = read(DETAIL)
    new_src, ok = replace_listen_live_on_pressed(src)
    if not ok:
        print("✗ Could not find Listen Live button — paste lines 80-130 from:")
        print(f"  {DETAIL}")
        return False

    if new_src == src:
        print("✓ Detail screen already patched")
        return True

    backup(DETAIL)
    write(DETAIL, new_src)
    print("✓ Patched Listen Live → webPlayerUrl in browser")
    return True


def fix_catalog_web_urls() -> None:
    if not os.path.isfile(CATALOG):
        print("− catalog not found")
        return

    src = read(CATALOG)
    original = src

    # Ensure webPlayerUrl exists for VOW + Mix (browser pages, not .aac).
    patches = [
        (
            re.compile(
                r"(id:\s*'vow[^']*'.*?streamUrl:\s*'https://edge\.iono\.fm/xice/101_medium\.aac',)"
                r"(?!\s*\n\s*webPlayerUrl:)",
                re.DOTALL | re.IGNORECASE,
            ),
            r"\1\n    webPlayerUrl: 'https://iono.fm/s/101',",
        ),
        (
            re.compile(
                r"(id:\s*'mix[^']*'.*?streamUrl:\s*'[^']+',)"
                r"(?!\s*\n\s*webPlayerUrl:)",
                re.DOTALL | re.IGNORECASE,
            ),
            r"\1\n    webPlayerUrl: 'https://zeno.fm/radio/mix-938/',",
        ),
    ]

    for pattern, repl in patches:
        src, n = pattern.subn(repl, src, count=1)
        if n:
            print(f"  Added webPlayerUrl ({pattern.pattern[:30]}…)")

    # Direct string inserts if regex missed
    if "webPlayerUrl: 'https://iono.fm/s/101'" not in src and "101_medium.aac" in src:
        src = src.replace(
            "streamUrl: 'https://edge.iono.fm/xice/101_medium.aac',",
            "streamUrl: 'https://edge.iono.fm/xice/101_medium.aac',\n"
            "    webPlayerUrl: 'https://iono.fm/s/101',",
            1,
        )
        print("  Added VOW webPlayerUrl (string replace)")

    if "zeno.fm/radio/mix" not in src.lower() and "mix" in src.lower():
        # Best-effort: user may already have webPlayerUrl under different slug
        pass

    if src != original:
        backup(CATALOG)
        write(CATALOG, src)
        print("✓ Updated radio_stations_catalog.dart")
    else:
        print("✓ Catalog webPlayerUrl entries look OK")


def main() -> int:
    print(f"App: {APP_DIR}\n")
    ok = fix_detail_listen_live()
    fix_catalog_web_urls()

    print("\nVerify Listen Live handler:")
    os.system(f"sed -n '85,120p' '{DETAIL}' 2>/dev/null || true")

    print("\nVerify catalog URLs:")
    os.system(
        f"grep -E 'webPlayerUrl|streamUrl' '{CATALOG}' | grep -iE 'vow|mix|101|938' | head -10"
    )

    if ok:
        print("\nNext:")
        print("  cd ~/development/mygigguide_app")
        print("  flutter analyze lib/screens/radio_station_detail_screen.dart")
        print("  ./scripts/build_apk.sh mygigguide --label=onair-browser-v5")
        print("  adb install -r build/app/outputs/flutter-apk/app-mygigguide-release.apk")
    return 0 if ok else 1


if __name__ == "__main__":
    raise SystemExit(main())
