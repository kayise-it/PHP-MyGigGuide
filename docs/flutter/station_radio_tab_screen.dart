import 'dart:async';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';
import 'package:xml/xml.dart' as xml;

import '../brand_config.dart';
import '../services/station_audio_service.dart';
import '../widgets/brand_logo_header.dart';
import '../widgets/zeno_now_playing_banner.dart';
import '../widgets/station_poll_section.dart';

/// Radio tab for station-branded app flavors (Rogues, FM919, HOT1027, VOW FM,
/// Rise FM, Mix 93.8). Vanilla My Gig Guide uses the On Air strip on Home instead.
class StationRadioTabScreen extends StatefulWidget {
  const StationRadioTabScreen({super.key});

  @override
  State<StationRadioTabScreen> createState() => _StationRadioTabScreenState();
}

class _StationRadioTabScreenState extends State<StationRadioTabScreen> {
  final StationAudioService _audio = StationAudioService.instance;
  StreamSubscription<PlayerState>? _playerSub;
  bool _playing = false;
  bool _buffering = false;
  String? _playerError;
  RiseStreamQuality _riseQuality = RiseStreamQuality.standard;

  @override
  void initState() {
    super.initState();
    _syncFromService();
    _playerSub = _audio.playerStateStream.listen((_) {
      if (mounted) _syncFromService();
    });
  }

  void _syncFromService() {
    setState(() {
      _playing = _audio.isPlayingForCurrentBrand;
      _buffering = _audio.isBuffering;
      _playerError = _audio.lastError;
      _riseQuality = _audio.riseStreamQuality;
    });
  }

  @override
  void dispose() {
    _playerSub?.cancel();
    super.dispose();
  }

  Future<void> _togglePlay() async {
    setState(() => _playerError = null);
    try {
      if (_playing) {
        await _audio.pause();
      } else {
        await _audio.playCurrentBrand(quality: _riseQuality);
      }
    } catch (e) {
      if (mounted) {
        setState(() => _playerError = e.toString());
      }
    }
  }

  Future<void> _onRiseQualityChanged(RiseStreamQuality quality) async {
    setState(() => _riseQuality = quality);
    _audio.riseStreamQuality = quality;
    if (_playing && BrandConfig.isRiseFm) {
      await _audio.playCurrentBrand(quality: quality);
    }
  }

  Future<void> _openUrl(String url) async {
    final uri = Uri.tryParse(url);
    if (uri == null) return;
    final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Could not open $url')),
      );
    }
  }

  List<_StationAction> _buildActions() {
    final actions = <_StationAction>[];

    if (BrandConfig.stationWebsiteUrl.isNotEmpty) {
      actions.add(
        _StationAction(
          label: 'Website',
          icon: Icons.language,
          url: BrandConfig.stationWebsiteUrl,
        ),
      );
    }

    if (BrandConfig.isRogues) {
      actions.addAll(const [
        _StationAction(
          label: 'Request a song',
          icon: Icons.music_note,
          url: 'https://roguesonradio.co.za/#contact',
        ),
        _StationAction(
          label: 'Competitions',
          icon: Icons.emoji_events_outlined,
          url: 'https://roguesonradio.co.za/',
        ),
      ]);
    }

    if (BrandConfig.isFm919 && BrandConfig.fm919FacebookUrl.isNotEmpty) {
      actions.add(
        _StationAction(
          label: 'Facebook',
          icon: Icons.facebook,
          url: BrandConfig.fm919FacebookUrl,
        ),
      );
    }

    if (BrandConfig.isHot1027 && BrandConfig.hot1027InstagramUrl.isNotEmpty) {
      actions.add(
        _StationAction(
          label: 'Instagram',
          icon: Icons.camera_alt_outlined,
          url: BrandConfig.hot1027InstagramUrl,
        ),
      );
    }

    if (BrandConfig.isVowFm && BrandConfig.vowFmWhatsAppUrl.isNotEmpty) {
      actions.add(
        _StationAction(
          label: 'WhatsApp',
          icon: Icons.chat,
          url: BrandConfig.vowFmWhatsAppUrl,
        ),
      );
    }

    if (BrandConfig.isRiseFm) {
      actions.addAll(const [
        _StationAction(
          label: 'Competitions',
          icon: Icons.card_giftcard_outlined,
          url: 'https://risefm.co.za/competitions/',
        ),
        _StationAction(
          label: 'RISE Rewind',
          icon: Icons.history,
          url: 'https://risefm.co.za/riserewind/',
        ),
      ]);
    }

    if (BrandConfig.isMix938) {
      if (BrandConfig.mix938InstagramUrl.isNotEmpty) {
        actions.add(
          _StationAction(
            label: 'Instagram',
            icon: Icons.camera_alt_outlined,
            url: BrandConfig.mix938InstagramUrl,
          ),
        );
      }
      actions.add(
        _StationAction(
          label: 'Open on Zeno',
          icon: Icons.open_in_new,
          url: BrandConfig.mix938ZenoWebUrl,
        ),
      );
      if (BrandConfig.mix938WhatsAppUrl.isNotEmpty) {
        actions.add(
          _StationAction(
            label: 'WhatsApp',
            icon: Icons.chat,
            url: BrandConfig.mix938WhatsAppUrl,
          ),
        );
      }
    }

    return actions;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final accent = BrandConfig.brandAccent;
    final actions = _buildActions();

    return Scaffold(
      backgroundColor: theme.scaffoldBackgroundColor,
      body: SafeArea(
        child: CustomScrollView(
          slivers: [
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                child: BrandLogoHeader(
                  title: BrandConfig.stationTabTitle,
                  subtitle: BrandConfig.stationTagline,
                ),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  _StationLivePlayerCard(
                    playing: _playing,
                    buffering: _buffering,
                    error: _playerError,
                    accent: accent,
                    onToggle: _togglePlay,
                  ),
                  const SizedBox(height: 8),
                  if (BrandConfig.isMix938) ...[
                    ZenoNowPlayingBanner(mountId: BrandConfig.mix938ZenoMountId),
                    const SizedBox(height: 8),
                  ],
                  if (BrandConfig.isRiseFm) ...[
                    _RiseFmQualityPicker(
                      value: _riseQuality,
                      accent: accent,
                      onChanged: _onRiseQualityChanged,
                    ),
                    const SizedBox(height: 12),
                  ],
                  if (actions.isNotEmpty) ...[
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: actions
                          .map(
                            (a) => _StationActionChip(
                              action: a,
                              accent: accent,
                              onTap: () => _openUrl(a.url),
                            ),
                          )
                          .toList(),
                    ),
                    const SizedBox(height: 16),
                  ],
                  if (BrandConfig.stationPollContext.isNotEmpty)
                    StationPollSection(contextKey: BrandConfig.stationPollContext),
                  if (BrandConfig.isRogues) ...[
                    const SizedBox(height: 8),
                    _RoguesHostsSection(accent: accent),
                    const SizedBox(height: 12),
                    _RoguesEngagementSection(accent: accent),
                    const SizedBox(height: 12),
                    _RoguesPartnersSection(accent: accent),
                  ],
                  if (BrandConfig.isFm919) ...[
                    const SizedBox(height: 8),
                    _Fm919ShowsSection(accent: accent),
                    const SizedBox(height: 12),
                    _Fm919NewsSection(accent: accent),
                  ],
                  if (BrandConfig.isRiseFm) ...[
                    const SizedBox(height: 8),
                    _RiseFmTodaySection(accent: accent),
                    const SizedBox(height: 12),
                    _RiseFmNewsSection(accent: accent),
                  ],
                  if (BrandConfig.isHot1027) ...[
                    const SizedBox(height: 8),
                    _Hot1027ShowsSection(accent: accent),
                  ],
                  if (BrandConfig.isVowFm) ...[
                    const SizedBox(height: 8),
                    _VowFmCommunitySection(accent: accent),
                  ],
                  if (BrandConfig.isMix938) ...[
                    const SizedBox(height: 8),
                    _Mix938DaypartsSection(accent: accent),
                    const SizedBox(height: 12),
                    _Mix938AboutSection(accent: accent),
                  ],
                  const SizedBox(height: 32),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// Rogues — hosts & shows (static mirror of roguesonradio.co.za lineup)
// ---------------------------------------------------------------------------

class _RoguesPresenterEntry {
  const _RoguesPresenterEntry({
    required this.name,
    required this.shows,
    required this.schedule,
  });

  final String name;
  final String shows;
  final String schedule;
}

const _kRoguesPresenters = <_RoguesPresenterEntry>[
  _RoguesPresenterEntry(
    name: 'Sean',
    shows: 'The Morning Heist with Sean & Crew · The Rogue Report',
    schedule: 'Weekdays & Thursdays · 7am–10am · 7pm–9pm',
  ),
  _RoguesPresenterEntry(
    name: 'Aldine',
    shows: 'The Morning Heist with Sean & Crew',
    schedule: 'Weekdays · 7am–10am',
  ),
  _RoguesPresenterEntry(
    name: 'Richard',
    shows: 'The Morning Heist with Sean & Crew · The Rogue Report',
    schedule: 'Weekdays & Thursdays · 7am–10am · 7pm–9pm',
  ),
  _RoguesPresenterEntry(
    name: 'Sunil',
    shows: 'The Midmorning Escape',
    schedule: 'Weekdays · 10am–1pm',
  ),
  _RoguesPresenterEntry(
    name: 'Gavin',
    shows: 'The Lunch Time Fix',
    schedule: 'Weekdays · 1pm–4pm',
  ),
  _RoguesPresenterEntry(
    name: 'Clive',
    shows: 'The Big Drive with Clive & Gang',
    schedule: 'Monday to Thursday · 4pm–7pm',
  ),
  _RoguesPresenterEntry(
    name: 'Robert',
    shows: 'The Switched Up Drive',
    schedule: 'Every Friday · 4pm–7pm',
  ),
  _RoguesPresenterEntry(
    name: 'Kyle',
    shows: 'The Switched Up Drive',
    schedule: 'Every Friday · 4pm–7pm',
  ),
  _RoguesPresenterEntry(
    name: 'Deon',
    shows: 'The Weekend Buzz',
    schedule: '7pm–10pm',
  ),
  _RoguesPresenterEntry(
    name: 'Henry',
    shows: 'The Weekend Wake Up',
    schedule: '7am–10am',
  ),
  _RoguesPresenterEntry(
    name: 'Craigie',
    shows: 'The Lawless Weekend',
    schedule: '10am–1pm',
  ),
  _RoguesPresenterEntry(
    name: 'Alusha',
    shows: 'The Lawless Weekend',
    schedule: '10am–1pm',
  ),
  _RoguesPresenterEntry(
    name: 'Ayanda',
    shows: 'The Lawless Weekend',
    schedule: '10am–1pm',
  ),
  _RoguesPresenterEntry(
    name: 'Chris',
    shows: 'The Weekend Riff',
    schedule: '1pm–4pm',
  ),
  _RoguesPresenterEntry(
    name: 'Cathy',
    shows: 'The Metal Manifesto',
    schedule: 'Saturday Nights · 10pm–1am',
  ),
];

class _RoguesHostsSection extends StatelessWidget {
  const _RoguesHostsSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Our hosts & shows',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: accent,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Live streaming 24 hours · studio shows weekdays 7am–7pm',
          style: theme.textTheme.bodySmall?.copyWith(
            color: theme.textTheme.bodySmall?.color?.withValues(alpha: 0.75),
          ),
        ),
        const SizedBox(height: 12),
        ..._kRoguesPresenters.map(
          (p) => _RoguesPresenterTile(entry: p, accent: accent),
        ),
      ],
    );
  }
}

class _RoguesPresenterTile extends StatelessWidget {
  const _RoguesPresenterTile({required this.entry, required this.accent});

  final _RoguesPresenterEntry entry;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              entry.name,
              style: theme.textTheme.titleSmall?.copyWith(
                fontWeight: FontWeight.w700,
                color: accent,
              ),
            ),
            const SizedBox(height: 4),
            Text(entry.shows, style: theme.textTheme.bodyMedium),
            const SizedBox(height: 2),
            Text(
              entry.schedule,
              style: theme.textTheme.bodySmall?.copyWith(
                color: theme.textTheme.bodySmall?.color?.withValues(alpha: 0.7),
              ),
            ),
          ],
        ),
      ),
    );
  }
}


class _RoguesEngagementSection extends StatelessWidget {
  const _RoguesEngagementSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Play your part',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: accent,
          ),
        ),
        const SizedBox(height: 8),
        _RoguesEngagementTile(
          title: 'Request a song',
          body: 'Celebrate yourself or someone special — ask our hosts to add a track.',
          icon: Icons.music_note,
          accent: accent,
        ),
        _RoguesEngagementTile(
          title: 'Competitions',
          body: 'Join the fun on air and online. Terms & conditions apply.',
          icon: Icons.emoji_events_outlined,
          accent: accent,
        ),
        _RoguesEngagementTile(
          title: 'Fundraising',
          body: 'Support community initiatives that listeners can get behind.',
          icon: Icons.volunteer_activism_outlined,
          accent: accent,
        ),
      ],
    );
  }
}

class _RoguesEngagementTile extends StatelessWidget {
  const _RoguesEngagementTile({
    required this.title,
    required this.body,
    required this.icon,
    required this.accent,
  });

  final String title;
  final String body;
  final IconData icon;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: accent, size: 22),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: theme.textTheme.titleSmall),
                Text(body, style: theme.textTheme.bodySmall),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// HOT 1027 — weekday shows snapshot
// ---------------------------------------------------------------------------

class _Hot1027ShowEntry {
  const _Hot1027ShowEntry({
    required this.title,
    required this.host,
    required this.time,
  });

  final String title;
  final String host;
  final String time;
}

const _kHot1027Shows = <_Hot1027ShowEntry>[
  _Hot1027ShowEntry(
    title: 'Breakfast with Martin Bester',
    host: 'Martin Bester',
    time: 'Weekdays · 6am–9am',
  ),
  _Hot1027ShowEntry(
    title: 'The Mid-Morning Fix',
    host: 'HOT 1027 team',
    time: 'Weekdays · 9am–12pm',
  ),
  _Hot1027ShowEntry(
    title: 'The Drive',
    host: 'HOT 1027 team',
    time: 'Weekdays · 3pm–7pm',
  ),
];

class _Hot1027ShowsSection extends StatelessWidget {
  const _Hot1027ShowsSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Shows',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: accent,
          ),
        ),
        const SizedBox(height: 12),
        ..._kHot1027Shows.map(
          (s) => ListTile(
            contentPadding: EdgeInsets.zero,
            title: Text(s.title, style: theme.textTheme.titleSmall),
            subtitle: Text('${s.host} · ${s.time}'),
          ),
        ),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// VOW FM — community blurb
// ---------------------------------------------------------------------------

class _VowFmCommunitySection extends StatelessWidget {
  const _VowFmCommunitySection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Voice of the community',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: accent,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'VOW FM connects listeners with local news, music, and community stories. '
          'Tune in live or reach out on WhatsApp.',
          style: theme.textTheme.bodyMedium,
        ),
      ],
    );
  }
}


// ---------------------------------------------------------------------------
// Mix 93.8 — dayparts + featured shows (zeno.fm / mix938.com)
// ---------------------------------------------------------------------------

class _Mix938DaypartEntry {
  const _Mix938DaypartEntry({required this.hours, required this.title});

  final String hours;
  final String title;
}

const _kMix938Dayparts = <_Mix938DaypartEntry>[
  _Mix938DaypartEntry(hours: '00–03', title: 'Mix Night Owl 2'),
  _Mix938DaypartEntry(hours: '03–06', title: 'Mix Dawn'),
  _Mix938DaypartEntry(hours: '06–09', title: 'Breakfast'),
  _Mix938DaypartEntry(hours: '09–12', title: 'Brunch'),
  _Mix938DaypartEntry(hours: '12–15', title: 'Afternoon'),
  _Mix938DaypartEntry(hours: '15–18', title: 'Drive'),
  _Mix938DaypartEntry(hours: '18–21', title: 'Mix Music Evenings'),
  _Mix938DaypartEntry(hours: '21–00', title: 'Mix Night Owl'),
];

const _kMix938FeaturedShows = <String>[
  'Mid Morning Mix',
  'Mix Timeless Tuesday Tunes',
  'SOULPHISTICATED SUNDAYS with Henceford',
  'THE ROCK ROADHOUSE WITH MICHAEL TYMVIOUS',
  'WATTS FOR BREAKFAST WITH DAVID WATTS',
  'THE WEEKEND SHOW WITH HUNTER AND MARIHESS',
  'THE MUSIC GURU MIX WITH SEAN BROKENSHA',
  'THE RIGHTEOUS SESSIONS WITH HARRY FISHER',
  "PABLO'S PLAYGROUND",
  'THE HANGOUT WITH KERRY ANNE ALLERSTON',
  'In the Fast Lane with Kornel',
  "LION'S ROAR WITH CLIFF HOCKING",
  'Martin Garrix Radio Show',
  'Matt Faulk & Friends Radio Show',
  'Symphony Radio Show with Timmy Trumpet',
  'THE MIXED STORY CORNER WITH WEZ',
  'The Global Underground',
  'The Power Mix With AL Your Pal',
  'The Sandton Times Hour With Alexander Leibner',
  'Thunderous Thursday Mix',
  'WEEKDAY EVENING LINEUP 10 MIXING IT UP WITH MAX SAVIOLI',
  'Wild Wednesday Evenings',
  "Let's talk about Sex with Lauri",
  'THAT SUNDAY SHOW WITH CAZ',
  "THEO'S AFTERNOON MIX",
];

class _Mix938DaypartsSection extends StatelessWidget {
  const _Mix938DaypartsSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Dayparts',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: accent,
          ),
        ),
        const SizedBox(height: 8),
        ..._kMix938Dayparts.map(
          (d) => Padding(
            padding: const EdgeInsets.only(bottom: 6),
            child: Row(
              children: [
                SizedBox(
                  width: 56,
                  child: Text(
                    d.hours,
                    style: theme.textTheme.labelMedium?.copyWith(color: accent),
                  ),
                ),
                Expanded(child: Text(d.title, style: theme.textTheme.bodyMedium)),
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),
        Text(
          'Featured shows & podcasts',
          style: theme.textTheme.titleSmall?.copyWith(
            fontWeight: FontWeight.w600,
            color: accent,
          ),
        ),
        const SizedBox(height: 8),
        ..._kMix938FeaturedShows.map(
          (show) => Padding(
            padding: const EdgeInsets.only(bottom: 4),
            child: Text('· $show', style: theme.textTheme.bodySmall),
          ),
        ),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// Mix 93.8 — about blurb (presenter-free format)
// ---------------------------------------------------------------------------

class _Mix938AboutSection extends StatelessWidget {
  const _Mix938AboutSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Real MIX, Real YOU',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: accent,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Mix 93.8 streams a curated mix of Pop, Rock, and R&B from the 1950s through '
          'to the 2010s — presenter-free, built on 18 years of FM heritage.',
          style: theme.textTheme.bodyMedium,
        ),
      ],
    );
  }
}


class _Fm919ShowEntry {
  const _Fm919ShowEntry({required this.title, required this.time});

  final String title;
  final String time;
}

const _kFm919Shows = <_Fm919ShowEntry>[
  _Fm919ShowEntry(title: 'Breakfast', time: 'Weekdays · 6am–9am'),
  _Fm919ShowEntry(title: 'Mid-Morning', time: 'Weekdays · 9am–12pm'),
  _Fm919ShowEntry(title: 'Afternoon Drive', time: 'Weekdays · 3pm–6pm'),
  _Fm919ShowEntry(title: 'Weekend Mix', time: 'Sat & Sun · 10am–2pm'),
  _Fm919ShowEntry(title: 'Late Night', time: 'Daily · 10pm–1am'),
];

class _Fm919ShowsSection extends StatelessWidget {
  const _Fm919ShowsSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          '919 FM shows',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: accent,
          ),
        ),
        const SizedBox(height: 12),
        ..._kFm919Shows.map(
          (s) => ListTile(
            contentPadding: EdgeInsets.zero,
            leading: Icon(Icons.radio, color: accent, size: 20),
            title: Text(s.title),
            subtitle: Text(s.time),
          ),
        ),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// FM919 news (RSS)
// ---------------------------------------------------------------------------

class _Fm919NewsSection extends StatefulWidget {
  const _Fm919NewsSection({required this.accent});

  final Color accent;

  @override
  State<_Fm919NewsSection> createState() => _Fm919NewsSectionState();
}

class _Fm919NewsSectionState extends State<_Fm919NewsSection> {
  List<_NewsItem> _items = const [];
  bool _loading = true;
  String? _error;

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
      final items = await _fetchRss(BrandConfig.fm919NewsRssUrl);
      if (mounted) {
        setState(() {
          _items = items;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _loading = false;
        });
      }
    }
  }

  Future<void> _open(_NewsItem item) async {
    final uri = Uri.tryParse(item.link);
    if (uri == null) return;
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
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
                  color: widget.accent,
                ),
              ),
            ),
            IconButton(
              icon: const Icon(Icons.refresh, size: 20),
              onPressed: _loading ? null : _load,
              tooltip: 'Refresh',
            ),
          ],
        ),
        if (_loading)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
          )
        else if (_error != null)
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Text(
              'Could not load news.',
              style: theme.textTheme.bodySmall,
            ),
          )
        else if (_items.isEmpty)
          Text('No headlines right now.', style: theme.textTheme.bodySmall)
        else
          ..._items.take(8).map(
                (item) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text(
                    item.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  subtitle: item.date != null ? Text(item.date!) : null,
                  trailing: const Icon(Icons.open_in_new, size: 16),
                  onTap: () => _open(item),
                ),
              ),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// Rise FM — today schedule + news
// ---------------------------------------------------------------------------

class _RiseScheduleEntry {
  const _RiseScheduleEntry({
    required this.time,
    required this.show,
    required this.host,
  });

  final String time;
  final String show;
  final String host;
}


const _kRiseWeekdaySchedule = <_RiseScheduleEntry>[
  _RiseScheduleEntry(time: '04:00', show: 'Pastor Sthembiso Ndlovu', host: 'Gospel message'),
  _RiseScheduleEntry(time: '06:00', show: 'RISE Breakfast', host: 'RISE team'),
  _RiseScheduleEntry(time: '09:00', show: 'Mid-Morning', host: 'RISE team'),
  _RiseScheduleEntry(time: '12:00', show: 'Lunch Break', host: 'RISE team'),
  _RiseScheduleEntry(time: '15:00', show: 'Drive Time', host: 'RISE team'),
  _RiseScheduleEntry(time: '19:00', show: 'Evening Mix', host: 'RISE team'),
];

const _kRiseSaturdaySchedule = <_RiseScheduleEntry>[
  _RiseScheduleEntry(time: '06:00', show: 'Weekend Breakfast', host: 'RISE team'),
  _RiseScheduleEntry(time: '10:00', show: 'Weekend Hits', host: 'RISE team'),
  _RiseScheduleEntry(time: '14:00', show: 'Afternoon Mix', host: 'RISE team'),
  _RiseScheduleEntry(time: '18:00', show: 'Saturday Night Live', host: 'RISE team'),
  _RiseScheduleEntry(time: '22:00', show: 'Late Session', host: 'RISE team'),
];

const _kRiseSundaySchedule = <_RiseScheduleEntry>[
  _RiseScheduleEntry(time: '06:00', show: 'Sunday Breakfast', host: 'RISE team'),
  _RiseScheduleEntry(time: '10:00', show: 'Sunday Praise', host: 'RISE team'),
  _RiseScheduleEntry(time: '14:00', show: 'Afternoon Chill', host: 'RISE team'),
  _RiseScheduleEntry(time: '18:00', show: 'Sunday Drive', host: 'RISE team'),
];

const _kRiseFeatureShows = <String>[
  'Ekugodleni',
  'Inside Africa',
  'RI60',
  'Office Scoop',
  'Behind Your Grades',
  'What Makes This Day Significant',
  'Pastor Sthembiso Ndlovu',
];

List<_RiseScheduleEntry> _riseScheduleForWeekday(int weekday) {
  if (weekday == DateTime.saturday) return _kRiseSaturdaySchedule;
  if (weekday == DateTime.sunday) return _kRiseSundaySchedule;
  return _kRiseWeekdaySchedule;
}


class _RiseFmTodaySection extends StatelessWidget {
  const _RiseFmTodaySection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final now = DateTime.now();
    final dayLabel = _weekdayLabel(now.weekday);
    final entries = _riseScheduleForWeekday(now.weekday);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Today on RISE FM',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: accent,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          dayLabel,
          style: theme.textTheme.bodySmall?.copyWith(
            color: theme.textTheme.bodySmall?.color?.withValues(alpha: 0.75),
          ),
        ),
        const SizedBox(height: 12),
        ...entries.map(
          (e) => _RiseScheduleRow(entry: e, accent: accent),
        ),
        const SizedBox(height: 12),
        Text(
          'Features & podcasts',
          style: theme.textTheme.titleSmall?.copyWith(
            fontWeight: FontWeight.w600,
            color: accent,
          ),
        ),
        const SizedBox(height: 8),
        ..._kRiseFeatureShows.map(
          (show) => Padding(
            padding: const EdgeInsets.only(bottom: 4),
            child: Text('· $show', style: theme.textTheme.bodySmall),
          ),
        ),
      ],
    );
  }

  String _weekdayLabel(int weekday) {
    const names = [
      'Monday',
      'Tuesday',
      'Wednesday',
      'Thursday',
      'Friday',
      'Saturday',
      'Sunday',
    ];
    return names[weekday - 1];
  }
}

class _RiseScheduleRow extends StatelessWidget {
  const _RiseScheduleRow({required this.entry, required this.accent});

  final _RiseScheduleEntry entry;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 52,
            child: Text(
              entry.time,
              style: theme.textTheme.labelLarge?.copyWith(
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
                  entry.show,
                  style: theme.textTheme.bodyMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  entry.host,
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: theme.textTheme.bodySmall?.color?.withValues(alpha: 0.7),
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

class _RiseFmNewsSection extends StatefulWidget {
  const _RiseFmNewsSection({required this.accent});

  final Color accent;

  @override
  State<_RiseFmNewsSection> createState() => _RiseFmNewsSectionState();
}

class _RiseFmNewsSectionState extends State<_RiseFmNewsSection> {
  List<_NewsItem> _items = const [];
  bool _loading = true;
  String? _error;

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
      final items = await _fetchRss(BrandConfig.riseFmNewsRssUrl);
      if (mounted) {
        setState(() {
          _items = items;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _loading = false;
        });
      }
    }
  }

  Future<void> _open(_NewsItem item) async {
    final uri = Uri.tryParse(item.link);
    if (uri == null) return;
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
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
                  color: widget.accent,
                ),
              ),
            ),
            IconButton(
              icon: const Icon(Icons.refresh, size: 20),
              onPressed: _loading ? null : _load,
              tooltip: 'Refresh',
            ),
          ],
        ),
        if (_loading)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
          )
        else if (_error != null)
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 8),
            child: Text(
              'Could not load news.',
              style: theme.textTheme.bodySmall,
            ),
          )
        else if (_items.isEmpty)
          Text('No headlines right now.', style: theme.textTheme.bodySmall)
        else
          ..._items.take(8).map(
                (item) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text(
                    item.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  subtitle: item.date != null ? Text(item.date!) : null,
                  trailing: const Icon(Icons.open_in_new, size: 16),
                  onTap: () => _open(item),
                ),
              ),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// Shared RSS helper + news model
// ---------------------------------------------------------------------------

class _NewsItem {
  const _NewsItem({required this.title, required this.link, this.date});

  final String title;
  final String link;
  final String? date;
}

Future<List<_NewsItem>> _fetchRss(String feedUrl) async {
  if (feedUrl.isEmpty) return const [];
  final response = await http
      .get(Uri.parse(feedUrl))
      .timeout(const Duration(seconds: 15));
  if (response.statusCode != 200) {
    throw Exception('HTTP ${response.statusCode}');
  }
  final doc = xml.XmlDocument.parse(response.body);
  final items = doc.findAllElements('item');
  return items.map((item) {
    final title = item.getElement('title')?.innerText.trim() ?? 'Untitled';
    final link = item.getElement('link')?.innerText.trim() ?? '';
    final pubDate = item.getElement('pubDate')?.innerText.trim();
    return _NewsItem(title: title, link: link, date: pubDate);
  }).where((i) => i.link.isNotEmpty).toList();
}

// ---------------------------------------------------------------------------
// Live player card
// ---------------------------------------------------------------------------

class _StationLivePlayerCard extends StatelessWidget {
  const _StationLivePlayerCard({
    required this.playing,
    required this.buffering,
    required this.error,
    required this.accent,
    required this.onToggle,
  });

  final bool playing;
  final bool buffering;
  final String? error;
  final Color accent;
  final VoidCallback onToggle;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Card(
      clipBehavior: Clip.antiAlias,
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
                if (buffering)
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
              ),
            ),
            if (BrandConfig.stationFrequencyLabel.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                BrandConfig.stationFrequencyLabel,
                style: theme.textTheme.bodyMedium?.copyWith(
                  color: theme.textTheme.bodyMedium?.color?.withValues(alpha: 0.75),
                ),
              ),
            ],
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: buffering ? null : onToggle,
                icon: Icon(playing ? Icons.stop_rounded : Icons.play_arrow_rounded),
                label: Text(playing ? 'Stop' : 'Play live'),
                style: FilledButton.styleFrom(
                  backgroundColor: accent,
                  foregroundColor: _onAccentForeground(accent),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                ),
              ),
            ),
            if (error != null) ...[
              const SizedBox(height: 8),
              Text(
                error!,
                style: theme.textTheme.bodySmall?.copyWith(color: theme.colorScheme.error),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Color _onAccentForeground(Color accent) {
    return accent.computeLuminance() > 0.5 ? Colors.black : Colors.white;
  }
}

// ---------------------------------------------------------------------------
// Rise FM stream quality picker
// ---------------------------------------------------------------------------

class _RiseFmQualityPicker extends StatelessWidget {
  const _RiseFmQualityPicker({
    required this.value,
    required this.accent,
    required this.onChanged,
  });

  final RiseStreamQuality value;
  final Color accent;
  final ValueChanged<RiseStreamQuality> onChanged;

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
        SegmentedButton<RiseStreamQuality>(
          segments: const [
            ButtonSegment(
              value: RiseStreamQuality.standard,
              label: Text('Standard'),
              icon: Icon(Icons.signal_cellular_alt, size: 16),
            ),
            ButtonSegment(
              value: RiseStreamQuality.high,
              label: Text('High'),
              icon: Icon(Icons.signal_cellular_alt_2_bar, size: 16),
            ),
          ],
          selected: {value},
          onSelectionChanged: (s) => onChanged(s.first),
        ),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// Station quick actions
// ---------------------------------------------------------------------------

class _StationAction {
  const _StationAction({
    required this.label,
    required this.icon,
    required this.url,
  });

  final String label;
  final IconData icon;
  final String url;
}

class _StationActionChip extends StatelessWidget {
  const _StationActionChip({
    required this.action,
    required this.accent,
    required this.onTap,
  });

  final _StationAction action;
  final Color accent;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return ActionChip(
      avatar: Icon(action.icon, size: 18, color: accent),
      label: Text(action.label),
      onPressed: onTap,
      side: BorderSide(color: accent.withValues(alpha: 0.35)),
    );
  }
}


class _RoguesPartnersSection extends StatelessWidget {
  const _RoguesPartnersSection({required this.accent});

  final Color accent;

  static const _partners = <String>[
  'AyobaAlli',
  'BIE Inspection Services',
  'Business Capital Group',
  'Churchill Plumbing',
  'Consolidated Auto',
  'Dukes Gold & Diamond Exchange',
  'Empirical',
  'Fourways Mall',
  'Pnet',
  'Shalkim',
  'Tic Tac',
  'Trinity Capital Holdings',
  'Turnkey Music & Multimedia',
  'Adapt Signage & Branding',
  'Bundle Media',
  'Open Fibre',
  'Dynamic IT',
  'Gold Reef City',
  'Hlasela Group',
  'Visual Audio',
  ];

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Partners & sponsors',
          style: theme.textTheme.titleMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: accent,
          ),
        ),
        const SizedBox(height: 8),
        Wrap(
          spacing: 6,
          runSpacing: 6,
          children: _partners
              .map(
                (p) => Chip(
                  label: Text(p, style: theme.textTheme.labelSmall),
                  visualDensity: VisualDensity.compact,
                  side: BorderSide(color: accent.withValues(alpha: 0.25)),
                ),
              )
              .toList(),
        ),
      ],
    );
  }
}

/// Rogues flavor keeps the historical screen name alias for imports/tests.
typedef RoguesRadioTabScreen = StationRadioTabScreen;
