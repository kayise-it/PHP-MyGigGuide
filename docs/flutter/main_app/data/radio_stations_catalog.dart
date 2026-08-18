import 'package:flutter/material.dart';

import '../models/radio_station_entry.dart';

/// Stations shown on the vanilla My Gig Guide Home → On Air strip.
class RadioStationsCatalog {
  RadioStationsCatalog._();

  static const mix938Accent = Color(0xFF00E676);

  /// Studio / WhatsApp: 066 417 8469
  static const mix938StudioE164 = '27664178469';

  static const mix938 = RadioStationEntry(
    id: 'mix938',
    name: 'Mix 93.8',
    tagline: 'Mpumalanga\'s Hit Music',
    frequencyLabel: '93.8 FM',
    streamUrl: 'https://stream.zeno.fm/lnwzymabtd3vv',
    accentColor: mix938Accent,
    pollContext: 'mix938',
    zenoMountId: 'lnwzymabtd3vv',
    zenoWebUrl: 'https://zeno.fm/radio/mix-938/',
    websiteUrl: 'https://mix938.com',
    whatsAppE164: mix938StudioE164,
    studioPhoneE164: mix938StudioE164,
    socialLinks: [
      RadioStationSocialLink(
        label: 'Facebook',
        url: 'https://www.facebook.com/MixFM93.8',
        iconKey: 'facebook',
      ),
      RadioStationSocialLink(
        label: 'Instagram',
        url: 'https://www.instagram.com/therealmix938/',
        iconKey: 'instagram',
      ),
    ],
    supportsInAppHosts: false,
  );

  /// Stations visible on the main app Home On Air strip (add more over time).
  static const List<RadioStationEntry> onAirStations = [
    mix938,
  ];

  static RadioStationEntry? byId(String id) {
    for (final station in onAirStations) {
      if (station.id == id) return station;
    }
    return null;
  }
}
