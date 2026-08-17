class _Fm919NewsSection extends StatelessWidget {
  const _Fm919NewsSection({
    required this.digest,
    required this.loading,
    required this.accent,
    required this.onOpen,
    required this.onRefresh,
  });

  final Fm919NewsDigest? digest;
  final bool loading;
  final Color accent;
  final ValueChanged<String> onOpen;
  final Future<void> Function() onRefresh;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dark = BrandConfig.usesDarkChrome;
    final items = digest?.items ?? const <Fm919NewsItem>[];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                '919 FM news',
                style: theme.textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w700,
                  color: accent,
                ),
              ),
            ),
            if (loading)
              const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(strokeWidth: 2),
              )
            else
              IconButton(
                icon: const Icon(Icons.refresh, size: 20),
                onPressed: () => onRefresh(),
                tooltip: 'Refresh',
              ),
          ],
        ),
        const SizedBox(height: 8),
        if (!loading && digest == null)
          Text(
            'Could not load news.',
            style: theme.textTheme.bodySmall?.copyWith(
              color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
            ),
          )
        else if (!loading && items.isEmpty)
          Text(
            'No headlines right now.',
            style: theme.textTheme.bodySmall?.copyWith(
              color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
            ),
          )
        else
          ...items.take(8).map(
                (item) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text(
                    item.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  subtitle: item.publishedLabel != null
                      ? Text(item.publishedLabel!)
                      : null,
                  trailing: const Icon(Icons.open_in_new, size: 16),
                  onTap: () => onOpen(item.url),
                ),
              ),
      ],
    );
  }
}

class _RiseFmTodaySection extends StatelessWidget {
  const _RiseFmTodaySection({
    required this.schedule,
    required this.loading,
    required this.accent,
    required this.onRefresh,
  });

  final RiseFmDaySchedule? schedule;
  final bool loading;
  final Color accent;
  final Future<void> Function() onRefresh;

  static const _weekdayNames = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
  ];

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dark = BrandConfig.usesDarkChrome;
    final dayLabel = schedule?.dayLabel ?? _weekdayNames[DateTime.now().weekday - 1];
    final slots = schedule?.slots ?? const <RiseFmScheduleSlot>[];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                'Today on RISE FM',
                style: theme.textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w700,
                  color: accent,
                ),
              ),
            ),
            if (loading)
              const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(strokeWidth: 2),
              )
            else
              IconButton(
                icon: const Icon(Icons.refresh, size: 20),
                onPressed: () => onRefresh(),
                tooltip: 'Refresh',
              ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          dayLabel,
          style: theme.textTheme.bodySmall?.copyWith(
            color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
          ),
        ),
        const SizedBox(height: 12),
        if (!loading && schedule == null)
          Text(
            'Could not load schedule.',
            style: theme.textTheme.bodySmall?.copyWith(
              color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
            ),
          )
        else if (slots.isEmpty)
          Text(
            'No schedule slots for today.',
            style: theme.textTheme.bodySmall?.copyWith(
              color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
            ),
          )
        else
          ...slots.map(
            (slot) => _RiseScheduleRow(
              time: slot.timeRange,
              show: slot.show,
              host: slot.presenter ?? 'RISE team',
              accent: accent,
            ),
          ),
      ],
    );
  }
}

class _RiseScheduleRow extends StatelessWidget {
  const _RiseScheduleRow({
    required this.time,
    required this.show,
    required this.host,
    required this.accent,
  });

  final String time;
  final String show;
  final String host;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dark = BrandConfig.usesDarkChrome;

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 56,
            child: Text(
              time,
              style: theme.textTheme.labelMedium?.copyWith(
                color: accent,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  show,
                  style: theme.textTheme.bodyMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                    color: dark ? const Color(0xFFF8FAFC) : BrandConfig.lightText,
                  ),
                ),
                Text(
                  host,
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _RiseFmNewsSection extends StatelessWidget {
  const _RiseFmNewsSection({
    required this.digest,
    required this.loading,
    required this.accent,
    required this.onOpen,
    required this.onRefresh,
  });

  final RiseFmNewsDigest? digest;
  final bool loading;
  final Color accent;
  final ValueChanged<String> onOpen;
  final Future<void> Function() onRefresh;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dark = BrandConfig.usesDarkChrome;
    final items = digest?.items ?? const <RiseFmNewsItem>[];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                'Mpumalanga news',
                style: theme.textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w700,
                  color: accent,
                ),
              ),
            ),
            if (loading)
              const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(strokeWidth: 2),
              )
            else
              IconButton(
                icon: const Icon(Icons.refresh, size: 20),
                onPressed: () => onRefresh(),
                tooltip: 'Refresh',
              ),
          ],
        ),
        const SizedBox(height: 8),
        if (!loading && digest == null)
          Text(
            'Could not load news.',
            style: theme.textTheme.bodySmall?.copyWith(
              color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
            ),
          )
        else if (!loading && items.isEmpty)
          Text(
            'No headlines right now.',
            style: theme.textTheme.bodySmall?.copyWith(
              color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
            ),
          )
        else
          ...items.take(8).map(
                (item) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text(
                    item.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  subtitle: item.publishedLabel != null
                      ? Text(item.publishedLabel!)
                      : null,
                  trailing: const Icon(Icons.open_in_new, size: 16),
                  onTap: () => onOpen(item.url),
                ),
              ),
      ],
    );
  }
}

class _StationLivePlayerCard extends StatelessWidget {
  const _StationLivePlayerCard({
    required this.accent,
    required this.uiState,
    required this.streamLabel,
    required this.onToggle,
    this.schedule,
  });

  final Color accent;
  final RoguesRadioUiState uiState;
  final String streamLabel;
  final VoidCallback onToggle;
  final RiseFmDaySchedule? schedule;

  String? _currentShowLabel() {
    final slots = schedule?.slots;
    if (slots == null || slots.isEmpty) return null;
    final now = DateTime.now();
    final minutesNow = now.hour * 60 + now.minute;

    RiseFmScheduleSlot? current;
    for (final slot in slots) {
      final start = _parseStartMinutes(slot.timeRange);
      if (start == null) continue;
      if (start <= minutesNow) {
        current = slot;
      } else {
        break;
      }
    }
    return current?.show;
  }

  int? _parseStartMinutes(String timeRange) {
    final part = timeRange.split('-').first.trim();
    final pieces = part.split(':');
    if (pieces.length < 2) return null;
    final hour = int.tryParse(pieces[0]);
    final minute = int.tryParse(pieces[1]);
    if (hour == null || minute == null) return null;
    return hour * 60 + minute;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dark = BrandConfig.usesDarkChrome;
    final playing = uiState == RoguesRadioUiState.playing;
    final loading = uiState == RoguesRadioUiState.loading;
    final onNow = _currentShowLabel();

    return Card(
      clipBehavior: Clip.antiAlias,
      color: dark ? const Color(0xFF12121A) : null,
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
              BrandConfig.stationName,
              style: theme.textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.w700,
                color: dark ? const Color(0xFFF8FAFC) : BrandConfig.lightText,
              ),
            ),
            if (BrandConfig.stationFrequencyLabel.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                BrandConfig.stationFrequencyLabel,
                style: theme.textTheme.bodyMedium?.copyWith(
                  color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
                ),
              ),
            ],
            if (onNow != null) ...[
              const SizedBox(height: 6),
              Text(
                'On now: $onNow',
                style: theme.textTheme.bodySmall?.copyWith(color: accent),
              ),
            ],
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: loading ? null : onToggle,
                icon: Icon(
                  playing ? Icons.stop_rounded : Icons.play_arrow_rounded,
                ),
                label: Text(playing ? 'Stop' : streamLabel),
                style: FilledButton.styleFrom(
                  backgroundColor: accent,
                  foregroundColor: accent.computeLuminance() > 0.5 ? Colors.black : Colors.white,
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

class _RiseFmQualityPicker extends StatelessWidget {
  const _RiseFmQualityPicker({
    required this.quality,
    required this.accent,
    required this.onChanged,
  });

  final String quality;
  final Color accent;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Stream quality',
          style: theme.textTheme.titleSmall?.copyWith(
            fontWeight: FontWeight.w600,
            color: accent,
          ),
        ),
        const SizedBox(height: 8),
        SegmentedButton<String>(
          segments: const [
            ButtonSegment(value: 'medium', label: Text('Standard')),
            ButtonSegment(value: 'high', label: Text('High')),
          ],
          selected: {quality},
          onSelectionChanged: (selection) => onChanged(selection.first),
        ),
      ],
    );
  }
}

class _StationAction {
  const _StationAction({
    required this.icon,
    required this.label,
    required this.subtitle,
    required this.onTap,
    this.faIcon,
  });

  final IconData icon;
  final String label;
  final String subtitle;
  final void Function(BuildContext context) onTap;
  final FaIconData? faIcon;
}

class _StationActionChip extends StatelessWidget {
  const _StationActionChip({
    required this.action,
    required this.accent,
  });

  final _StationAction action;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dark = BrandConfig.usesDarkChrome;

    return Material(
      color: dark ? const Color(0xFF12121A) : theme.colorScheme.surfaceContainerHighest,
      borderRadius: BorderRadius.circular(10),
      child: InkWell(
        borderRadius: BorderRadius.circular(10),
        onTap: () => action.onTap(context),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
          child: Row(
            children: [
              if (action.faIcon != null)
                FaIcon(action.faIcon, size: 16, color: accent)
              else
                AppIcon(action.icon, size: 16, color: accent),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  action.label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: theme.textTheme.labelLarge?.copyWith(
                    fontWeight: FontWeight.w600,
                    color: dark ? const Color(0xFFF8FAFC) : BrandConfig.lightText,
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

/// Rogues flavor keeps the historical screen name alias for imports/tests.
typedef RoguesRadioTabScreen = StationRadioTabScreen;
