import 'package:flutter/material.dart';

import '../brand_config.dart';
import '../widgets/station_poll_section.dart';

/// Full-screen listener poll for station flavors (Mix 93.8, VOW FM, etc.).
class StationInAppPollScreen extends StatelessWidget {
  const StationInAppPollScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final pollContext = BrandConfig.stationPollContext.trim();
    final accent = BrandConfig.stationAccent;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Listener poll'),
      ),
      body: pollContext.isEmpty
          ? const Center(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: Text('Listener poll is not configured for this station yet.'),
              ),
            )
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
              children: [
                StationPollSection(
                  pollContext: pollContext,
                  accentColor: accent,
                ),
              ],
            ),
    );
  }
}
