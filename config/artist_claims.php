<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Artist Claim Grace Period
    |--------------------------------------------------------------------------
    |
    | This setting controls the grace period before automatically claiming
    | an artist profile when someone registers with a matching email.
    |
    | enable_grace_period: Set to true to enable the 48-hour grace period.
    |                      When false, claims happen immediately after email verification.
    |                      For testing: set to false to bypass the waiting period.
    |
    | grace_period_hours: Number of hours to wait before auto-claiming (default: 48).
    |
    */
    
    'enable_grace_period' => env('ARTIST_CLAIM_GRACE_PERIOD_ENABLED', false),
    
    'grace_period_hours' => env('ARTIST_CLAIM_GRACE_PERIOD_HOURS', 48),
    
    /*
    |--------------------------------------------------------------------------
    | Admin Email Notifications
    |--------------------------------------------------------------------------
    |
    | When set to true, admins will receive email notifications when
    | someone attempts to claim an artist profile or when disputes are raised.
    |
    */
    
    'notify_admins' => env('ARTIST_CLAIM_NOTIFY_ADMINS', true),
    
    'admin_email' => env('ARTIST_CLAIM_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')),
];







