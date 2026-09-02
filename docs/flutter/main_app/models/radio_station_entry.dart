import 'package:flutter/material.dart';

/// One station in the main-app On Air catalog (vanilla mygigguide flavor).
class RadioStationEntry {
  const RadioStationEntry({
    required this.id,
    required this.name,
    required this.tagline,
    required this.streamUrl,
    required this.accentColor,
    required this.pollContext,
    this.frequencyLabel = '',
    this.zenoMountId,
    this.zenoWebUrl,
    this.websiteUrl = '',
    this.whatsAppE164 = '',
    this.studioPhoneE164 = '',
    this.socialLinks = const [],
    this.supportsInAppHosts = false,
  });

  final String id;
  final String name;
  final String tagline;
  final String frequencyLabel;
  final String streamUrl;
  final Color accentColor;
  final String pollContext;
  final String? zenoMountId;
  final String? zenoWebUrl;
  final String websiteUrl;
  final String whatsAppE164;
  final String studioPhoneE164;
  final List<RadioStationSocialLink> socialLinks;

  /// When true, detail screen opens [StationHostsScreen] in-app.
  /// Mix from main app uses website until hosts screen accepts a station id.
  final bool supportsInAppHosts;

  Uri? get whatsAppUri {
    final digits = whatsAppE164.replaceAll(RegExp(r'\D'), '');
    if (digits.isEmpty) return null;
    return Uri.parse('https://wa.me/$digits');
  }

  String get whatsAppDisplay {
    if (whatsAppE164.isEmpty) return '';
    if (whatsAppE164.startsWith('27') && whatsAppE164.length >= 11) {
      final local = whatsAppE164.substring(2);
      if (local.length == 9) {
        return '0${local.substring(0, 2)} ${local.substring(2, 5)} ${local.substring(5)}';
      }
    }
    return whatsAppE164;
  }
}

class RadioStationSocialLink {
  const RadioStationSocialLink({
    required this.label,
    required this.url,
    required this.iconKey,
  });

  final String label;
  final String url;
  final String iconKey;
}
