<?php

return [
    /** Live stream (iono.fm AAC) — same URL as the Rogues Flutter app. */
    'stream_url' => env('ROGUES_STREAM_URL', 'https://edge.iono.fm/xice/441_medium.aac'),

    /** Station marketing site (optional link on listen page). */
    'public_site_url' => env('ROGUES_PUBLIC_SITE_URL', 'https://www.roguesonradio.co.za'),
];
