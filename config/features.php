<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Central place to toggle optional functionality without touching code.
    | Leverage environment variables for per-environment overrides.
    |
    */

    'dashboard_stats' => env('FEATURE_DASHBOARD_STATS', true),
];

