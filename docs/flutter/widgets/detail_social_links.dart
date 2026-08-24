import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

/// Social profile chips for artist / event detail screens.
/// Pass only the URLs you have — hidden when all are empty.
class DetailSocialLinks extends StatelessWidget {
  const DetailSocialLinks({
    super.key,
    this.instagram,
    this.facebook,
    this.twitter,
    this.tiktok,
    this.title = 'Social',
  });

  final String? instagram;
  final String? facebook;
  final String? twitter;
  final String? tiktok;
  final String title;

  static bool _filled(String? value) =>
      value != null && value.trim().isNotEmpty;

  bool get _hasAny =>
      _filled(instagram) ||
      _filled(facebook) ||
      _filled(twitter) ||
      _filled(tiktok);

  @override
  Widget build(BuildContext context) {
    if (!_hasAny) {
      return const SizedBox.shrink();
    }

    final entries = <MapEntry<String, String>>[];
    if (_filled(instagram)) entries.add(MapEntry('Instagram', instagram!.trim()));
    if (_filled(facebook)) entries.add(MapEntry('Facebook', facebook!.trim()));
    if (_filled(twitter)) entries.add(MapEntry('X', twitter!.trim()));
    if (_filled(tiktok)) entries.add(MapEntry('TikTok', tiktok!.trim()));

    final theme = Theme.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: theme.textTheme.titleMedium),
        const SizedBox(height: 8),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            for (final entry in entries)
              ActionChip(
                label: Text(entry.key),
                onPressed: () => _openUrl(context, entry.value),
              ),
          ],
        ),
      ],
    );
  }

  Future<void> _openUrl(BuildContext context, String url) async {
    final uri = Uri.tryParse(url);
    if (uri == null) {
      _snack(context, 'Invalid link');
      return;
    }

    final opened = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!opened && context.mounted) {
      _snack(context, 'Could not open link');
    }
  }

  void _snack(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }
}
