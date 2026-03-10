<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Category;
use App\Models\Event;
use App\Models\Venue;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Display the home page.
     */
    public function index()
    {
        // in future we will adjust to handle loged in user show events based on likes, phase 2
        $now = Carbon::now();
        $endOfNextMonth = $now->copy()->addMonths(1)->endOfMonth();

        $events = Event::with(['venue', 'artists', 'owner', 'categories'])
            ->where('status', 'upcoming')
            ->where(function($query) use ($now) {
                // Show events from NOW onwards (comparing date + time)
                $query->where('date', '>', $now)
                      ->orWhere(function($q) use ($now) {
                          $q->whereDate('date', '=', $now)
                            ->whereTime('time', '>=', $now->format('H:i:s'));
                      });
            })
            ->whereDate('date', '<=', $endOfNextMonth)
            ->orderBy('date', 'asc')
            ->orderBy('time', 'asc')
            ->limit(6)
            ->get();

        // Larger dataset for the homepage map so markers always render
        $mapEvents = Event::with(['venue', 'artists', 'owner', 'categories'])
            ->where('status', 'upcoming')
            ->where(function($query) use ($now) {
                // Show events from NOW onwards (comparing date + time)
                $query->where('date', '>', $now)
                      ->orWhere(function($q) use ($now) {
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
            ->limit(200)
            ->get();

        $artists = Artist::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $venues = Venue::with('owner')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('home', compact('events', 'artists', 'venues', 'mapEvents', 'categories'));
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
            ->where(function($query) use ($now) {
                // Show events from NOW onwards (comparing date + time)
                $query->where('date', '>', $now)
                      ->orWhere(function($q) use ($now) {
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
            ->orderBy('sort_order')
            ->orderBy('name')
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
