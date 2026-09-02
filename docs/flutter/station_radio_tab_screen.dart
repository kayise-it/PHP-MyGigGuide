import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:font_awesome_flutter/font_awesome_flutter.dart' show FaIcon, FaIconData, FontAwesomeIcons;

import '../app_icon_data.dart';
import '../brand_config.dart';
import '../data/risefm_hosts_catalog.dart';
import '../data/station_host_entry.dart';
import '../models/hot1027_schedule.dart';
import '../providers/rogues_radio_player_provider.dart';
import '../services/fm919_site_parser.dart';
import '../services/fm919_site_repository.dart';
import '../services/hot1027_radio_repository.dart';
import '../services/risefm_site_parser.dart';
import '../services/risefm_site_repository.dart';
import '../services/rogues_radio_player.dart';
import '../widgets/app_icon.dart';
import '../widgets/brand_logo_header.dart';
import '../widgets/zeno_now_playing_banner.dart';
import '../widgets/station_poll_section.dart';
import 'hot1027_hosts_screen.dart';
import 'rise_demo_poll_screen.dart';
import 'rogues_demo_poll_screen.dart';
import 'site_web_tab_screen.dart';
import 'station_hosts_screen.dart';
import 'station_in_app_poll_screen.dart';

/// Station flavor hub — native live stream, site, hosts, WhatsApp, surveys, feedback.
/// Used for Rogues on Radio and 919 FM (4th bottom tab replaces Add).
class StationRadioTabScreen extends ConsumerStatefulWidget {
  const StationRadioTabScreen({super.key});

  @override
  ConsumerState<StationRadioTabScreen> createState() => _StationRadioTabScreenState();
}

class _StationRadioTabScreenState extends ConsumerState<StationRadioTabScreen> {
  final _fm919Repo = const Fm919SiteRepository();
  final _riseFmRepo = const RiseFmSiteRepository();
  final _hot1027Repo = Hot1027RadioRepository();
  Fm919NewsDigest? _news;
  bool _newsLoading = false;
  RiseFmDaySchedule? _riseTodaySchedule;
  bool _riseScheduleLoading = false;
  RiseFmNewsDigest? _riseNews;
  bool _riseNewsLoading = false;
  Hot1027DaySchedule? _hotTodaySchedule;
  Hot1027Broadcast? _hotBroadcast;
  bool _hotScheduleLoading = false;
  String _riseStreamQuality = 'medium';
  String _hot1027StreamQuality = 'medium';

  static const _kRiseFmQualityKey = 'risefm_stream_quality';
  static const _kHot1027QualityKey = 'hot1027_stream_quality';

  @override
  void initState() {
    super.initState();
    if (BrandConfig.isFm919) _loadNews();
    if (BrandConfig.isRiseFm) {
      _loadRiseSchedule();
      _loadRiseNews();
      _loadRiseStreamQuality();
    }
    if (BrandConfig.isHot1027) {
      _loadHot1027StreamQuality();
      _loadHot1027Schedule();
    }
  }

  Future<void> _loadRiseStreamQuality() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_kRiseFmQualityKey) ?? 'medium';
    if (!mounted) return;
    setState(() => _riseStreamQuality = saved);
  }

  Future<void> _onRiseQualityChanged(String quality) async {
    if (quality == _riseStreamQuality) return;
    setState(() => _riseStreamQuality = quality);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kRiseFmQualityKey, quality);
    final newUrl = BrandConfig.riseFmStreamUrlForQuality(quality);
    await ref.read(roguesRadioPlayerProvider).setStreamUrl(newUrl);
  }

  Future<void> _loadHot1027StreamQuality() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_kHot1027QualityKey) ?? 'medium';
    if (!mounted) return;
    setState(() => _hot1027StreamQuality = saved);
  }

  Future<void> _onHot1027QualityChanged(String quality) async {
    if (quality == _hot1027StreamQuality) return;
    setState(() => _hot1027StreamQuality = quality);
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kHot1027QualityKey, quality);
    final newUrl = BrandConfig.hot1027StreamUrlForQuality(quality);
    await ref.read(roguesRadioPlayerProvider).setStreamUrl(newUrl);
  }

  Future<void> _loadNews() async {
    setState(() => _newsLoading = true);
    try {
      final digest = await _fm919Repo.fetchLatestNews();
      if (!mounted) return;
      setState(() {
        _news = digest;
        _newsLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _newsLoading = false);
    }
  }

  Future<void> _loadRiseSchedule() async {
    setState(() => _riseScheduleLoading = true);
    try {
      final today = await _riseFmRepo.fetchTodaySchedule();
      if (!mounted) return;
      setState(() {
        _riseTodaySchedule = today;
        _riseScheduleLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _riseScheduleLoading = false);
    }
  }

  Future<void> _loadHot1027Schedule() async {
    setState(() => _hotScheduleLoading = true);
    try {
      final today = await _hot1027Repo.fetchTodaySchedule();
      final broadcast = await _hot1027Repo.fetchBroadcast();
      if (!mounted) return;
      setState(() {
        _hotTodaySchedule = today;
        _hotBroadcast = broadcast;
        _hotScheduleLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _hotScheduleLoading = false);
    }
  }

  Future<void> _loadRiseNews() async {
    setState(() => _riseNewsLoading = true);
    try {
      final digest = await _riseFmRepo.fetchLatestNews();
      if (!mounted) return;
      setState(() {
        _riseNews = digest;
        _riseNewsLoading = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _riseNewsLoading = false);
    }
  }

  Future<void> _onRefresh() async {
    if (BrandConfig.isFm919) {
      await _loadNews();
    } else if (BrandConfig.isRiseFm) {
      await Future.wait([_loadRiseSchedule(), _loadRiseNews()]);
    } else if (BrandConfig.isHot1027) {
      await _loadHot1027Schedule();
    }
  }

  Future<void> _openUri(BuildContext context, Uri? uri, {required String failMessage}) async {
    if (uri == null) {
      _snack(context, failMessage);
      return;
    }
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      if (!context.mounted) return;
      _snack(context, failMessage);
    }
  }

  void _snack(BuildContext context, String message) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> _toggleStream(WidgetRef ref, BuildContext context) async {
    if (!RoguesRadioPlayer.isSupported) {
      _snack(context, 'Live stream is not available on this device.');
      return;
    }
    await ref.read(roguesRadioPlayerProvider).toggle();
    final state = ref.read(roguesRadioPlayerProvider).uiState;
    if (!context.mounted) return;
    if (state == RoguesRadioUiState.error) {
      final message = ref.read(roguesRadioPlayerProvider).errorMessage;
      _snack(context, message ?? 'Could not play live stream.');
    }
  }

  Future<void> _openWebsite(BuildContext context) async {
    final raw = BrandConfig.brandPublicWebsiteUrl.trim();
    if (raw.isEmpty) {
      _snack(context, 'No station website configured.');
      return;
    }
    await _openUri(context, Uri.parse(raw), failMessage: 'Could not open website.');
  }

  Future<void> _openWhatsApp(BuildContext context) async {
    await _openUri(
      context,
      BrandConfig.stationWhatsAppUri,
      failMessage: 'Could not open WhatsApp.',
    );
  }

  Future<void> _openCompetition(BuildContext context) async {
    final raw = BrandConfig.stationCompetitionUrl.trim();
    if (raw.isNotEmpty) {
      await _openUri(context, Uri.parse(raw), failMessage: 'Could not open competition page.');
      return;
    }
    final fallback = BrandConfig.brandPublicWebsiteUrl.trim();
    if (fallback.isNotEmpty) {
      await _openUri(
        context,
        Uri.parse(fallback),
        failMessage: 'Could not open website.',
      );
      return;
    }
    _snack(context, 'Competition link not configured.');
  }

  Future<void> _openPoll(BuildContext context) async {
    if (BrandConfig.stationPollContext.trim().isNotEmpty) {
      await Navigator.of(context).push<void>(
        MaterialPageRoute<void>(builder: (_) => const StationInAppPollScreen()),
      );
      return;
    }
    final surveyUrl = BrandConfig.stationSurveyUrl.trim();
    if (surveyUrl.isNotEmpty) {
      if (BrandConfig.isFm919 && BrandConfig.isInAppHost(Uri.parse(surveyUrl))) {
        await _openTenantPage(context, url: surveyUrl, title: 'Listener poll');
        return;
      }
      await _openUri(context, Uri.parse(surveyUrl), failMessage: 'Could not open survey.');
      return;
    }
    if (BrandConfig.isRogues) {
      Navigator.of(context).push<void>(
        MaterialPageRoute<void>(builder: (_) => const RoguesDemoPollScreen()),
      );
      return;
    }
    if (BrandConfig.isRiseFm) {
      Navigator.of(context).push<void>(
        MaterialPageRoute<void>(builder: (_) => const RiseDemoPollScreen()),
      );
      return;
    }
    _snack(context, 'Listener poll link not configured yet for ${BrandConfig.appTitle}.');
  }

  Future<void> _openRequest(BuildContext context) async {
    final raw = BrandConfig.stationRequestPageUrl.trim();
    if (raw.isEmpty) {
      _snack(context, 'Request page not configured yet.');
      return;
    }
    if (BrandConfig.isInAppHost(Uri.parse(raw))) {
      await _openTenantPage(context, url: raw, title: 'Send a request');
      return;
    }
    await _openUri(context, Uri.parse(raw), failMessage: 'Could not open request page.');
  }

  Future<void> _openTenantPage(
    BuildContext context, {
    required String url,
    required String title,
  }) async {
    await Navigator.of(context).push<void>(
      MaterialPageRoute<void>(
        builder: (_) => SiteWebTabScreen(title: title, initialUrl: url),
      ),
    );
  }

  Future<void> _openFeedback(BuildContext context) async {
    await _openUri(
      context,
      Uri.parse(BrandConfig.stationFeedbackUrl),
      failMessage: 'Could not open feedback form.',
    );
  }

  void _openHosts(BuildContext context) {
    if (BrandConfig.isHot1027) {
      Navigator.of(context).push<void>(
        MaterialPageRoute<void>(builder: (_) => const Hot1027HostsScreen()),
      );
      return;
    }
    Navigator.of(context).push<void>(
      MaterialPageRoute<void>(builder: (_) => const StationHostsScreen()),
    );
  }

  Future<void> _openPodcast(BuildContext context) async {
    final url = BrandConfig.stationPodcastUrl.trim();
    if (url.isEmpty) {
      _snack(context, 'Podcast link not configured.');
      return;
    }
    await _openUri(context, Uri.parse(url), failMessage: 'Could not open podcast page.');
  }

  Future<void> _callStudio(BuildContext context) async {
    final digits = BrandConfig.stationStudioPhoneE164.replaceAll(RegExp(r'\D'), '');
    if (digits.isEmpty) {
      _snack(context, 'Studio number not configured.');
      return;
    }
    await _openUri(context, Uri.parse('tel:+$digits'), failMessage: 'Could not open phone dialler.');
  }

  Future<void> _openSocial(BuildContext context, String url) async {
    await _openUri(context, Uri.parse(url), failMessage: 'Could not open link.');
  }

  static FaIconData? _socialFaIcon(String key) {
    switch (key) {
      case 'facebook':
        return FontAwesomeIcons.facebook;
      case 'instagram':
        return FontAwesomeIcons.instagram;
      case 'youtube':
        return FontAwesomeIcons.youtube;
      default:
        return null;
    }
  }

  String _streamSubtitle(RoguesRadioUiState state) {
    switch (state) {
      case RoguesRadioUiState.playing:
        return 'Live on air — tap to pause';
      case RoguesRadioUiState.loading:
        return 'Connecting…';
      case RoguesRadioUiState.paused:
        return 'Paused — tap to resume';
      case RoguesRadioUiState.error:
        return 'Tap to try again';
      case RoguesRadioUiState.idle:
        return 'Listen live in app';
    }
  }

  String _websiteSubtitle() {
    final raw = BrandConfig.brandPublicWebsiteUrl.trim();
    if (raw.isEmpty) return 'Station website';
    return Uri.tryParse(raw)?.host ?? raw;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final accent = BrandConfig.stationAccent;
    final dark = BrandConfig.usesDarkChrome;
    final uiState = ref.watch(roguesRadioUiStateProvider).maybeWhen(
          data: (state) => state,
          orElse: () => RoguesRadioUiState.idle,
        );

    final subtitleMuted = dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText;
    final heroTitleColor = dark ? const Color(0xFFF8FAFC) : BrandConfig.lightText;

    final actions = <_StationAction>[
      _StationAction(
        icon: uiState == RoguesRadioUiState.playing
            ? CupertinoIcons.pause_circle
            : AppIconData.playCircle,
        label: BrandConfig.stationStreamLabel,
        subtitle: _streamSubtitle(uiState),
        onTap: (ctx) => _toggleStream(ref, ctx),
      ),
      if (BrandConfig.isRiseFm)
        _StationAction(
          icon: AppIconData.playCircle,
          label: 'Open in iono',
          subtitle: 'Full player & schedule',
          onTap: (ctx) => _openUri(
            ctx,
            Uri.parse('https://iono.fm/s/73'),
            failMessage: 'Could not open iono player.',
          ),
        ),
      if (BrandConfig.isVowFm)
        _StationAction(
          icon: AppIconData.playCircle,
          label: 'Open in iono',
          subtitle: 'Full player & schedule',
          onTap: (ctx) => _openUri(
            ctx,
            Uri.parse('https://iono.fm/s/101'),
            failMessage: 'Could not open iono player.',
          ),
        ),
      if (BrandConfig.isHot1027)
        _StationAction(
          icon: AppIconData.playCircle,
          label: 'Open in iono',
          subtitle: 'Full player & schedule',
          onTap: (ctx) => _openUri(
            ctx,
            Uri.parse('https://iono.fm/s/57'),
            failMessage: 'Could not open iono player.',
          ),
        ),
      if (BrandConfig.isMix938)
        _StationAction(
          icon: AppIconData.playCircle,
          label: 'Open on Zeno',
          subtitle: 'Full web player',
          onTap: (ctx) => _openUri(
            ctx,
            Uri.parse(BrandConfig.mix938ZenoWebUrl),
            failMessage: 'Could not open Zeno player.',
          ),
        ),
      _StationAction(
        icon: AppIconData.globe,
        label: 'Open website',
        subtitle: _websiteSubtitle(),
        onTap: (ctx) => _openWebsite(ctx),
      ),
      _StationAction(
        icon: AppIconData.groups,
        label: BrandConfig.isRiseFm || BrandConfig.isHot1027 ? 'Show schedule' : 'Hosts & shows',
        subtitle: BrandConfig.isRiseFm || BrandConfig.isHot1027 ? 'Mon–Sun lineup' : 'Who\'s on air',
        onTap: (ctx) => _openHosts(ctx),
      ),
      _StationAction(
        icon: AppIconData.chat,
        label: BrandConfig.stationWhatsAppLabel,
        subtitle: BrandConfig.stationWhatsAppSubtitle,
        onTap: (ctx) => _openWhatsApp(ctx),
      ),
      if (BrandConfig.stationRequestPageUrl.trim().isNotEmpty)
        _StationAction(
          icon: AppIconData.mic,
          label: 'Send a request',
          subtitle: 'Song or shout-out',
          onTap: (ctx) => _openRequest(ctx),
        ),
      if (BrandConfig.stationPollContext.trim().isEmpty)
        _StationAction(
          icon: AppIconData.chartBar,
          label: 'Listener poll',
          subtitle: BrandConfig.stationSurveyUrl.trim().isNotEmpty
              ? 'Vote in the poll'
              : (BrandConfig.isRogues || BrandConfig.isRiseFm
                  ? 'Tap to vote'
                  : 'Not configured yet'),
          onTap: (ctx) => _openPoll(ctx),
        ),
      _StationAction(
        icon: AppIconData.trophy,
        label: 'Quiz & competitions',
        subtitle: 'Enter active promos',
        onTap: (ctx) => _openCompetition(ctx),
      ),
      _StationAction(
        icon: AppIconData.feedback,
        label: 'Feedback',
        subtitle: 'Errata & suggestions',
        onTap: (ctx) => _openFeedback(ctx),
      ),
      if (BrandConfig.stationPodcastUrl.isNotEmpty)
        _StationAction(
          icon: CupertinoIcons.headphones,
          label: 'Catch up',
          subtitle: 'Listen again',
          onTap: (ctx) => _openPodcast(ctx),
        ),
      if (BrandConfig.stationStudioPhoneE164.isNotEmpty)
        _StationAction(
          icon: CupertinoIcons.phone,
          label: 'Call studio',
          subtitle: '+${BrandConfig.stationStudioPhoneE164}',
          onTap: (ctx) => _callStudio(ctx),
        ),
      for (final social in BrandConfig.stationSocialLinks)
        _StationAction(
          icon: AppIconData.globe,
          faIcon: _socialFaIcon(social[2]),
          label: social[0],
          subtitle: 'Follow us',
          onTap: (ctx) => _openSocial(ctx, social[1]),
        ),
    ];

    return Scaffold(
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          BrandLogoHeader(title: BrandConfig.stationTabHeaderTitle),
          Expanded(
            child: RefreshIndicator(
              onRefresh: _onRefresh,
              child: ListView(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                children: [
                  Text(
                    BrandConfig.stationTabHeroTitle,
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w800,
                      color: BrandConfig.isRiseFm ? accent : heroTitleColor,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    BrandConfig.stationTabHeroSubtitle,
                    style: theme.textTheme.bodySmall?.copyWith(color: subtitleMuted),
                  ),
                  const SizedBox(height: 10),
                  _StationLivePlayerCard(
                    accent: accent,
                    uiState: uiState,
                    streamLabel: BrandConfig.stationStreamLabel,
                    onToggle: () => _toggleStream(ref, context),
                    schedule: BrandConfig.isRiseFm ? _riseTodaySchedule : null,
                    onAirShowName: BrandConfig.isHot1027
                        ? _hotBroadcast?.currentShow?.title
                        : null,
                    onAirImageUrl: BrandConfig.isHot1027
                        ? _hotBroadcast?.currentImageUrl
                        : null,
                    nextShowName: BrandConfig.isHot1027
                        ? _hotBroadcast?.nextShow?.title
                        : null,
                  ),
                  const SizedBox(height: 8),
                  if (BrandConfig.isMix938) ...[
                    ZenoNowPlayingBanner(mountId: BrandConfig.mix938ZenoMountId),
                    const SizedBox(height: 8),
                  ],
                  if (BrandConfig.isRiseFm) ...[
                    _RiseFmQualityPicker(
                      quality: _riseStreamQuality,
                      accent: accent,
                      onChanged: _onRiseQualityChanged,
                    ),
                    const SizedBox(height: 8),
                  ],
                  if (BrandConfig.isHot1027) ...[
                    _RiseFmQualityPicker(
                      quality: _hot1027StreamQuality,
                      accent: accent,
                      onChanged: _onHot1027QualityChanged,
                    ),
                    const SizedBox(height: 8),
                  ],
                  if (BrandConfig.stationPollContext.trim().isNotEmpty) ...[
                    const SizedBox(height: 8),
                    StationPollSection(
                      pollContext: BrandConfig.stationPollContext,
                      accentColor: accent,
                    ),
                  ],
                  GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      mainAxisSpacing: 6,
                      crossAxisSpacing: 6,
                      mainAxisExtent: 42,
                    ),
                    itemCount: actions.length,
                    itemBuilder: (context, index) {
                      return _StationActionChip(
                        action: actions[index],
                        accent: accent,
                      );
                    },
                  ),
                  if (BrandConfig.isFm919) ...[
                    const SizedBox(height: 16),
                    _Fm919NewsSection(
                      digest: _news,
                      loading: _newsLoading,
                      accent: accent,
                      onOpen: (url) => _openUri(context, Uri.parse(url), failMessage: 'Could not open article.'),
                      onRefresh: _loadNews,
                    ),
                  ],
                  if (BrandConfig.isHot1027) ...[
                    const SizedBox(height: 16),
                    _Hot1027TodaySection(
                      schedule: _hotTodaySchedule,
                      broadcast: _hotBroadcast,
                      loading: _hotScheduleLoading,
                      accent: accent,
                      onRefresh: _loadHot1027Schedule,
                    ),
                  ],
                  if (BrandConfig.isRiseFm) ...[
                    const SizedBox(height: 16),
                    _RiseFmTodaySection(
                      schedule: _riseTodaySchedule,
                      loading: _riseScheduleLoading,
                      accent: accent,
                      onRefresh: _loadRiseSchedule,
                    ),
                    const SizedBox(height: 16),
                    _RiseFmNewsSection(
                      digest: _riseNews,
                      loading: _riseNewsLoading,
                      accent: accent,
                      onOpen: (url) => _openUri(
                        context,
                        Uri.parse(url),
                        failMessage: 'Could not open article.',
                      ),
                      onRefresh: _loadRiseNews,
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

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
                  subtitle: item.excerpt.isNotEmpty
                      ? Text(
                          item.excerpt,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        )
                      : null,
                  trailing: const Icon(Icons.open_in_new, size: 16),
                  onTap: () => onOpen(item.url),
                ),
              ),
      ],
    );
  }
}

class _Hot1027TodaySection extends StatelessWidget {
  const _Hot1027TodaySection({
    required this.schedule,
    required this.broadcast,
    required this.loading,
    required this.accent,
    required this.onRefresh,
  });

  final Hot1027DaySchedule? schedule;
  final Hot1027Broadcast? broadcast;
  final bool loading;
  final Color accent;
  final Future<void> Function() onRefresh;

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dark = BrandConfig.usesDarkChrome;
    final dayLabel = schedule?.dayName ?? 'Today';
    final shows = schedule?.shows ?? [];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                'Today on HOT 102.7',
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
        if (broadcast?.currentShow != null) ...[
          const SizedBox(height: 4),
          Text(
            'On air now: ${broadcast!.currentShow!.title}',
            style: theme.textTheme.bodyMedium?.copyWith(fontWeight: FontWeight.w600),
          ),
          if (broadcast!.nextShow != null)
            Text(
              'Up next: ${broadcast!.nextShow!.title} (${broadcast!.nextShow!.timeRange})',
              style: theme.textTheme.bodySmall?.copyWith(
                color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
              ),
            ),
        ],
        const SizedBox(height: 8),
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
            style: theme.textTheme.bodySmall,
          )
        else if (shows.isEmpty)
          Text(
            'No schedule slots for today.',
            style: theme.textTheme.bodySmall,
          )
        else
          ...shows.map(
            (show) => _RiseScheduleRow(
              time: show.timeRange,
              show: show.title,
              host: 'HOT 102.7',
              accent: accent,
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
    final dayLabel = schedule != null
        ? _weekdayNames[(schedule!.weekday.clamp(1, 7)) - 1]
        : _weekdayNames[DateTime.now().weekday - 1];
    final shows = schedule?.shows ?? [];

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
        else if (shows.isEmpty)
          Text(
            'No schedule slots for today.',
            style: theme.textTheme.bodySmall?.copyWith(
              color: dark ? const Color(0xFF64748B) : BrandConfig.lightMutedText,
            ),
          )
        else
          ...shows.map(
            (show) => _RiseScheduleRow(
              time: show.timeRange,
              show: show.title,
              host: show.host ?? 'RISE team',
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
                  subtitle: item.excerpt.isNotEmpty
                      ? Text(
                          item.excerpt,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        )
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
    this.onAirShowName,
    this.onAirImageUrl,
    this.nextShowName,
  });

  final Color accent;
  final RoguesRadioUiState uiState;
  final String streamLabel;
  final VoidCallback onToggle;
  final RiseFmDaySchedule? schedule;
  final String? onAirShowName;
  final String? onAirImageUrl;
  final String? nextShowName;

  String? _currentShowLabel() {
    final shows = schedule?.shows;
    if (shows == null || shows.isEmpty) return null;
    final now = DateTime.now();
    final minutesNow = now.hour * 60 + now.minute;

    var currentShow = shows.first.title;
    for (final show in shows) {
      final start = _parseStartMinutes(show.timeRange);
      if (start == null) continue;
      if (start <= minutesNow) {
        currentShow = show.title;
      } else {
        break;
      }
    }
    return currentShow;
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
    final onNow = onAirShowName ?? _currentShowLabel();

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
              BrandConfig.stationTabHeroTitle,
              style: theme.textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.w700,
                color: dark ? const Color(0xFFF8FAFC) : BrandConfig.lightText,
              ),
            ),
            if (BrandConfig.stationTabHeroSubtitle.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                BrandConfig.stationTabHeroSubtitle,
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
