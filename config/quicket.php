<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quicket public events API
    |--------------------------------------------------------------------------
    |
    | Read-only pull of ticketed listings. We store Quicket event URLs as
    | ticket_url and never sell tickets ourselves.
    |
    | Key: https://developer.quicket.co.za/ → register → API subscriber key
    | Docs: https://docs.quicket.com/
    |
    */

    'api_key' => env('QUICKET_API_KEY'),

    'base_url' => rtrim((string) env('QUICKET_BASE_URL', 'https://api.quicket.co.za/api/events'), '/'),

    'timeout_seconds' => (int) env('QUICKET_TIMEOUT', 60),

    /** Default page size when pulling (API max may vary). */
    'page_size' => (int) env('QUICKET_PAGE_SIZE', 25),

    /**
     * Quicket category IDs to request (comma-separated in .env).
     * Known: 1 = Music, 5 = Sports & Fitness, 6 = Travel & Outdoor,
     * 9 = Arts & Culture, 30 = Family & Education, 64 = Other.
     * Cron should stay on Music (1) until non-music seeds look good.
     */
    'categories' => array_values(array_filter(array_map(
        'intval',
        explode(',', (string) env('QUICKET_CATEGORIES', '1'))
    ))),

    /**
     * User id that owns imported events (morph owner_type=user).
     * Required for --apply; dry-run works without it.
     */
    'owner_user_id' => env('QUICKET_OWNER_USER_ID') !== null && env('QUICKET_OWNER_USER_ID') !== ''
        ? (int) env('QUICKET_OWNER_USER_ID')
        : null,

    /** Only import events whose locality.levelTwo matches (case-insensitive), empty = all. */
    'province_allowlist' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env(
            'QUICKET_PROVINCES',
            'Gauteng,Western Cape,KwaZulu-Natal,Free State,Eastern Cape'
        ))
    ))),

    /**
     * Map Quicket API category id → My Gig Guide category slugs.
     * Always include `quicket` so imports stay filterable by source.
     * Create matching MGG categories in admin before --apply for non-music.
     *
     * Known Quicket IDs currently mapped: 1, 5, 6, 9, 30, 64.
     * Any Quicket category ID not listed here will fall back to mgg_category_slugs
     * (live-music, quicket) and log a warning: "Quicket: unmapped category ID X for event '...'"
     * — check storage/logs/laravel.log after a pull to spot new IDs.
     *
     * Broad categories (9 = Arts & Culture, 64 = Other) intentionally map to ['quicket'] only.
     * QuicketImportService::refineCategories() then scans the event title and description for
     * keywords and adds more specific slugs (comedy, theatre, open-mic, festival, etc.).
     * This prevents a "Round Coffee Table Workshop" from landing in theatre just because
     * Quicket filed it under Arts & Culture.
     *
     * Potential additions (add if/when Quicket starts sending them):
     *   2 => ['comedy', 'quicket'],                 // Comedy
     *   3 => ['festival', 'quicket'],               // Festival
     *   4 => ['dj-club', 'quicket'],                // Club/DJ
     */
    'category_slug_map' => [
        1 => ['live-music', 'quicket'],              // Music
        5 => ['sports', 'quicket'],                  // Sports & Fitness
        6 => ['travel-outdoor', 'quicket'],          // Travel & Outdoor
        30 => ['family-friendly', 'quicket'],        // Family & Education
        9 => ['quicket'],                            // Arts & Culture — too broad; refineCategories() adds specifics
        64 => ['quicket'],                           // Other — too broad; refineCategories() adds specifics
    ],

    /**
     * @deprecated Prefer category_slug_map. Kept as Music fallback / backfill default.
     */
    'mgg_category_slugs' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('QUICKET_MGG_CATEGORY_SLUGS', 'live-music,quicket'))
    ))),

];
