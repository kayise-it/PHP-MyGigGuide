#!/usr/bin/env python3
"""Patch lib/screens/station_radio_tab_screen.dart to match Dave's parser API.

Run from mygigguide_app root:
  python3 patch_station_radio.py
"""
from pathlib import Path

TARGET = Path("lib/screens/station_radio_tab_screen.dart")

REPLACEMENTS = [
    (
        """                  subtitle: item.date != null
                      ? Text(item.date!)""",
        """                  subtitle: item.excerpt.isNotEmpty
                      ? Text(
                          item.excerpt,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        )""",
    ),
    (
        """    final dayLabel = schedule?.weekday ?? _weekdayNames[DateTime.now().weekday - 1];
    final entries = schedule?.entries ?? const <RiseFmScheduleEntry>[];""",
        """    final dayLabel = schedule != null
        ? _weekdayNames[(schedule!.weekday.clamp(1, 7)) - 1]
        : _weekdayNames[DateTime.now().weekday - 1];
    final shows = schedule?.shows ?? [];""",
    ),
    ("else if (entries.isEmpty)", "else if (shows.isEmpty)"),
    (
        """          ...entries.map(
            (entry) => _RiseScheduleRow(
              time: entry.time,
              show: entry.show,
              host: entry.presenter ?? 'RISE team',
              accent: accent,
            ),
          ),""",
        """          ...shows.map(
            (show) => _RiseScheduleRow(
              time: show.timeRange,
              show: show.title,
              host: show.host ?? 'RISE team',
              accent: accent,
            ),
          ),""",
    ),
    (
        """  String? _currentShowLabel() {
    final entries = schedule?.entries;
    if (entries == null || entries.isEmpty) return null;
    final now = DateTime.now();
    final minutesNow = now.hour * 60 + now.minute;

    RiseFmScheduleEntry? current;
    for (final entry in entries) {
      final start = _parseStartMinutes(entry.time);
      if (start == null) continue;
      if (start <= minutesNow) {
        current = entry;
      } else {
        break;
      }
    }
    return current?.show;
  }""",
        """  String? _currentShowLabel() {
    final shows = schedule?.shows;
    if (shows == null || shows.isEmpty) return null;
    final now = DateTime.now();
    final minutesNow = now.hour * 60 + now.minute;

    var currentShow = shows.first.title;
    for (final show in shows) {
      final start = _parseStartMinutes(show.timeRange);
      if (start == null) continue;
      if (start <= minutesNow) {
        currentShow = show.title;
      } else {
        break;
      }
    }
    return currentShow;
  }""",
    ),
    ("BrandConfig.stationName", "BrandConfig.stationTabHeroTitle"),
    ("BrandConfig.stationFrequencyLabel", "BrandConfig.stationTabHeroSubtitle"),
]


def main() -> None:
    if not TARGET.exists():
        raise SystemExit(f"Not found: {TARGET} (run from mygigguide_app root)")

    text = TARGET.read_text()
    original = text

    for old, new in REPLACEMENTS:
        if old in text:
            text = text.replace(old, new)

    if text == original:
        print("No changes made — file may already be patched.")
        return

    TARGET.write_text(text)
    print(f"Patched {TARGET}")
    print("Verify:")
    print("  grep -n 'item.date\\|entries\\|RiseFmScheduleEntry' lib/screens/station_radio_tab_screen.dart || echo OK")


if __name__ == "__main__":
    main()
