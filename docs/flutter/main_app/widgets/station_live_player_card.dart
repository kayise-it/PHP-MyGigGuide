import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../models/radio_station_entry.dart';
import '../providers/rogues_radio_player_provider.dart';
import '../services/rogues_radio_player.dart';

/// Compact live player — shared by main-app station detail and flavor radio tabs.
class StationLivePlayerCard extends ConsumerWidget {
  const StationLivePlayerCard({
    super.key,
    required this.station,
    this.streamLabel = 'Play live',
  });

  final RadioStationEntry station;
  final String streamLabel;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final theme = Theme.of(context);
    final accent = station.accentColor;
    final uiState = ref.watch(roguesRadioUiStateProvider).maybeWhen(
          data: (state) => state,
          orElse: () => RoguesRadioUiState.idle,
        );
    final playing = uiState == RoguesRadioUiState.playing;
    final loading = uiState == RoguesRadioUiState.loading;

    return Card(
      clipBehavior: Clip.antiAlias,
      color: const Color(0xFF12121A),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 10,
                  height: 10,
                  decoration: BoxDecoration(
                    color: playing ? Colors.redAccent : theme.disabledColor,
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 8),
                Text(
                  playing ? 'ON AIR' : 'LIVE STREAM',
                  style: theme.textTheme.labelLarge?.copyWith(
                    fontWeight: FontWeight.w700,
                    letterSpacing: 1.2,
                    color: playing ? Colors.redAccent : accent,
                  ),
                ),
                const Spacer(),
                if (loading)
                  const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              station.name,
              style: theme.textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.w700,
                color: const Color(0xFFF8FAFC),
              ),
            ),
            if (station.tagline.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                station.frequencyLabel.isNotEmpty
                    ? '${station.frequencyLabel} · ${station.tagline}'
                    : station.tagline,
                style: theme.textTheme.bodyMedium?.copyWith(
                  color: const Color(0xFF64748B),
                ),
              ),
            ],
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: loading
                    ? null
                    : () async {
                        if (!RoguesRadioPlayer.isSupported) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Live stream is not available on this device.'),
                            ),
                          );
                          return;
                        }
                        await ref.read(roguesRadioPlayerProvider).setStreamUrl(station.streamUrl);
                        await ref.read(roguesRadioPlayerProvider).toggle();
                      },
                icon: Icon(playing ? Icons.stop_rounded : Icons.play_arrow_rounded),
                label: Text(playing ? 'Stop' : streamLabel),
                style: FilledButton.styleFrom(
                  backgroundColor: accent,
                  foregroundColor:
                      accent.computeLuminance() > 0.5 ? Colors.black : Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
