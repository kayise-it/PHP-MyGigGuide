<?php

namespace App\Providers;

use App\Models\Artist;
use App\Models\Organiser;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set up morph map for polymorphic relationships
        Relation::morphMap([
            'App\Models\Artist' => Artist::class,
            'App\Models\Organiser' => Organiser::class,
            'App\Models\User' => User::class,
            'App\Models\Event' => \App\Models\Event::class,
            'App\Models\Venue' => \App\Models\Venue::class,
            // Legacy mappings for backward compatibility
            'artist' => Artist::class,
            'organiser' => Organiser::class,
            'user' => User::class,
            'admin' => User::class,
            'superuser' => User::class,
        ]);
    }
}
