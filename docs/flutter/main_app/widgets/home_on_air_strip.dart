import 'package:flutter/material.dart';

import '../data/radio_stations_catalog.dart';
import '../models/radio_station_entry.dart';
import '../screens/radio_station_detail_screen.dart';

/// Horizontal On Air strip for vanilla My Gig Guide Home tab.
class HomeOnAirStrip extends StatelessWidget {
  const HomeOnAirStrip({super.key});

  @override
  Widget build(BuildContext context) {
    final stations = RadioStationsCatalog.onAirStations;
    if (stations.isEmpty) return const SizedBox.shrink();

    final theme = Theme.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
          child: Text(
            'On Air',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.w800,
              color: Colors.white,
            ),
          ),
        ),
        SizedBox(
          height: 112,
          child: ListView.separated(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            scrollDirection: Axis.horizontal,
            itemCount: stations.length,
            separatorBuilder: (_, __) => const SizedBox(width: 10),
            itemBuilder: (context, index) {
              final station = stations[index];
              return _OnAirCard(
                station: station,
                onTap: () => RadioStationDetailScreen.open(context, station),
              );
            },
          ),
        ),
      ],
    );
  }
}

class _OnAirCard extends StatelessWidget {
  const _OnAirCard({
    required this.station,
    required this.onTap,
  });

  final RadioStationEntry station;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final accent = station.accentColor;

    return Material(
      color: const Color(0xFF12121A),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Container(
          width: 200,
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: accent.withValues(alpha: 0.45)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Row(
                children: [
                  Container(
                    width: 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: Colors.redAccent,
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 6),
                  Text(
                    'LIVE',
                    style: TextStyle(
                      color: accent,
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                      letterSpacing: 1.1,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                station.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                  fontSize: 16,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                station.frequencyLabel.isNotEmpty
                    ? station.frequencyLabel
                    : station.tagline,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: Color(0xFF64748B),
                  fontSize: 12,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
