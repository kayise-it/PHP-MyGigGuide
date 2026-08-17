<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Category;
use App\Models\Event;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    private const HERO_DAY_PRESETS = [1, 7, 30, 90];

    /** Max hero carousel rows per preset (Quicket-heavy weeks exceed 60 quickly). */
    private const HERO_EVENT_LIMITS = [
        1 => 100,
        7 => 500,
        30 => 1200,
        90 => 2000,
    ];

    /**
     * Display the home page.
     */
    public function index(Request $request)
    {
        $now = Carbon::now();
        $heroDays = (int) $request->get('days', 7);
        if (! in_array($heroDays, self::HERO_DAY_PRESETS, true)) {
            $heroDays = 7;
        }

        $categorySlug = $request->filled('category')
            ? trim((string) $request->get('category'))
            : null;

        $browseSort = $request->get('browse_sort', 'events');
        if (! in_array($browseSort, ['events', 'rating'], true)) {
            $browseSort = 'events';
        }

        $categories = Category::where('is_active', true)
            ->orderedForDisplay()
            ->get();

        $heroEventCatalog = $this->heroEventCatalog($now, $categories);

        $mapEvents = Event::with(['venue', 'artists', 'owner', 'categories'])
            ->where($this->upcomingScope($now, 90, $categorySlug))
            ->whereHas('venue', function ($q) {
                $q->whereNotNull('latitude')
                    ->whereNotNull('longitude');
            })
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->limit(200)
            ->get();

        $artistsByEvents = $this->browseArtists('events');
        $artistsByRating = $this->browseArtists('rating');
        $venuesByEvents = $this->browseVenues('events');
        $venuesByRating = $this->browseVenues('rating');

        $homeQuery = array_filter([
            'days' => $heroDays !== 7 ? $heroDays : null,
            'category' => $categorySlug,
        ], fn ($v) => $v !== null && $v !== '');

        return view('home', compact(
            'heroEventCatalog',
            'artistsByEvents',
            'artistsByRating',
            'venuesByEvents',
            'venuesByRating',
            'mapEvents',
            'categories',
            'heroDays',
            'categorySlug',
            'browseSort',
            'homeQuery',
        ));
    }

    private function upcomingScope(Carbon $now, int $heroDays, ?string $categorySlug): \Closure
    {
        // Inclusive window: today + (heroDays - 1) calendar days (matches app diary range).
        $windowEnd = $now->copy()->startOfDay()->addDays($heroDays - 1);

        return function ($query) use ($now, $windowEnd, $categorySlug) {
            $query->where('status', 'upcoming')
                ->where(function ($q) use ($now) {
                    $q->where('date', '>', $now)
                        ->orWhere(function ($inner) use ($now) {
                            $inner->whereDate('date', '=', $now)
                                ->whereTime('time', '>=', $now->format('H:i:s'));
                        });
                })
                ->whereDate('date', '<=', $windowEnd);

            if ($categorySlug) {
                $query->whereHas('categories', function ($categoryQuery) use ($categorySlug) {
                    $categoryQuery->where('slug', $categorySlug);
                });
            }
        };
    }

    private function heroEventCatalog(Carbon $now, $categories): array
    {
        $catalog = [];
        $slugs = [''];

        foreach ($categories as $category) {
            $slugs[] = $category->slug;
        }

        foreach ($slugs as $slug) {
            $categorySlug = $slug === '' ? null : $slug;
            $catalog[$slug] = collect(self::HERO_DAY_PRESETS)
                ->mapWithKeys(fn (int $days) => [
                    $days => $this->serializeHeroEvents(
                        $this->heroEvents($now, $days, $categorySlug)
                    ),
                ])
                ->all();
        }

        return $catalog;
    }

    private function serializeHeroEvents($events): array
    {
        return $events->map(function (Event $event) {
            $parts = collect([
                $event->date->format('D j M'),
                $event->time ? Carbon::parse($event->time)->format('H:i') : null,
                $event->venue?->name,
            ])->filter()->values();

            return [
                'url' => route('events.show', $event),
                'name' => $event->name,
                'line' => $parts->join(' · '),
                'venue' => $event->venue?->name,
                'poster' => $event->poster
                    ? Storage::url($event->poster_card ?: $event->poster)
                    : null,
                'poster_full' => $event->poster ? Storage::url($event->poster) : null,
            ];
        })->values()->all();
    }

    private function heroEvents(Carbon $now, int $heroDays, ?string $categorySlug)
    {
        return Event::with(['venue', 'categories'])
            ->where($this->upcomingScope($now, $heroDays, $categorySlug))
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->limit(self::HERO_EVENT_LIMITS[$heroDays] ?? 500)
            ->get();
    }

    private function browseArtists(string $sort)
    {
        $query = Artist::with('user')
            ->withCount('events')
            ->withAvg('ratings', 'rating');

        if ($sort === 'rating') {
            $query->orderByDesc('ratings_avg_rating')->orderByDesc('events_count');
        } else {
            $query->orderByDesc('events_count')->orderByDesc('ratings_avg_rating');
        }

        return $query->limit(8)->get();
    }

    private function browseVenues(string $sort)
    {
        $query = Venue::with('owner')
            ->withCount('events')
            ->withAvg('ratings', 'rating');

        if ($sort === 'rating') {
            $query->orderByDesc('ratings_avg_rating')->orderByDesc('events_count');
        } else {
            $query->orderByDesc('events_count')->orderByDesc('ratings_avg_rating');
        }

        return $query->limit(8)->get();
    }

    /**
     * Display the map page.
     */
    public function map()
    {
        $now = Carbon::now();
        $endOfNextMonth = $now->copy()->addMonths(1)->endOfMonth();

        $events = Event::with(['venue', 'artists', 'owner', 'categories'])
            ->where('status', 'upcoming')
            ->where(function ($query) use ($now) {
                $query->where('date', '>', $now)
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('date', '=', $now)
                            ->whereTime('time', '>=', $now->format('H:i:s'));
                    });
            })
            ->whereDate('date', '<=', $endOfNextMonth)
            ->whereHas('venue', function ($q) {
                $q->whereNotNull('latitude')
                    ->whereNotNull('longitude');
            })
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->get();

        $venues = Venue::with('owner')
            ->orderBy('created_at', 'desc')
            ->get();

        $categories = Category::where('is_active', true)
            ->orderedForDisplay()
            ->get();

        return view('map', compact('events', 'venues', 'categories'));
    }

    /**
     * Display the test map page.
     */
    public function testMap()
    {
        $events = Event::with(['venue', 'artists', 'owner'])
            ->where('status', 'upcoming')
            ->orderBy('date', 'asc')
            ->get();

        return view('test-map', compact('events'));
    }
}
