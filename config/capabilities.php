<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route to capability mapping
    |--------------------------------------------------------------------------
    |
    | Maps route names to capability (permission) names. These names are
    | Laratrust permission "name" values and are treated as capabilities.
    |
    */
    'routes' => [
        // Dashboards
        'dashboard.artist' => 'view-events', // plus edit-artist-profile for management
        'dashboard.organiser' => 'view-events',
        'dashboard.venue-owner' => 'view-venues',
        'dashboard.admin' => 'manage-system',
        'dashboard.user' => 'view-events',

        // Artist profile & listing
        'artists.index' => 'view-artists',
        'artists.show' => 'view-artists',
        'artists.edit' => 'edit-artist-profile',
        'artists.update' => 'edit-artist-profile',
        'artists.create' => 'create-artist-profile',
        'artists.store' => 'create-artist-profile',
        'artists.destroy' => 'edit-artist-profile',

        // Venues
        'venues.index' => 'view-venues',
        'venues.show' => 'view-venues',
        'venues.create' => 'create-venues',
        'venues.store' => 'create-venues',
        'venues.edit' => 'edit-venues',
        'venues.update' => 'edit-venues',
        'venues.destroy' => 'delete-venues',
        'venues.quick-store' => 'create-venues',
        'venues.request-ownership' => 'claim-venues',
        'venues.my-requests' => 'claim-venues',
        'venues.approve-request' => 'claim-venues',
        'venues.reject-request' => 'claim-venues',
        'venues.add-owner' => 'edit-venues',
        'venues.update-owner-role' => 'edit-venues',
        'venues.remove-owner' => 'edit-venues',

        // Events
        'events.index' => 'view-events',
        'events.show' => 'view-events',
        'events.create' => 'create-events',
        'events.store' => 'create-events',
        'events.edit' => 'edit-events',
        'events.update' => 'edit-events',
        'events.destroy' => 'delete-events',
        'events.rate' => 'rate-content',
        'events.calendar' => 'view-events',

        // Profile
        'profile.show' => 'edit-profile',
        'profile.edit' => 'edit-profile',
        'profile.update' => 'edit-profile',
        'profile.destroy' => 'edit-profile',

        // Favorites & ratings
        'favorites.events.toggle' => 'rate-content',
        'favorites.venues.toggle' => 'rate-content',
        'favorites.artists.toggle' => 'rate-content',
        'favorites.organisers.toggle' => 'rate-content',
        'favorites.check' => 'rate-content',
        'ratings.store' => 'rate-content',
        'reviews.load-more' => 'view-ratings',

        // Admin routes (high-level examples)
        'admin.dashboard' => 'manage-system',
        'admin.index' => 'manage-system',
        'admin.users.index' => 'manage-users',
        'admin.events.index' => 'view-events',
        'admin.venues.index' => 'view-venues',
        'admin.artists.index' => 'view-artists',
        'admin.organisers.index' => 'view-organisers',
        'admin.settings.index' => 'manage-system',
        'admin.settings.update' => 'manage-system',
    ],

    /*
    |--------------------------------------------------------------------------
    | Capability groups (for future admin UI)
    |--------------------------------------------------------------------------
    */
    'groups' => [
        'dashboard' => [
            'view-events',
            'view-venues',
            'manage-system',
        ],
        'artist' => [
            'create-artist-profile',
            'edit-artist-profile',
            'view-artists',
        ],
        'venue' => [
            'create-venues',
            'edit-venues',
            'delete-venues',
            'view-venues',
            'claim-venues',
        ],
        'event' => [
            'create-events',
            'edit-events',
            'delete-events',
            'view-events',
        ],
        'organiser' => [
            'create-organiser-profile',
            'edit-organiser-profile',
            'view-organisers',
        ],
        'rating' => [
            'rate-content',
            'view-ratings',
        ],
        'admin' => [
            'manage-users',
            'moderate-content',
            'view-analytics',
            'manage-system',
        ],
    ],
];

