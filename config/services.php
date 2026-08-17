<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', env('APP_URL') . '/auth/facebook/callback'),
    ],

    'firebase' => [
        /** Web API Key — verify ID tokens server-side and initialize Firebase JS on the website. */
        'web_api_key' => env('FIREBASE_WEB_API_KEY'),
        'project_id' => env('FIREBASE_PROJECT_ID', 'my-gig-guide-43138'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN', 'my-gig-guide-43138.firebaseapp.com'),
        'web_app_id' => env('FIREBASE_WEB_APP_ID', '1:929498924637:web:8d691f0f25d1cd9f72afb4'),
    ],

    'evolution' => [
        'enabled' => env('EVOLUTION_API_ENABLED', false),
        'url' => env('EVOLUTION_API_URL'),
        'instance' => env('EVOLUTION_INSTANCE'),
        'admin_number' => env('EVOLUTION_ADMIN_NUMBER'),
    ],

];
