<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Miggie bridge (poster vision)
    |--------------------------------------------------------------------------
    |
    | Laravel proxies mobile poster reads to the Python bridge on the same VPS
    | (Docker port 8787). WhatsApp/n8n use the same service internally.
    |
    */

    'url' => rtrim((string) env('MIGGS_BRIDGE_URL', 'http://127.0.0.1:8787'), '/'),

    'poster_secret' => env('MIGGS_BRIDGE_POSTER_SECRET', env('WA_BRIDGE_SECRET')),

    'timeout_seconds' => (int) env('MIGGS_BRIDGE_TIMEOUT', 120),

    /** Max upload size in kilobytes (bridge default is 8 MiB). */
    'max_poster_kb' => (int) env('MIGGS_BRIDGE_MAX_POSTER_KB', 8192),

];
