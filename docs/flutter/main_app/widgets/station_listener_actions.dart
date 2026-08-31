import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart' show FaIcon, FontAwesomeIcons;
import 'package:url_launcher/url_launcher.dart';

import '../app_icon_data.dart';
import '../models/radio_station_entry.dart';
import '../widgets/app_icon.dart';

typedef StationActionCallback = Future<void> Function(BuildContext context);

/// Primary listener actions — WhatsApp, website, social, hosts, Zeno.
class StationListenerActions extends StatelessWidget {
  const StationListenerActions({
    super.key,
    required this.station,
    this.onHosts,
  });

  final RadioStationEntry station;
  final StationActionCallback? onHosts;

  Future<void> _openUri(BuildContext context, Uri? uri, String failMessage) async {
    if (uri == null) {
      _snack(context, failMessage);
      return;
    }
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      if (context.mounted) _snack(context, failMessage);
    }
  }

  void _snack(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  IconData? _faIcon(String key) {
    switch (key) {
      case 'facebook':
        return FontAwesomeIcons.facebook;
      case 'instagram':
        return FontAwesomeIcons.instagram;
      case 'youtube':
        return FontAwesomeIcons.youtube;
      default:
        return null;
    }
  }

  @override
  Widget build(BuildContext context) {
    final accent = station.accentColor;
    final chips = <Widget>[];

    if (station.whatsAppUri != null) {
      chips.add(_ActionChip(
        accent: accent,
        icon: AppIconData.chat,
        label: 'WhatsApp studio',
        subtitle: station.whatsAppDisplay,
        onTap: () => _openUri(context, station.whatsAppUri, 'Could not open WhatsApp.'),
      ));
    }

    if (station.websiteUrl.isNotEmpty) {
      chips.add(_ActionChip(
        accent: accent,
        icon: AppIconData.globe,
        label: 'Website',
        subtitle: Uri.tryParse(station.websiteUrl)?.host ?? station.websiteUrl,
        onTap: () => _openUri(context, Uri.parse(station.websiteUrl), 'Could not open website.'),
      ));
    }

    if (onHosts != null) {
      chips.add(_ActionChip(
        accent: accent,
        icon: AppIconData.groups,
        label: 'Hosts & shows',
        subtitle: 'Who\'s on air',
        onTap: () => onHosts!(context),
      ));
    }

    if (station.zenoWebUrl != null && station.zenoWebUrl!.isNotEmpty) {
      chips.add(_ActionChip(
        accent: accent,
        icon: AppIconData.playCircle,
        label: 'Open on Zeno',
        subtitle: 'Full web player',
        onTap: () => _openUri(context, Uri.parse(station.zenoWebUrl!), 'Could not open Zeno.'),
      ));
    }

    if (station.studioPhoneE164.isNotEmpty) {
      chips.add(_ActionChip(
        accent: accent,
        icon: Icons.phone_outlined,
        label: 'Call studio',
        subtitle: station.whatsAppDisplay.isNotEmpty
            ? station.whatsAppDisplay
            : station.studioPhoneE164,
        onTap: () => _openUri(
          context,
          Uri.parse('tel:+${station.studioPhoneE164.replaceAll(RegExp(r'\D'), '')}'),
          'Could not open phone dialler.',
        ),
      ));
    }

    for (final social in station.socialLinks) {
      final fa = _faIcon(social.iconKey);
      chips.add(_ActionChip(
        accent: accent,
        icon: AppIconData.globe,
        faIcon: fa,
        label: social.label,
        subtitle: 'Follow us',
        onTap: () => _openUri(context, Uri.parse(social.url), 'Could not open link.'),
      ));
    }

    if (chips.isEmpty) return const SizedBox.shrink();

    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      mainAxisSpacing: 6,
      crossAxisSpacing: 6,
      childAspectRatio: 2.8,
      children: chips,
    );
  }
}

class _ActionChip extends StatelessWidget {
  const _ActionChip({
    required this.accent,
    required this.icon,
    required this.label,
    required this.subtitle,
    required this.onTap,
    this.faIcon,
  });

  final Color accent;
  final IconData icon;
  final IconData? faIcon;
  final String label;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Material(
      color: const Color(0xFF12121A),
      borderRadius: BorderRadius.circular(10),
      child: InkWell(
        borderRadius: BorderRadius.circular(10),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
          child: Row(
            children: [
              if (faIcon != null)
                FaIcon(faIcon, size: 16, color: accent)
              else
                AppIcon(icon, size: 16, color: accent),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: theme.textTheme.labelLarge?.copyWith(
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFFF8FAFC),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
