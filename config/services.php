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

    /*
     * Evolution API — self-hosted WhatsApp Business API for admin claim notifications.
     * Set EVOLUTION_API_URL, EVOLUTION_INSTANCE, EVOLUTION_API_KEY, and EVOLUTION_ADMIN_NUMBER
     * in .env to activate. WhatsApp notification is silently skipped if any are blank.
     *
     * Evolution API send-text endpoint:
     *   POST {url}/message/sendText/{instance}
     *   Header: apikey: {api_key}
     *   Body:   { "number": "27XXXXXXXXX", "text": "..." }
     */
    'evolution' => [
        'url'          => env('EVOLUTION_API_URL', ''),
        'instance'     => env('EVOLUTION_INSTANCE', ''),
        'api_key'      => env('EVOLUTION_API_KEY', ''),
        'admin_number' => env('EVOLUTION_ADMIN_NUMBER', ''),
    ],

    /*
     * Event creation notifications — WhatsApp alert sent when any user crowd-sources a new event.
     * Comma-separated user IDs in EVENT_NOTIFY_EXCLUDE_USER_IDS are never notified about
     * (add Dave's own ID + the Quicket system user ID so their imports stay silent).
     * Notification is also silently skipped when Evolution API vars are not set.
     */
    'event_notifications' => [
        'exclude_user_ids' => array_filter(array_map('intval', explode(',', env('EVENT_NOTIFY_EXCLUDE_USER_IDS', '')))),
    ],

];
