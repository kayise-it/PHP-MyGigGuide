import 'package:flutter/cupertino.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:url_launcher/url_launcher.dart';

import '../brand_config.dart';
import '../models/app_icon_data.dart';
import '../providers/rogues_radio_player_provider.dart';
import '../services/fm919_site_repository.dart';
import '../services/risefm_site_repository.dart';
import '../screens/station_hosts_screen.dart';
import '../widgets/brand_logo_header.dart';
import '../widgets/zeno_now_playing_banner.dart';

/// Radio tab for station-branded flavors (Rogues, 919 FM, HOT 1027, VOW FM,
/// Rise FM, Mix 93.8). Vanilla My Gig Guide uses the Home On Air strip.
class StationRadioTabScreen extends ConsumerStatefulWidget {
  const StationRadioTabScreen({super.key});

  @override
  ConsumerState<StationRadioTabScreen> createState() =>
      _StationRadioTabScreenState();
}

class _StationRadioTabScreenState extends ConsumerState<StationRadioTabScreen> {
  static const _riseQualityPrefKey = 'rise_fm_stream_quality';
  String _riseStreamQuality = 'medium';

  @override
  void initState() {
    super.initState();
    _loadRiseStreamQuality();
  }

  Future<void> _loadRiseStreamQuality() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_riseQualityPrefKey);
    if (saved == 'medium' || saved == 'high') {
      if (mounted) setState(() => _riseStreamQuality = saved!);
    }
  }

  Future<void> _persistRiseStreamQuality(String quality) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_riseQualityPrefKey, quality);
  }

  Future<void> _onRiseQualityChanged(String quality) async {
    setState(() => _riseStreamQuality = quality);
    await _persistRiseStreamQuality(quality);
    await ref.read(roguesRadioPlayerProvider.notifier).setRiseStreamQuality(quality);
  }

  Future<void> _openUrl(String url) async {
    final uri = Uri.tryParse(url);
    if (uri == null) return;
    final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!launched && mounted) {
      _showMessage('Could not open link');
    }
  }

  void _showMessage(String message) {
    showCupertinoDialog<void>(
      context: context,
      builder: (ctx) => CupertinoAlertDialog(
        content: Text(message),
        actions: [
          CupertinoDialogAction(
            isDefaultAction: true,
            onPressed: () => Navigator.of(ctx).pop(),
            child: const Text('OK'),
          ),
        ],
      ),
    );
  }

  void _openHostsScreen() {
    Navigator.of(context).push(
      CupertinoPageRoute<void>(builder: (_) => const StationHostsScreen()),
    );
  }

  List<_StationAction> _buildActions() {
    final actions = <_StationAction>[];

    if (BrandConfig.stationWebsiteUrl.isNotEmpty) {
      actions.add(
        _StationAction(
          label: 'Website',
          icon: CupertinoIcons.globe,
          url: BrandConfig.stationWebsiteUrl,
        ),
      );
    }

    if (BrandConfig.isRogues) {
      actions.addAll(const [
        _StationAction(
          label: 'Request a song',
          icon: CupertinoIcons.music_note_2,
          url: 'https://roguesonradio.co.za/#contact',
        ),
        _StationAction(
          label: 'Competitions',
          icon: CupertinoIcons.gift,
          url: 'https://roguesonradio.co.za/',
        ),
      ]);
    }

    if (BrandConfig.isFm919 && BrandConfig.fm919FacebookUrl.isNotEmpty) {
      actions.add(
        _StationAction(
          label: 'Facebook',
          faIcon: FontAwesomeIcons.facebook,
          url: BrandConfig.fm919FacebookUrl,
        ),
      );
    }

    if (BrandConfig.isHot1027 && BrandConfig.hot1027InstagramUrl.isNotEmpty) {
      actions.add(
        _StationAction(
          label: 'Instagram',
          faIcon: FontAwesomeIcons.instagram,
          url: BrandConfig.hot1027InstagramUrl,
        ),
      );
    }

    if (BrandConfig.isVowFm && BrandConfig.vowFmWhatsAppUrl.isNotEmpty) {
      actions.add(
        _StationAction(
          label: 'WhatsApp',
          faIcon: FontAwesomeIcons.whatsapp,
          url: BrandConfig.vowFmWhatsAppUrl,
        ),
      );
    }

    if (BrandConfig.isRiseFm) {
      actions.addAll(const [
        _StationAction(
          label: 'Competitions',
          icon: CupertinoIcons.gift,
          url: 'https://risefm.co.za/competitions/',
        ),
        _StationAction(
          label: 'RISE Rewind',
          icon: CupertinoIcons.time,
          url: 'https://risefm.co.za/riserewind/',
        ),
      ]);
    }

    if (BrandConfig.isMix938) {
      if (BrandConfig.mix938InstagramUrl.isNotEmpty) {
        actions.add(
          _StationAction(
            label: 'Instagram',
            faIcon: FontAwesomeIcons.instagram,
            url: BrandConfig.mix938InstagramUrl,
          ),
        );
      }
      actions.add(
        _StationAction(
          label: 'Open on Zeno',
          icon: CupertinoIcons.arrow_up_right_square,
          url: BrandConfig.mix938ZenoWebUrl,
        ),
      );
      if (BrandConfig.mix938WhatsAppUrl.isNotEmpty) {
        actions.add(
          _StationAction(
            label: 'WhatsApp',
            faIcon: FontAwesomeIcons.whatsapp,
            url: BrandConfig.mix938WhatsAppUrl,
          ),
        );
      }
    }

    return actions;
  }

  @override
  Widget build(BuildContext context) {
    final accent = BrandConfig.stationAccent;
    final actions = _buildActions();

    return CupertinoPageScaffold(
      backgroundColor: CupertinoColors.black,
      child: SafeArea(
        bottom: false,
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
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 28),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  _StationLivePlayerCard(accent: accent),
                  const SizedBox(height: 8),
                  if (BrandConfig.isMix938) ...[
                    ZenoNowPlayingBanner(mountId: BrandConfig.mix938ZenoMountId),
                    const SizedBox(height: 8),
                  ],
                  if (BrandConfig.isRiseFm) ...[
                    _RiseFmQualityPicker(
                      value: _riseStreamQuality,
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
                            (action) => _StationActionChip(
                              action: action,
                              accent: accent,
                              onTap: () => _openUrl(action.url),
                            ),
                          )
                          .toList(),
                    ),
                    const SizedBox(height: 16),
                  ],
                  if (BrandConfig.isRogues) ...[
                    const SizedBox(height: 8),
                    _RoguesHostsSection(accent: accent, onViewAll: _openHostsScreen),
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
    schedule: 'Weekends · 7am–10am',
  ),
  _RoguesPresenterEntry(
    name: 'Craigie',
    shows: 'The Lawless Weekend',
    schedule: 'Weekends · 10am–1pm',
  ),
  _RoguesPresenterEntry(
    name: 'Alusha',
    shows: 'The Lawless Weekend',
    schedule: 'Weekends · 10am–1pm',
  ),
  _RoguesPresenterEntry(
    name: 'Ayanda',
    shows: 'The Lawless Weekend',
    schedule: 'Weekends · 10am–1pm',
  ),
  _RoguesPresenterEntry(
    name: 'Chris',
    shows: 'The Weekend Riff',
    schedule: 'Weekends · 1pm–4pm',
  ),
  _RoguesPresenterEntry(
    name: 'Cathy',
    shows: 'The Metal Manifesto',
    schedule: 'Saturday Nights · 10pm–1am',
  ),
];

class _RoguesHostsSection extends StatelessWidget {
  const _RoguesHostsSection({required this.accent, required this.onViewAll});

  final Color accent;
  final VoidCallback onViewAll;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                'Our hosts & shows',
                style: TextStyle(
                  color: accent,
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
            CupertinoButton(
              padding: EdgeInsets.zero,
              onPressed: onViewAll,
              child: Text(
                'View all',
                style: TextStyle(color: accent, fontSize: 14),
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        const Text(
          'Live streaming 24 hours · studio shows weekdays 7am–7pm',
          style: TextStyle(color: CupertinoColors.systemGrey, fontSize: 13),
        ),
        const SizedBox(height: 12),
        ..._kRoguesPresenters.map(
          (entry) => _RoguesPresenterTile(entry: entry, accent: accent),
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
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFF12121A),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            entry.name,
            style: TextStyle(
              color: accent,
              fontSize: 15,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            entry.shows,
            style: const TextStyle(color: CupertinoColors.white, fontSize: 14),
          ),
          const SizedBox(height: 2),
          Text(
            entry.schedule,
            style: const TextStyle(color: CupertinoColors.systemGrey, fontSize: 12),
          ),
        ],
      ),
    );
  }
}

class _RoguesEngagementSection extends StatelessWidget {
  const _RoguesEngagementSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Play your part',
          style: TextStyle(
            color: accent,
            fontSize: 18,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 8),
        _RoguesEngagementRow(
          accent: accent,
          icon: CupertinoIcons.music_note_2,
          title: 'Request a song',
          body: 'Celebrate yourself or someone special — ask our hosts to add a track.',
        ),
        _RoguesEngagementRow(
          accent: accent,
          icon: CupertinoIcons.gift,
          title: 'Competitions',
          body: 'Join the fun on air and online. Terms & conditions apply.',
        ),
        _RoguesEngagementRow(
          accent: accent,
          icon: CupertinoIcons.heart,
          title: 'Fundraising',
          body: 'Support community initiatives that listeners can get behind.',
        ),
      ],
    );
  }
}

class _RoguesEngagementRow extends StatelessWidget {
  const _RoguesEngagementRow({
    required this.accent,
    required this.icon,
    required this.title,
    required this.body,
  });

  final Color accent;
  final IconData icon;
  final String title;
  final String body;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: accent, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: CupertinoColors.white,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  body,
                  style: const TextStyle(
                    color: CupertinoColors.systemGrey,
                    fontSize: 12,
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
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Partners & sponsors',
          style: TextStyle(
            color: accent,
            fontSize: 18,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 8),
        Wrap(
          spacing: 6,
          runSpacing: 6,
          children: _partners
              .map(
                (name) => Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: accent.withValues(alpha: 0.25)),
                  ),
                  child: Text(
                    name,
                    style: const TextStyle(
                      color: CupertinoColors.systemGrey,
                      fontSize: 11,
                    ),
                  ),
                ),
              )
              .toList(),
        ),
      ],
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
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Shows',
          style: TextStyle(
            color: accent,
            fontSize: 18,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 12),
        ..._kHot1027Shows.map(
          (show) => Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(
                  CupertinoIcons.dot_radiowaves_left_right,
                  color: accent,
                  size: 18,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        show.title,
                        style: const TextStyle(
                          color: CupertinoColors.white,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      Text(
                        '${show.host} · ${show.time}',
                        style: const TextStyle(
                          color: CupertinoColors.systemGrey,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
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
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Voice of the community',
          style: TextStyle(
            color: accent,
            fontSize: 18,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'VOW FM connects listeners with local news, music, and community stories. '
          'Tune in live or reach out on WhatsApp.',
          style: TextStyle(color: CupertinoColors.white, fontSize: 14),
        ),
      ],
    );
  }
}

// ---------------------------------------------------------------------------
// Mix 93.8 — dayparts + featured shows
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
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Dayparts',
          style: TextStyle(
            color: accent,
            fontSize: 18,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 8),
        ..._kMix938Dayparts.map(
          (entry) => Padding(
            padding: const EdgeInsets.only(bottom: 6),
            child: Row(
              children: [
                SizedBox(
                  width: 56,
                  child: Text(
                    entry.hours,
                    style: TextStyle(
                      color: accent,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                Expanded(
                  child: Text(
                    entry.title,
                    style: const TextStyle(
                      color: CupertinoColors.white,
                      fontSize: 13,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),
        Text(
          'Featured shows & podcasts',
          style: TextStyle(
            color: accent,
            fontSize: 15,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        ..._kMix938FeaturedShows.map(
          (show) => Padding(
            padding: const EdgeInsets.only(bottom: 4),
            child: Text(
              '· $show',
              style: const TextStyle(
                color: CupertinoColors.systemGrey,
                fontSize: 12,
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _Mix938AboutSection extends StatelessWidget {
  const _Mix938AboutSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Real MIX, Real YOU',
          style: TextStyle(
            color: accent,
            fontSize: 18,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Mix 93.8 streams a curated mix of Pop, Rock, and R&B from the 1950s through '
          'to the 2010s — presenter-free, built on 18 years of FM heritage.',
          style: TextStyle(color: CupertinoColors.white, fontSize: 14),
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
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          '919 FM shows',
          style: TextStyle(
            color: accent,
            fontSize: 18,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 12),
        ..._kFm919Shows.map(
          (show) => Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: Row(
              children: [
                Icon(
                  CupertinoIcons.antenna_radiowaves_left_right,
                  color: accent,
                  size: 18,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        show.title,
                        style: const TextStyle(
                          color: CupertinoColors.white,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      Text(
                        show.time,
                        style: const TextStyle(
                          color: CupertinoColors.systemGrey,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _Fm919NewsSection extends ConsumerWidget {
  const _Fm919NewsSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final news = ref.watch(fm919NewsProvider);
    return news.when(
      loading: () => _Fm919NewsBody(accent: accent, items: const [], loading: true),
      error: (_, __) => _Fm919NewsBody(accent: accent, items: const [], error: true),
      data: (items) => _Fm919NewsBody(accent: accent, items: items),
    );
  }
}

class _Fm919NewsBody extends StatelessWidget {
  const _Fm919NewsBody({
    required this.accent,
    required this.items,
    this.loading = false,
    this.error = false,
  });

  final Color accent;
  final List<Fm919NewsItem> items;
  final bool loading;
  final bool error;

  Future<void> _open(Fm919NewsItem item) async {
    final uri = Uri.tryParse(item.url);
    if (uri == null) return;
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                '919 FM news',
                style: TextStyle(
                  color: accent,
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
            if (loading) const CupertinoActivityIndicator(radius: 9),
          ],
        ),
        const SizedBox(height: 8),
        if (error)
          const Text(
            'Could not load news.',
            style: TextStyle(color: CupertinoColors.systemGrey, fontSize: 13),
          ),
        if (!loading && !error && items.isEmpty)
          const Text(
            'No headlines right now.',
            style: TextStyle(color: CupertinoColors.systemGrey, fontSize: 13),
          ),
        ...items.take(8).map(
              (item) => GestureDetector(
                onTap: () => _open(item),
                child: Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              item.title,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: CupertinoColors.white,
                                fontSize: 14,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            if (item.publishedLabel != null)
                              Text(
                                item.publishedLabel!,
                                style: const TextStyle(
                                  color: CupertinoColors.systemGrey,
                                  fontSize: 11,
                                ),
                              ),
                          ],
                        ),
                      ),
                      const Icon(
                        CupertinoIcons.arrow_up_right,
                        size: 14,
                        color: CupertinoColors.systemGrey,
                      ),
                    ],
                  ),
                ),
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

class _RiseFmTodaySection extends ConsumerWidget {
  const _RiseFmTodaySection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final schedule = ref.watch(riseFmTodayScheduleProvider);
    return schedule.when(
      loading: () => _RiseFmTodayBody(
        accent: accent,
        entries: _riseScheduleForWeekday(DateTime.now().weekday),
        loading: true,
      ),
      error: (_, __) => _RiseFmTodayBody(
        accent: accent,
        entries: _riseScheduleForWeekday(DateTime.now().weekday),
      ),
      data: (slots) {
        final entries = slots.isEmpty
            ? _riseScheduleForWeekday(DateTime.now().weekday)
            : slots
                .map(
                  (slot) => _RiseScheduleEntry(
                    time: slot.timeRange,
                    show: slot.show,
                    host: slot.presenter ?? 'RISE team',
                  ),
                )
                .toList();
        return _RiseFmTodayBody(accent: accent, entries: entries);
      },
    );
  }
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

class _RiseFmTodayBody extends StatelessWidget {
  const _RiseFmTodayBody({
    required this.accent,
    required this.entries,
    this.loading = false,
  });

  final Color accent;
  final List<_RiseScheduleEntry> entries;
  final bool loading;

  @override
  Widget build(BuildContext context) {
    final day = _weekdayLabel(DateTime.now().weekday);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                'Today on RISE FM',
                style: TextStyle(
                  color: accent,
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
            if (loading) const CupertinoActivityIndicator(radius: 9),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          day,
          style: const TextStyle(color: CupertinoColors.systemGrey, fontSize: 13),
        ),
        const SizedBox(height: 12),
        ...entries.map((entry) => _RiseScheduleRow(entry: entry, accent: accent)),
        const SizedBox(height: 12),
        Text(
          'Features & podcasts',
          style: TextStyle(
            color: accent,
            fontSize: 15,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        ..._kRiseFeatureShows.map(
          (show) => Padding(
            padding: const EdgeInsets.only(bottom: 4),
            child: Text(
              '· $show',
              style: const TextStyle(
                color: CupertinoColors.systemGrey,
                fontSize: 12,
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _RiseScheduleRow extends StatelessWidget {
  const _RiseScheduleRow({required this.entry, required this.accent});

  final _RiseScheduleEntry entry;
  final Color accent;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 52,
            child: Text(
              entry.time,
              style: TextStyle(
                color: accent,
                fontSize: 12,
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
                  style: const TextStyle(
                    color: CupertinoColors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                Text(
                  entry.host,
                  style: const TextStyle(
                    color: CupertinoColors.systemGrey,
                    fontSize: 12,
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

class _RiseFmNewsSection extends ConsumerWidget {
  const _RiseFmNewsSection({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final news = ref.watch(riseFmNewsProvider);
    return news.when(
      loading: () => _RiseFmNewsBody(accent: accent, items: const [], loading: true),
      error: (_, __) => _RiseFmNewsBody(accent: accent, items: const [], error: true),
      data: (items) => _RiseFmNewsBody(accent: accent, items: items),
    );
  }
}

class _RiseFmNewsBody extends StatelessWidget {
  const _RiseFmNewsBody({
    required this.accent,
    required this.items,
    this.loading = false,
    this.error = false,
  });

  final Color accent;
  final List<RiseFmNewsItem> items;
  final bool loading;
  final bool error;

  Future<void> _open(RiseFmNewsItem item) async {
    final uri = Uri.tryParse(item.url);
    if (uri == null) return;
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                'Mpumalanga news',
                style: TextStyle(
                  color: accent,
                  fontSize: 18,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
            if (loading) const CupertinoActivityIndicator(radius: 9),
          ],
        ),
        const SizedBox(height: 8),
        if (error)
          const Text(
            'Could not load news.',
            style: TextStyle(color: CupertinoColors.systemGrey, fontSize: 13),
          ),
        if (!loading && !error && items.isEmpty)
          const Text(
            'No headlines right now.',
            style: TextStyle(color: CupertinoColors.systemGrey, fontSize: 13),
          ),
        ...items.take(8).map(
              (item) => GestureDetector(
                onTap: () => _open(item),
                child: Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              item.title,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: CupertinoColors.white,
                                fontSize: 14,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            if (item.publishedLabel != null)
                              Text(
                                item.publishedLabel!,
                                style: const TextStyle(
                                  color: CupertinoColors.systemGrey,
                                  fontSize: 11,
                                ),
                              ),
                          ],
                        ),
                      ),
                      const Icon(
                        CupertinoIcons.arrow_up_right,
                        size: 14,
                        color: CupertinoColors.systemGrey,
                      ),
                    ],
                  ),
                ),
              ),
            ),
      ],
    );
  }
}

class _StationLivePlayerCard extends ConsumerWidget {
  const _StationLivePlayerCard({required this.accent});

  final Color accent;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final ui = ref.watch(roguesRadioUiStateProvider);
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF12121A),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 10,
                height: 10,
                decoration: BoxDecoration(
                  color: ui.isPlaying
                      ? CupertinoColors.systemRed
                      : CupertinoColors.systemGrey,
                  shape: BoxShape.circle,
                ),
              ),
              const SizedBox(width: 8),
              Text(
                ui.isPlaying ? 'ON AIR' : 'LIVE STREAM',
                style: TextStyle(
                  color: ui.isPlaying ? CupertinoColors.systemRed : accent,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1.1,
                  fontSize: 12,
                ),
              ),
              const Spacer(),
              if (ui.isLoading) const CupertinoActivityIndicator(radius: 9),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            BrandConfig.stationName,
            style: const TextStyle(
              color: CupertinoColors.white,
              fontSize: 20,
              fontWeight: FontWeight.w700,
            ),
          ),
          if (BrandConfig.stationFrequencyLabel.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              BrandConfig.stationFrequencyLabel,
              style: const TextStyle(
                color: CupertinoColors.systemGrey,
                fontSize: 14,
              ),
            ),
          ],
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: CupertinoButton.filled(
              color: accent,
              padding: const EdgeInsets.symmetric(vertical: 14),
              onPressed: ui.isLoading
                  ? null
                  : () => ref.read(roguesRadioPlayerProvider).toggle(),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    ui.isPlaying
                        ? CupertinoIcons.stop_fill
                        : CupertinoIcons.play_fill,
                    size: 18,
                  ),
                  const SizedBox(width: 8),
                  Text(ui.isPlaying ? 'Stop' : 'Play live'),
                ],
              ),
            ),
          ),
          if (ui.errorMessage != null) ...[
            const SizedBox(height: 8),
            Text(
              ui.errorMessage!,
              style: const TextStyle(
                color: CupertinoColors.systemRed,
                fontSize: 12,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _RiseFmQualityPicker extends StatelessWidget {
  const _RiseFmQualityPicker({
    required this.value,
    required this.accent,
    required this.onChanged,
  });

  final String value;
  final Color accent;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Stream quality',
          style: TextStyle(
            color: accent,
            fontSize: 14,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        CupertinoSlidingSegmentedControl<String>(
          groupValue: value,
          onValueChanged: (next) {
            if (next != null) onChanged(next);
          },
          children: const {
            'medium': Padding(
              padding: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              child: Text('Standard'),
            ),
            'high': Padding(
              padding: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              child: Text('High'),
            ),
          },
        ),
      ],
    );
  }
}

class _StationAction {
  const _StationAction({
    required this.label,
    required this.url,
    this.icon,
    this.faIcon,
  });

  final String label;
  final String url;
  final IconData? icon;
  final IconData? faIcon;
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
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: accent.withValues(alpha: 0.35)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (action.faIcon != null)
              FaIcon(action.faIcon, size: 14, color: accent)
            else
              Icon(action.icon, size: 16, color: accent),
            const SizedBox(width: 6),
            Text(
              action.label,
              style: const TextStyle(
                color: CupertinoColors.white,
                fontSize: 13,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Rogues flavor keeps the historical screen name alias for imports/tests.
typedef RoguesRadioTabScreen = StationRadioTabScreen;
