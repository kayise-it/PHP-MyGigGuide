// Snippet — merge into lib/brand_config.dart for Mix 93.8 WhatsApp + poll API.
//
// Studio / WhatsApp: 066 417 8469 → E.164 27664178469

// --- add near other mix938 constants ---
static const mix938StudioE164 = '27664178469';

// --- mix938 WhatsApp (standalone flavor + any flavor-aware getters) ---
static Uri? get stationWhatsAppUri {
  if (isMix938) {
    return Uri.parse('https://wa.me/$mix938StudioE164');
  }
  // ... existing other flavors ...
}

static String get stationWhatsAppSubtitle {
  if (isMix938) return '066 417 8469';
  // ...
}

static String get stationStudioPhoneE164 {
  if (isMix938) return mix938StudioE164;
  // ...
}

// --- required for PollApiService (main app On Air polls) ---
static String get siteUrl {
  // Return your existing SITE_URL dart-define value, e.g.:
  // return const String.fromEnvironment('SITE_URL', defaultValue: 'https://www.mygigguide.co.za');
}
