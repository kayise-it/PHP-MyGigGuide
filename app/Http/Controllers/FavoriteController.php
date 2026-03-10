<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\Venue;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite status for an event
     */
    public function toggleEvent(Request $request, Event $event)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to add favorites',
            ], 401);
        }

        $isFavorited = $user->favoriteEvents()->where('event_id', $event->id)->exists();

        if ($isFavorited) {
            $user->favoriteEvents()->detach($event->id);
            $message = 'Event removed from favorites';
        } else {
            $user->favoriteEvents()->attach($event->id);
            $message = 'Event added to favorites';
        }

        return response()->json([
            'success' => true,
            'favorited' => ! $isFavorited,
            'message' => $message,
        ]);
    }

    /**
     * Toggle favorite status for a venue
     */
    public function toggleVenue(Request $request, Venue $venue)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to add favorites',
            ], 401);
        }

        $isFavorited = $user->favoriteVenues()->where('venue_id', $venue->id)->exists();

        if ($isFavorited) {
            $user->favoriteVenues()->detach($venue->id);
            $message = 'Venue removed from favorites';
        } else {
            $user->favoriteVenues()->attach($venue->id);
            $message = 'Venue added to favorites';
        }

        return response()->json([
            'success' => true,
            'favorited' => ! $isFavorited,
            'message' => $message,
        ]);
    }

    /**
     * Toggle favorite status for an artist
     */
    public function toggleArtist(Request $request, Artist $artist)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to add favorites',
            ], 401);
        }

        $isFavorited = $user->favoriteArtists()->where('artist_id', $artist->id)->exists();

        if ($isFavorited) {
            $user->favoriteArtists()->detach($artist->id);
            $message = 'Artist removed from favorites';
        } else {
            $user->favoriteArtists()->attach($artist->id);
            $message = 'Artist added to favorites';
        }

        return response()->json([
            'success' => true,
            'favorited' => ! $isFavorited,
            'message' => $message,
        ]);
    }

    /**
     * Toggle favorite status for an organiser
     */
    public function toggleOrganiser(Request $request, Organiser $organiser)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to add favorites',
            ], 401);
        }

        $isFavorited = $user->favoriteOrganisers()->where('organiser_id', $organiser->id)->exists();

        if ($isFavorited) {
            $user->favoriteOrganisers()->detach($organiser->id);
            $message = 'Organiser removed from favorites';
        } else {
            $user->favoriteOrganisers()->attach($organiser->id);
            $message = 'Organiser added to favorites';
        }

        return response()->json([
            'success' => true,
            'favorited' => ! $isFavorited,
            'message' => $message,
        ]);
    }

    /**
     * Check if current user has favorited specific items
     */
    public function checkFavorites(Request $request)
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'events' => [],
                'venues' => [],
                'artists' => [],
                'organisers' => [],
            ]);
        }

        $events = $user->favoriteEvents()->pluck('events.id');
        $venues = $user->favoriteVenues()->pluck('venues.id');
        $artists = $user->favoriteArtists()->pluck('artists.id');
        $organisers = $user->favoriteOrganisers()->pluck('organisers.id');

        return response()->json([
            'events' => $events,
            'venues' => $venues,
            'artists' => $artists,
            'organisers' => $organisers,
        ]);
    }
}
