import 'package:flutter/material.dart';

import '../brand_config.dart';
import '../models/hot1027_schedule.dart';
import '../services/hot1027_radio_repository.dart';

/// Presenters and shows for the HOT 1027 standalone flavor.
class Hot1027HostsScreen extends StatefulWidget {
  const Hot1027HostsScreen({super.key});

  @override
  State<Hot1027HostsScreen> createState() => _Hot1027HostsScreenState();
}

class _Hot1027HostsScreenState extends State<Hot1027HostsScreen> {
  final _repo = Hot1027RadioRepository();
  List<Hot1027Presenter> _presenters = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final list = await _repo.fetchPresenters();
      if (!mounted) return;
      setState(() {
        _presenters = list;
        _loading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final accent = BrandConfig.brandAccent;
    final theme = Theme.of(context);
    final dark = BrandConfig.usesDarkChrome;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Hosts & shows'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loading ? null : _load,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _presenters.isEmpty
                ? ListView(
                    children: const [
                      SizedBox(height: 48),
                      Center(child: Text('Could not load presenters.')),
                    ],
                  )
                : ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _presenters.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final p = _presenters[index];
                      return Card(
                        color: dark ? const Color(0xFF12121A) : null,
                        child: ListTile(
                          leading: CircleAvatar(
                            backgroundColor: accent.withValues(alpha: 0.2),
                            backgroundImage:
                                p.imageUrl != null ? NetworkImage(p.imageUrl!) : null,
                            child: p.imageUrl == null
                                ? Text(
                                    p.name.isNotEmpty ? p.name[0].toUpperCase() : '?',
                                    style: TextStyle(color: accent),
                                  )
                                : null,
                          ),
                          title: Text(
                            p.name,
                            style: theme.textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          subtitle: p.scheduleSummary.isEmpty
                              ? null
                              : Text(
                                  p.scheduleSummary.take(3).join('\n'),
                                  maxLines: 4,
                                  overflow: TextOverflow.ellipsis,
                                ),
                          isThreeLine: p.scheduleSummary.isNotEmpty,
                        ),
                      );
                    },
                  ),
      ),
    );
  }
}
