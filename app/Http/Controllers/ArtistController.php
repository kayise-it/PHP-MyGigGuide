<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Artist;
use Illuminate\Support\Facades\Auth;

class ArtistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Artist::query();

        // Search by stage name, real name, or related user fields (substring match)
        if (request()->filled('search')) {
            $searchTerm = trim(request('search'));
            $query->where(function ($q) use ($searchTerm) {
                $q->where('stage_name', 'like', "%{$searchTerm}%")
                  ->orWhere('real_name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function ($uq) use ($searchTerm) {
                      $uq->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('username', 'like', "%{$searchTerm}%")
                         ->orWhere('email', 'like', "%{$searchTerm}%");
                  });
            });

            // Prioritize artists whose names START with the search term
            // Then sort the rest normally. This keeps “A …” above names with numbers that merely contain "a".
            $safe = str_replace(['%', '_'], ['\\%', '\\_'], $searchTerm);
            $query->orderByRaw("CASE WHEN stage_name LIKE ? THEN 0 ELSE 1 END", ["{$safe}%"]);
        }

        // Filter by genre if provided
        if (request()->filled('genre')) {
            $query->where('genre', request('genre'));
        }

        // Sorting options
        $sortBy = request('sort', 'name');
        switch ($sortBy) {
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            case 'rating':
                if (method_exists(Artist::class, 'ratings')) {
                    $query->withAvg('ratings', 'rating')->orderByDesc('ratings_avg_rating');
                } else {
                    $query->orderByDesc('created_at');
                }
                break;
            case 'events':
                if (method_exists(Artist::class, 'events')) {
                    $query->withCount('events')->orderByDesc('events_count');
                } else {
                    $query->orderByDesc('created_at');
                }
                break;
            case 'name':
            default:
                $query->orderBy('stage_name');
                break;
        }

        $artists = $query->paginate(12)->withQueryString();

        // For AJAX requests, return only the results partial to avoid duplicating the whole page
        if (request()->ajax()) {
            return view('artists._results', compact('artists'));
        }

        return view('artists.index', compact('artists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Artist $artist)
    {
        // Load YouTube videos
        $artist->load('youtubeVideos');
        
        // Compute average rating if ratings relation exists; default to 0
        $ratingAvg = 0.0;
        if (method_exists($artist, 'ratings')) {
            $ratingAvg = round((float) ($artist->ratings()->avg('rating') ?? 0), 1);
        }

        // Upcoming events where this artist is performing or is the owner
        $upcomingEvents = \App\Models\Event::with(['venue'])
            ->where('status', 'upcoming')
            ->whereDate('date', '>=', now()->toDateString())
            ->where(function ($q) use ($artist) {
                $q->where(function($own) use ($artist) {
                        $own->where('owner_type', 'artist')->where('owner_id', $artist->id);
                    })
                  ->orWhereHas('artists', function ($aq) use ($artist) {
                        $aq->where('artist_id', $artist->id);
                    });
            })
            ->orderBy('date', 'asc')
            ->take(10)
            ->get();

        return view('artists.show', [
            'artist' => $artist,
            'ratingAvg' => $ratingAvg,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
// TODO: implement artist edit form
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Dispute an artist profile claim.
     */
    public function dispute(Artist $artist)
    {
        // Raise dispute flag
        $artist->update([
            'dispute_raised' => true,
            'dispute_raised_at' => now(),
            'claim_status' => 'disputed',
        ]);

        return redirect()->route('contact.index')
            ->with('success', 'Dispute raised successfully. Our team will review your claim. You can also contact us directly via the contact form.');
    }

    /**
     * Quick create an artist with minimal information
     */
    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'stage_name' => 'required|string|max:255',
            'genre' => 'nullable|string|max:255',
        ]);

        // Quick-create artists from event forms should start as UNCLAIMED.
        // Do not link them to the currently authenticated user; they can
        // be claimed later via the standard claim flow.
        $artist = Artist::create([
            'stage_name' => $validated['stage_name'],
            'genre' => $validated['genre'] ?? 'Unknown',
            'user_id' => null,
            'contact_email' => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $artist->id,
                'stage_name' => $artist->stage_name,
                'genre' => $artist->genre,
            ]);
        }

        return back()->with('success', 'Artist created');
    }
}
