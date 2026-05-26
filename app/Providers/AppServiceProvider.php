<?php

namespace App\Providers;

use App\Models\Artist;
use App\Models\Organiser;
use App\Models\User;
use App\Services\MailCredentialsService;
use App\Support\SiteBrand;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\View;
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
        // Mail credentials from Admin > Mail Accounts (encrypted file) instead of .env when set
        MailCredentialsService::applyToConfig();

        if (! $this->app->runningInConsole()) {
            View::share('siteBrand', SiteBrand::current());
        }

        // Share unread notifications for navbar (when user is logged in and notifications table exists)
        View::composer('layouts.navigation', function ($view) {
            if (auth()->check()) {
                try {
                    $user = auth()->user();
                    $unreadNotificationCount = $user->unreadNotifications()->count();
                    $notifications = $user->unreadNotifications()->take(15)->get();
                    $view->with('unreadNotificationCount', $unreadNotificationCount);
                    $view->with('navbarNotifications', $notifications);
                } catch (\Throwable $e) {
                    $view->with('unreadNotificationCount', 0);
                    $view->with('navbarNotifications', collect());
                }
            } else {
                $view->with('unreadNotificationCount', 0);
                $view->with('navbarNotifications', collect());
            }
        });

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
