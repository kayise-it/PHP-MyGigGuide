import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:url_launcher/url_launcher.dart';

import '../data/radio_stations_catalog.dart';
import '../models/radio_station_entry.dart';
import '../widgets/station_listener_actions.dart';
import '../widgets/station_live_player_card.dart';
import '../widgets/station_poll_section.dart';
import '../widgets/zeno_now_playing_banner.dart';
import 'station_hosts_screen.dart';

/// Main My Gig Guide — opened from Home → On Air strip (not a flavor radio tab).
class RadioStationDetailScreen extends ConsumerStatefulWidget {
  const RadioStationDetailScreen({
    super.key,
    required this.station,
  });

  final RadioStationEntry station;

  static Future<void> open(BuildContext context, RadioStationEntry station) {
    return Navigator.of(context).push<void>(
      MaterialPageRoute<void>(
        builder: (_) => RadioStationDetailScreen(station: station),
      ),
    );
  }

  /// Convenience for Mix 93.8.
  static Future<void> openMix938(BuildContext context) {
    return open(context, RadioStationsCatalog.mix938);
  }

  @override
  ConsumerState<RadioStationDetailScreen> createState() =>
      _RadioStationDetailScreenState();
}

class _RadioStationDetailScreenState extends ConsumerState<RadioStationDetailScreen> {
  RadioStationEntry get station => widget.station;

  Future<void> _openHosts(BuildContext context) async {
    if (station.supportsInAppHosts) {
      await Navigator.of(context).push<void>(
        MaterialPageRoute<void>(builder: (_) => const StationHostsScreen()),
      );
      return;
    }
    final url = station.websiteUrl.trim();
    if (url.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Hosts page not configured yet.')),
      );
      return;
    }
    final uri = Uri.parse(url);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not open website.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final accent = station.accentColor;

    return Scaffold(
      appBar: AppBar(
        title: Text(station.name),
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
      ),
      backgroundColor: Colors.black,
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
        children: [
          Text(
            station.tagline,
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.w800,
              color: accent,
            ),
          ),
          const SizedBox(height: 12),
          StationLivePlayerCard(station: station),
          if (station.zenoMountId != null && station.zenoMountId!.isNotEmpty) ...[
            const SizedBox(height: 8),
            ZenoNowPlayingBanner(mountId: station.zenoMountId!),
          ],
          const SizedBox(height: 12),
          StationListenerActions(
            station: station,
            onHosts: _openHosts,
          ),
          const SizedBox(height: 20),
          StationPollSection(
            pollContext: station.pollContext,
            accentColor: station.accentColor,
          ),
        ],
      ),
    );
  }
}
