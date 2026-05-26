<?php

use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MetaController;
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
    Route::get('events/{event}', [EventController::class, 'show']);

    Route::get('venues', [VenueController::class, 'index']);
    Route::get('venues/{venue}', [VenueController::class, 'show']);

    Route::get('artists', [ArtistController::class, 'index']);
    Route::get('artists/{artist}', [ArtistController::class, 'show']);

    Route::get('{type}/{id}/reviews', [RatingController::class, 'index'])
        ->whereIn('type', ['events', 'artists', 'venues'])
        ->whereNumber('id');

    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/firebase', [AuthController::class, 'firebaseLogin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('me', [MeController::class, 'show']);
        Route::post('me/claims/initiate', [MeController::class, 'initiateClaims']);
        Route::post('me/claims/request', [MeController::class, 'requestClaim']);
        Route::post('me/web-session', [MeController::class, 'createWebSession']);
        Route::post('me/link-firebase', [MeController::class, 'linkFirebase']);
        Route::get('me/favorites', [MeController::class, 'favorites']);
        Route::post('me/favorites/{type}/{id}', [MeController::class, 'addFavorite'])
            ->whereNumber('id');
        Route::delete('me/favorites/{type}/{id}', [MeController::class, 'removeFavorite'])
            ->whereNumber('id');

        Route::post('events', [EventController::class, 'store'])
            ->middleware('api.permission:create-events');

        Route::post('events/{event}', [EventController::class, 'update'])
            ->middleware('api.permission:create-events');

        Route::match(['put', 'patch'], 'events/{event}', [EventController::class, 'update'])
            ->middleware('api.permission:create-events');

        Route::post('artists', [ArtistController::class, 'store'])
            ->middleware('api.permission:create-events');

        Route::post('venues', [VenueController::class, 'store'])
            ->middleware('api.permission:create-events');

        Route::post('ratings', [RatingController::class, 'store'])
            ->middleware('api.permission:rate-content');
    });
});
