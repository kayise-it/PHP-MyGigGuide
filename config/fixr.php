<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FIXR (organiser event feed)
    |--------------------------------------------------------------------------
    |
    | FIXR’s documented API is for events on *your* organiser account — not a
    | public “all local gigs” directory. Token: contact FIXR / account manager.
    | Docs: https://docs.fixr.co/api/
    |
    | Alternative: webhooks from organiser.fixr.co → our HTTPS endpoint.
    |
    */

    'api_token' => env('FIXR_API_TOKEN'),

    /** Set once FIXR gives you the events feed URL (placeholder until confirmed). */
    'events_url' => env('FIXR_EVENTS_URL'),

    'timeout_seconds' => (int) env('FIXR_TIMEOUT', 45),

    /** Shared secret to verify inbound FIXR webhooks (Bearer). */
    'webhook_bearer' => env('FIXR_WEBHOOK_BEARER'),

    'owner_user_id' => env('FIXR_OWNER_USER_ID') !== null && env('FIXR_OWNER_USER_ID') !== ''
        ? (int) env('FIXR_OWNER_USER_ID')
        : (env('QUICKET_OWNER_USER_ID') !== null && env('QUICKET_OWNER_USER_ID') !== ''
            ? (int) env('QUICKET_OWNER_USER_ID')
            : null),

];
