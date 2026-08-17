<?php

use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\ArtistRepertoireController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ContentReportController;
use App\Http\Controllers\Api\V1\EventCheckInController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\LiveSessionController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MetaController;
use App\Http\Controllers\Api\V1\PollController;
use App\Http\Controllers\Api\V1\RatingController;
use App\Http\Controllers\Api\V1\VenueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile app & integrations (WhatsApp bridge, etc.)
|--------------------------------------------------------------------------
| Public read-only JSON. Add Sanctum / Firebase middleware later for
| protected routes. Base URL: /api/v1/...
*/

Route::prefix('v1')->group(function () {
    Route::get('meta', [MetaController::class, 'show']);

    Route::get('categories', [CategoryController::class, 'index']);

    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{event}', [EventController::class, 'show'])
        ->middleware('auth.sanctum.optional')
        ->whereNumber('event');

    Route::get('venues/map', [VenueController::class, 'map']);
    Route::get('venues', [VenueController::class, 'index']);
    Route::get('venues/{venue}', [VenueController::class, 'show'])
        ->middleware('auth.sanctum.optional');

    Route::get('artists', [ArtistController::class, 'index']);
    Route::get('artists/{artist}', [ArtistController::class, 'show'])
        ->middleware('auth.sanctum.optional');

    Route::get('artists/{artist}/repertoire', [ArtistRepertoireController::class, 'index'])
        ->whereNumber('artist');

    Route::get('artists/{artist}/live', [LiveSessionController::class, 'forArtist'])
        ->whereNumber('artist');

    Route::get('events/{event}/live', [LiveSessionController::class, 'forEvent'])
        ->whereNumber('event');

    Route::get('events/{event}/board', [EventCheckInController::class, 'board'])
        ->middleware('auth.sanctum.optional')
        ->whereNumber('event');

    Route::get('venues/{venue}/tonight', [EventCheckInController::class, 'tonightAtVenue'])
        ->middleware('auth.sanctum.optional')
        ->whereNumber('venue');

    Route::get('live-sessions/{liveSession}', [LiveSessionController::class, 'show'])
        ->whereNumber('liveSession');

    Route::get('{type}/{id}/reviews', [RatingController::class, 'index'])
        ->whereIn('type', ['events', 'artists', 'venues'])
        ->whereNumber('id');

    // Station polls — anonymous, device-fingerprint voting.
    Route::get('polls/{context}', [PollController::class, 'show']);
    Route::post('polls/{poll}/vote', [PollController::class, 'vote']);

    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/firebase', [AuthController::class, 'firebaseLogin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('me', [MeController::class, 'show']);
        Route::get('me/events', [MeController::class, 'events']);
        Route::post('me/claims/initiate', [MeController::class, 'initiateClaims']);
        Route::post('me/claims/request', [MeController::class, 'requestClaim']);
        Route::post('me/web-session', [MeController::class, 'createWebSession']);
        Route::post('me/link-firebase', [MeController::class, 'linkFirebase']);
        Route::get('me/favorites', [MeController::class, 'favorites']);
        Route::get('me/favorites/updates', [MeController::class, 'favoriteUpdates']);
        Route::get('me/page-alerts', [MeController::class, 'pageAlerts']);

        Route::get('me/artist/songs', [ArtistRepertoireController::class, 'meIndex']);
        Route::post('me/artist/songs/bulk', [ArtistRepertoireController::class, 'bulkStore']);
        Route::post('me/artist/songs/spotify', [ArtistRepertoireController::class, 'importSpotify']);
        Route::post('me/artist/songs/spotify/add', [ArtistRepertoireController::class, 'addSpotifyTrack']);
        Route::get('me/artist/songs/spotify/search', [ArtistRepertoireController::class, 'spotifySearch']);
        Route::get('me/artist/songs/spotify/status', [ArtistRepertoireController::class, 'spotifyStatus']);
        Route::get('me/artist/songs/spotify/connect', [ArtistRepertoireController::class, 'spotifyAuthUrl']);
        Route::delete('me/artist/songs/spotify/connect', [ArtistRepertoireController::class, 'spotifyDisconnect']);
        Route::post('me/artist/songs', [ArtistRepertoireController::class, 'store']);
        Route::patch('me/artist/songs/reorder', [ArtistRepertoireController::class, 'reorder']);
        Route::patch('me/artist/songs/{song}', [ArtistRepertoireController::class, 'update'])
            ->whereNumber('song');
        Route::delete('me/artist/songs/{song}', [ArtistRepertoireController::class, 'destroy'])
            ->whereNumber('song');

        Route::get('artists/{artist}/songs', [ArtistRepertoireController::class, 'manageIndex'])
            ->whereNumber('artist');
        Route::post('artists/{artist}/songs/bulk', [ArtistRepertoireController::class, 'manageBulkStore'])
            ->whereNumber('artist');
        Route::post('artists/{artist}/songs/spotify', [ArtistRepertoireController::class, 'manageImportSpotify'])
            ->whereNumber('artist');
        Route::post('artists/{artist}/songs/spotify/add', [ArtistRepertoireController::class, 'manageAddSpotifyTrack'])
            ->whereNumber('artist');
        Route::get('artists/{artist}/songs/spotify/status', [ArtistRepertoireController::class, 'manageSpotifyStatus'])
            ->whereNumber('artist');
        Route::get('artists/{artist}/songs/spotify/connect', [ArtistRepertoireController::class, 'manageSpotifyAuthUrl'])
            ->whereNumber('artist');
        Route::delete('artists/{artist}/songs/spotify/connect', [ArtistRepertoireController::class, 'manageSpotifyDisconnect'])
            ->whereNumber('artist');
        Route::post('artists/{artist}/songs', [ArtistRepertoireController::class, 'manageStore'])
            ->whereNumber('artist');
        Route::patch('artists/{artist}/songs/reorder', [ArtistRepertoireController::class, 'manageReorder'])
            ->whereNumber('artist');
        Route::patch('artists/{artist}/songs/{song}', [ArtistRepertoireController::class, 'manageUpdate'])
            ->whereNumber('artist')
            ->whereNumber('song');
        Route::delete('artists/{artist}/songs/{song}', [ArtistRepertoireController::class, 'manageDestroy'])
            ->whereNumber('artist')
            ->whereNumber('song');

        Route::post('me/live-sessions', [LiveSessionController::class, 'store']);
        Route::post('live-sessions/{liveSession}/end', [LiveSessionController::class, 'end'])
            ->whereNumber('liveSession');
        Route::get('live-sessions/{liveSession}/queue', [LiveSessionController::class, 'queue'])
            ->whereNumber('liveSession');
        Route::patch('live-sessions/{liveSession}/requests/{songRequest}', [LiveSessionController::class, 'updateRequest'])
            ->whereNumber('liveSession')
            ->whereNumber('songRequest');
        Route::patch('live-sessions/{liveSession}/requests/{songRequest}/tip-intent', [LiveSessionController::class, 'recordTipIntent'])
            ->whereNumber('liveSession')
            ->whereNumber('songRequest');
        Route::post('live-sessions/{liveSession}/requests', [LiveSessionController::class, 'submitRequest'])
            ->whereNumber('liveSession');
        Route::get('live-sessions/{liveSession}/my-requests', [LiveSessionController::class, 'myRequests'])
            ->whereNumber('liveSession');

        Route::post('events/{event}/check-in', [EventCheckInController::class, 'checkIn'])
            ->whereNumber('event');
        Route::delete('events/{event}/check-in', [EventCheckInController::class, 'removeCheckIn'])
            ->whereNumber('event');

        Route::post('me/favorites/{type}/{id}', [MeController::class, 'addFavorite'])
            ->whereNumber('id');
        Route::delete('me/favorites/{type}/{id}', [MeController::class, 'removeFavorite'])
            ->whereNumber('id');

        Route::post('events/parse-poster', [EventController::class, 'parsePoster'])
            ->middleware('api.permission:create-events');

        Route::get('events/check-duplicate', [EventController::class, 'checkDuplicate'])
            ->middleware('api.permission:create-events');

        Route::post('events', [EventController::class, 'store'])
            ->middleware('api.permission:create-events');

        Route::post('events/{event}', [EventController::class, 'update'])
            ->middleware('api.permission:create-events')
            ->whereNumber('event');

        Route::match(['put', 'patch'], 'events/{event}', [EventController::class, 'update'])
            ->middleware('api.permission:create-events')
            ->whereNumber('event');

        Route::delete('events/{event}', [EventController::class, 'destroy'])
            ->middleware('api.permission:delete-events')
            ->whereNumber('event');

        Route::post('artists', [ArtistController::class, 'store'])
            ->middleware('api.permission:create-events');

        // Multipart uploads (profile photo) must use POST — PHP does not parse files on PATCH.
        Route::post('artists/{artist}', [ArtistController::class, 'update'])
            ->whereNumber('artist');

        Route::match(['put', 'patch'], 'artists/{artist}', [ArtistController::class, 'update'])
            ->whereNumber('artist');

        Route::post('venues/resolve', [VenueController::class, 'resolve'])
            ->middleware('api.permission:create-events');

        Route::post('venues', [VenueController::class, 'store'])
            ->middleware('api.permission:create-events');

        // Multipart uploads (main picture) must use POST — PHP does not parse files on PATCH.
        Route::post('venues/{venue}', [VenueController::class, 'update'])
            ->whereNumber('venue');

        Route::match(['put', 'patch'], 'venues/{venue}', [VenueController::class, 'update'])
            ->whereNumber('venue');

        Route::post('ratings', [RatingController::class, 'store'])
            ->middleware('api.permission:rate-content');

        Route::post('reports', [ContentReportController::class, 'store'])
            ->middleware('api.permission:rate-content');
    });
});
