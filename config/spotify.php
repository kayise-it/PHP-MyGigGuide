<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Spotify Web API (client credentials)
    |--------------------------------------------------------------------------
    |
    | Used to search tracks and import metadata into artist repertoire.
    | Create an app at https://developer.spotify.com/dashboard
    |
    */

    'client_id' => env('SPOTIFY_CLIENT_ID'),

    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),

    'token_url' => 'https://accounts.spotify.com/api/token',

    'api_base_url' => 'https://api.spotify.com/v1',

    'timeout_seconds' => (int) env('SPOTIFY_TIMEOUT', 20),

    /** Max tracks imported from one playlist paste. */
    'playlist_import_limit' => (int) env('SPOTIFY_PLAYLIST_IMPORT_LIMIT', 100),

    /** ISO market for track availability (ZA = South Africa). Required for many API responses. */
    'market' => env('SPOTIFY_MARKET', 'ZA'),

    /** OAuth redirect — must match Spotify dashboard exactly (e.g. https://mygigguide.co.za/spotify/callback). */
    'redirect_uri' => env('SPOTIFY_REDIRECT_URI'),

    /** Scopes for playlist import after Spotify Feb 2026 API changes. */
    'scopes' => env('SPOTIFY_SCOPES', 'playlist-read-private playlist-read-collaborative'),

];
