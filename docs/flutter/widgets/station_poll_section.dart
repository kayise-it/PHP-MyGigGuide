import 'package:flutter/material.dart';

import '../models/station_poll.dart';
import '../services/poll_api_service.dart';

/// Listener poll block — flavor radio tabs and main-app station detail.
class StationPollSection extends StatefulWidget {
  const StationPollSection({
    super.key,
    required this.pollContext,
    required this.accentColor,
  });

  final String pollContext;
  final Color accentColor;

  @override
  State<StationPollSection> createState() => _StationPollSectionState();
}

class _StationPollSectionState extends State<StationPollSection> {
  final _api = PollApiService();
  StationPoll? _poll;
  bool _loading = true;
  String? _error;
  bool _voted = false;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final poll = await _api.fetchPoll(widget.pollContext);
      if (!mounted) return;
      setState(() {
        _poll = poll;
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  Future<void> _vote(int index) async {
    final poll = _poll;
    if (poll == null || !poll.isOpen || _submitting || _voted) return;
    setState(() => _submitting = true);
    try {
      final updated = await _api.vote(pollId: poll.id, optionIndex: index);
      if (!mounted) return;
      setState(() {
        _poll = updated;
        _voted = true;
        _submitting = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Thanks — vote recorded')),
      );
    } on PollAlreadyVotedException catch (e) {
      if (!mounted) return;
      setState(() {
        _poll = e.poll;
        _voted = true;
        _submitting = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _submitting = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString())),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final accent = widget.accentColor;
    final theme = Theme.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                'Listener poll',
                style: theme.textTheme.titleMedium?.copyWith(
                  fontWeight: FontWeight.w700,
                  color: accent,
                ),
              ),
            ),
            if (_loading)
              const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(strokeWidth: 2),
              )
            else
              IconButton(
                icon: const Icon(Icons.refresh, size: 20),
                onPressed: _load,
                tooltip: 'Refresh poll',
              ),
          ],
        ),
        const SizedBox(height: 8),
        if (_loading)
          const SizedBox.shrink()
        else if (_error != null)
          Text(
            'Poll unavailable right now.',
            style: theme.textTheme.bodySmall,
          )
        else if (_poll == null)
          Text(
            'No active poll at the moment.',
            style: theme.textTheme.bodySmall,
          )
        else ...[
          Text(
            _poll!.question,
            style: theme.textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 10),
          ...List.generate(_poll!.options.length, (index) {
            final label = _poll!.options[index];
            final count = index < _poll!.voteCounts.length ? _poll!.voteCounts[index] : 0;
            final total = _poll!.totalVotes;
            final pct = total > 0 ? count / total : 0.0;
            final showResults = _voted || !_poll!.isOpen;

            return Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: InkWell(
                borderRadius: BorderRadius.circular(10),
                onTap: _poll!.isOpen && !_voted && !_submitting
                    ? () => _vote(index)
                    : null,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: accent.withValues(alpha: 0.35)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(label),
                      if (showResults) ...[
                        const SizedBox(height: 6),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(4),
                          child: LinearProgressIndicator(
                            value: pct,
                            minHeight: 6,
                            backgroundColor: accent.withValues(alpha: 0.15),
                            color: accent,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '$count vote${count == 1 ? '' : 's'}',
                          style: theme.textTheme.labelSmall,
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            );
          }),
          if (!_poll!.isOpen)
            Text(
              'This poll is closed.',
              style: theme.textTheme.bodySmall,
            ),
        ],
      ],
    );
  }
}
