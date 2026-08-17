<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bandsintown Public API
    |--------------------------------------------------------------------------
    |
    | Artist-centric event pull. Platform-wide access usually needs a partner
    | app_id from Bandsintown (API@bandsintown.com). Per-artist keys from
    | Bandsintown for Artists only cover that one artist.
    |
    | Docs: https://help.artists.bandsintown.com/en/articles/9186477-api-documentation
    | Spec: https://app.swaggerhub.com/apis-docs/Bandsintown/PublicAPI/3.0.1
    |
    */

    'app_id' => env('BANDSINTOWN_APP_ID'),

    'base_url' => rtrim((string) env('BANDSINTOWN_BASE_URL', 'https://rest.bandsintown.com'), '/'),

    'timeout_seconds' => (int) env('BANDSINTOWN_TIMEOUT', 45),

    /**
     * Pipe-separated artist names to pull when no CLI artist is given.
     * Example: BANDSINTOWN_ARTISTS=Black Coffee|Tyla|Jeremy Loops
     */
    'artists' => array_values(array_filter(array_map(
        'trim',
        explode('|', (string) env('BANDSINTOWN_ARTISTS', ''))
    ))),

    /** ISO country codes to keep (empty = all). Default South Africa. */
    'country_allowlist' => array_values(array_filter(array_map(
        fn ($c) => strtoupper(trim((string) $c)),
        explode(',', (string) env('BANDSINTOWN_COUNTRIES', 'ZA'))
    ))),

    /**
     * Optional city substrings (case-insensitive). Empty = all cities in allowlisted countries.
     * Example: Johannesburg,Pretoria,Centurion,Sandton
     */
    'city_allowlist' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('BANDSINTOWN_CITIES', ''))
    ))),

    'owner_user_id' => env('BANDSINTOWN_OWNER_USER_ID') !== null && env('BANDSINTOWN_OWNER_USER_ID') !== ''
        ? (int) env('BANDSINTOWN_OWNER_USER_ID')
        : (env('QUICKET_OWNER_USER_ID') !== null && env('QUICKET_OWNER_USER_ID') !== ''
            ? (int) env('QUICKET_OWNER_USER_ID')
            : null),

];
