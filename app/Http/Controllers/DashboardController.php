<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\Rating;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the appropriate dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }
// Redirect to role-specific dashboard
        if ($user->hasRole('artist')) {
            return $this->artistDashboard();
        } elseif ($user->hasRole('organiser')) {
            return $this->organiserDashboard();
        } elseif ($user->hasRole('venue_owner')) {
            return $this->venueOwnerDashboard();
        } elseif ($user->hasRole('admin') || $user->hasRole('superuser')) {
            return $this->adminDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    /**
     * Artist Dashboard
     */
    public function artistDashboard()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }
$artist = $user->artist;

        if (! $artist) {
            // Create artist profile if it doesn't exist
            $artist = Artist::create([
                'user_id' => $user->id,
                'stage_name' => $user->name,
                'real_name' => $user->name,
                'genre' => 'Unknown',
                'bio' => 'Artist bio coming soon...',
            ]);
        }
// Get all events where artist is involved (either as owner or performer)
        $allEvents = Event::where(function ($query) use ($artist) {
            // Events owned by the artist
            $query->where(function ($subQuery) use ($artist) {
                $subQuery->where('owner_type', 'artist')
                    ->where('owner_id', $artist->id);
            })
            // OR events where artist is performing
                ->orWhereHas('artists', function ($artistQuery) use ($artist) {
                    $artistQuery->where('artist_id', $artist->id);
                });
        })
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'desc')
            ->get();

        // Get upcoming events (scheduled and future dates)
        $upcomingEvents = $allEvents->filter(function ($event) {
            return $event->status === 'upcoming' && $event->date >= now();
        })->sortBy('date')->take(5);

        // Get past events
        $pastEvents = $allEvents->filter(function ($event) {
            return $event->status === 'completed' || $event->date < now();
        })->sortByDesc('date')->take(5);

        // Get venues owned by the user (via user_id or venue_owners table)
        try {
            $venues = Venue::where('user_id', $user->id)
                ->orWhereHas('owners', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();
        } catch (\Exception $e) {
            // Fallback if venue_owners table doesn't exist
            $venues = Venue::where('user_id', $user->id)->get();
        }

        // Get ratings for the artist
        $ratings = Rating::where('rateable_type', 'artist')
            ->where('rateable_id', $artist->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $statsEnabled = config('features.dashboard_stats', true);
        $stats = [];

        if ($statsEnabled) {
            $stats = [
                'total_events' => $allEvents->count(),
                'upcoming_events' => $upcomingEvents->count(),
                'venues_owned' => $venues->count(),
                'average_rating' => $ratings->avg('rating') ?? 0,
                'total_ratings' => $ratings->count(),
            ];
        }

        return view('dashboard.artist', compact('artist', 'upcomingEvents', 'pastEvents', 'venues', 'ratings', 'stats', 'statsEnabled'));
    }

    /**
     * Organiser Dashboard
     */
    public function organiserDashboard()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $organiser = $user->organiser;

        if (! $organiser) {
            // Create organiser profile if it doesn't exist
            $organiser = Organiser::create([
                'user_id' => $user->id,
                'organisation_name' => $user->name.' Events',
                'contact_email' => $user->email,
                'description' => 'Event organiser profile coming soon...',
            ]);
        }

        // Get all organiser's events
        $allEvents = Event::where('owner_type', 'organiser')
            ->where('owner_id', $organiser->id)
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'desc')
            ->get();

        // Get upcoming events (scheduled and future dates)
        $upcomingEvents = $allEvents->filter(function ($event) {
            return $event->status === 'upcoming' && $event->date >= now();
        })->sortBy('date')->take(5);

        // Get past events
        $pastEvents = $allEvents->filter(function ($event) {
            return $event->status === 'completed' || $event->date < now();
        })->sortByDesc('date')->take(5);

        // Get venues owned by the user (not the organiser profile)
        // Get venues owned by the organiser (via user_id or venue_owners table)
        try {
            $venues = Venue::where('user_id', $user->id)
                ->orWhereHas('owners', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();
        } catch (\Exception $e) {
            // Fallback if venue_owners table doesn't exist
            $venues = Venue::where('user_id', $user->id)->get();
        }

        // Get ratings for the organiser
        $ratings = Rating::where('rateable_type', 'organiser')
            ->where('rateable_id', $organiser->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $statsEnabled = config('features.dashboard_stats', true);
        $stats = [];

        if ($statsEnabled) {
            $stats = [
                'total_events' => $allEvents->count(),
                'upcoming_events' => $upcomingEvents->count(),
                'venues_owned' => $venues->count(),
                'average_rating' => $ratings->avg('rating') ?? 0,
                'total_ratings' => $ratings->count(),
            ];
        }

        return view('dashboard.organiser', compact('organiser', 'upcomingEvents', 'pastEvents', 'venues', 'ratings', 'stats', 'statsEnabled'));
    }

    /**
     * Venue Owner Dashboard
     */
    public function venueOwnerDashboard()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Get venue owner's venues (via user_id or venue_owners table)
        try {
            $venues = Venue::where('user_id', $user->id)
                ->orWhereHas('owners', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();
        } catch (\Exception $e) {
            // Fallback if venue_owners table doesn't exist
            $venues = Venue::where('user_id', $user->id)->get();
        }

        // Get events at these venues
        $venueIds = $venues->pluck('id');
        $upcomingEvents = Event::whereIn('venue_id', $venueIds)
            ->where('status', 'upcoming')
            ->whereDate('date', '>=', now()->format('Y-m-d'))
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'asc')
            ->get();

        $pastEvents = Event::whereIn('venue_id', $venueIds)
            ->where('status', 'completed')
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        // Get ratings for venues
        $ratings = Rating::where('rateable_type', 'venue')
            ->whereIn('rateable_id', $venueIds)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $statsEnabled = config('features.dashboard_stats', true);
        $stats = [];

        if ($statsEnabled) {
            $stats = [
                'total_venues' => $venues->count(),
                'total_events' => $upcomingEvents->count() + $pastEvents->count(),
                'upcoming_events' => $upcomingEvents->count(),
                'average_rating' => $ratings->avg('rating') ?? 0,
                'total_ratings' => $ratings->count(),
            ];
        }

        return view('dashboard.venue-owner', compact('venues', 'upcomingEvents', 'pastEvents', 'ratings', 'stats', 'statsEnabled'));
    }

    /**
     * Admin Dashboard
     */
    public function adminDashboard()
    {
        $statsEnabled = config('features.dashboard_stats', true);
        $stats = [];

        if ($statsEnabled) {
            $stats = [
                'total_users' => User::count(),
                'total_artists' => Artist::count(),
                'total_organisers' => Organiser::count(),
                'total_venues' => Venue::count(),
                'total_events' => Event::count(),
                'upcoming_events' => Event::where('status', 'upcoming')
                    ->whereDate('date', '>=', now()->format('Y-m-d'))
                    ->count(),
            ];
        }

        $recentEvents = Event::with(['venue', 'artists', 'owner'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentUsers = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard.admin', compact('stats', 'recentEvents', 'recentUsers', 'statsEnabled'));
    }

    /**
     * Regular User Dashboard
     */
    public function userDashboard()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Get user's favorite events
        $favoriteEvents = Event::whereHas('favoritedBy', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'asc')
            ->get();

        // Get user's favorite artists
        $favoriteArtists = Artist::whereHas('favoritedBy', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['user', 'ratings'])
            ->orderBy('stage_name', 'asc')
            ->get();

        // Get upcoming events near user (sample - would need location logic)
        $upcomingEvents = Event::where('status', 'upcoming')
            ->whereDate('date', '>=', now()->format('Y-m-d'))
            ->with(['venue', 'artists', 'owner'])
            ->orderBy('date', 'asc')
            ->limit(6)
            ->get();

        // Get user's ratings
        $userRatings = Rating::where('user_id', $user->id)
            ->with('rateable')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get user's owned venues with pagination (via user_id or venue_owners table)
        try {
            $userVenues = Venue::where('user_id', $user->id)
                ->orWhereHas('owners', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->withCount('events')
                ->with(['events' => function ($query) {
                    $query->where('status', 'upcoming')
                        ->whereDate('date', '>=', now()->format('Y-m-d'))
                        ->orderBy('date', 'asc')
                        ->limit(3);
                }])
                ->orderBy('created_at', 'desc')
                ->paginate(6);
        } catch (\Exception $e) {
            // Fallback if venue_owners table doesn't exist
            $userVenues = Venue::where('user_id', $user->id)
                ->withCount('events')
                ->with(['events' => function ($query) {
                    $query->where('status', 'upcoming')
                        ->whereDate('date', '>=', now()->format('Y-m-d'))
                        ->orderBy('date', 'asc')
                        ->limit(3);
                }])
                ->orderBy('created_at', 'desc')
                ->paginate(6);
        }

        $statsEnabled = config('features.dashboard_stats', true);
        $stats = [];

        if ($statsEnabled) {
            // Calculate venues owned count (via user_id or venue_owners table)
            $venuesOwnedCount = 0;
            try {
                $venuesOwnedCount = Venue::where('user_id', $user->id)
                    ->orWhereHas('owners', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })
                    ->count();
            } catch (\Exception $e) {
                $venuesOwnedCount = Venue::where('user_id', $user->id)->count();
            }

            $stats = [
                'favorite_events' => $favoriteEvents->count(),
                'favorite_artists' => $favoriteArtists->count(),
                'upcoming_events' => $upcomingEvents->count(),
                'ratings_given' => $userRatings->count(),
                'venues_owned' => $venuesOwnedCount,
            ];
        }

        return view('dashboard.user', compact('favoriteEvents', 'favoriteArtists', 'upcomingEvents', 'userRatings', 'userVenues', 'stats', 'statsEnabled'));
    }
}
