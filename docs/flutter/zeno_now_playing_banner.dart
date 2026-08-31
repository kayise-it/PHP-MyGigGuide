import 'package:flutter/material.dart';

import '../brand_config.dart';
import '../services/zeno_now_playing_service.dart';

class ZenoNowPlayingBanner extends StatefulWidget {
  const ZenoNowPlayingBanner({super.key, required this.mountId});

  final String mountId;

  @override
  State<ZenoNowPlayingBanner> createState() => _ZenoNowPlayingBannerState();
}

class _ZenoNowPlayingBannerState extends State<ZenoNowPlayingBanner> {
  late final ZenoNowPlayingService _service;
  String _title = 'Loading now playing…';

  @override
  void initState() {
    super.initState();
    _service = ZenoNowPlayingService(mountId: widget.mountId);
    _service.stream.listen((title) {
      if (mounted) setState(() => _title = title);
    });
    _service.start();
  }

  @override
  void dispose() {
    _service.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final accent = BrandConfig.stationAccent;
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 0),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFF060910),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: accent.withValues(alpha: 0.35)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Now playing',
            style: TextStyle(
              color: accent,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            _title,
            style: const TextStyle(color: Colors.white, fontSize: 15),
          ),
        ],
      ),
    );
  }
}
