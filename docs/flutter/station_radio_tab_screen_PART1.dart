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
import '../providers/rogues_radio_player_provider.dart';
import '../services/fm919_site_parser.dart';
import '../services/fm919_site_repository.dart';
import '../services/risefm_site_parser.dart';
import '../services/risefm_site_repository.dart';
import '../services/rogues_radio_player.dart';
import '../widgets/app_icon.dart';
import '../widgets/brand_logo_header.dart';
import '../widgets/zeno_now_playing_banner.dart';
import 'rise_demo_poll_screen.dart';
import 'rogues_demo_poll_screen.dart';
import 'site_web_tab_screen.dart';
import 'station_hosts_screen.dart';

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
  Fm919NewsDigest? _news;
  bool _newsLoading = false;
  RiseFmDaySchedule? _riseTodaySchedule;
  bool _riseScheduleLoading = false;
  RiseFmNewsDigest? _riseNews;
  bool _riseNewsLoading = false;
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
    if (BrandConfig.isHot1027) _loadHot1027StreamQuality();
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
        label: BrandConfig.isRiseFm ? 'Show schedule' : 'Hosts & shows',
        subtitle: BrandConfig.isRiseFm ? 'Mon–Sun lineup' : 'Who\'s on air',
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
        onTap: (ctx) => _openPoll(ctx),
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

// --- PART 2 CONTINUES BELOW (helper widget classes) ---
