// Snippet — merge into lib/brand_config.dart for in-app listener polls.
//
// Poll API uses BrandConfig.siteUrl + /api/v1/polls/{context}

/// Laravel poll context for this flavor (empty = fall back to survey URL / demo).
static String get stationPollContext {
  if (isMix938) return 'mix938';
  if (isVowFm) return 'vowfm';
  return '';
}

/// Site root for PollApiService — required when stationPollContext is set.
static String get siteUrl {
  // Return your existing SITE_URL dart-define value, e.g.:
  // return const String.fromEnvironment('SITE_URL', defaultValue: 'https://www.mygigguide.co.za');
}
