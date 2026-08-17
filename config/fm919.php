<?php

return [
    /** Live stream (iono.fm AAC) — same URL as the 919 FM Flutter app. */
    'stream_url' => env('FM919_STREAM_URL', 'https://edge.iono.fm/xice/112_medium.aac'),

    /** Station marketing site (optional link on listen page). */
    'public_site_url' => env('FM919_PUBLIC_SITE_URL', 'https://919.co.za'),

    /** WhatsApp line for song requests (E.164, no +). */
    'whatsapp_e164' => env('FM919_WHATSAPP_E164', '27842212919'),

    /**
     * Comma-separated station staff emails allowed into /station on 919fm host.
     * Admins and superusers always have access. Example: kayise@example.com,studio@919.co.za
     */
    'station_emails' => env('FM919_STATION_EMAILS', ''),
];
