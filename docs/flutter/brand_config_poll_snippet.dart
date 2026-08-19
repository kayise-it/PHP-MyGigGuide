// Snippet — merge into lib/brand_config.dart for in-app listener polls.
//
// Polls are served from the MGG Laravel API — NOT the station website
// (mix938.com / vowfm.co.za do not host /api/v1/polls).

/// Laravel host for listener poll API (same for all flavors).
static String get pollApiSiteUrl {
  const override = String.fromEnvironment('POLL_API_SITE_URL', defaultValue: '');
  if (override.isNotEmpty) return override;
  return 'https://www.mygigguide.co.za';
}

/// Poll context sent to GET /api/v1/polls/{context}
static String get stationPollContext {
  if (isMix938) return 'mix938';
  if (isVowFm) return 'vowfm';
  return '';
}
