<?php

use App\Http\Controllers\Api\V1\ArtistController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\MetaController;
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

    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('me', [MeController::class, 'show']);
        Route::get('me/favorites', [MeController::class, 'favorites']);
        Route::post('me/favorites/{type}/{id}', [MeController::class, 'addFavorite'])
            ->whereNumber('id');
        Route::delete('me/favorites/{type}/{id}', [MeController::class, 'removeFavorite'])
            ->whereNumber('id');
    });
});
