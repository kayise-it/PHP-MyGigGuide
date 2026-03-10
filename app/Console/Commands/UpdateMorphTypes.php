<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Rating;
use App\Models\Venue;
use Illuminate\Console\Command;

class UpdateMorphTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'morph:update-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update morph types from full class names to simple strings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating morph types...');

        // Update Events table
        $this->updateEvents();

        // Update Venues table
        $this->updateVenues();

        // Update Ratings table
        $this->updateRatings();

        $this->info('Morph types update completed!');
    }

    private function updateEvents()
    {
        $this->info('Updating Events table...');

        $events = Event::where('owner_type', 'App\Models\Artist')->get();
        $this->info("Found {$events->count()} events with App\Models\Artist");
        foreach ($events as $event) {
            $event->update(['owner_type' => 'artist']);
        }

        $events = Event::where('owner_type', 'App\Models\Organiser')->get();
        $this->info("Found {$events->count()} events with App\Models\Organiser");
        foreach ($events as $event) {
            $event->update(['owner_type' => 'organiser']);
        }

        $events = Event::where('owner_type', 'App\Models\User')->get();
        $this->info("Found {$events->count()} events with App\Models\User");
        foreach ($events as $event) {
            $event->update(['owner_type' => 'user']);
        }
    }

    private function updateVenues()
    {
        $this->info('Updating Venues table...');

        $venues = Venue::where('owner_type', 'App\Models\Artist')->get();
        $this->info("Found {$venues->count()} venues with App\Models\Artist");
        foreach ($venues as $venue) {
            $venue->update(['owner_type' => 'artist']);
        }

        $venues = Venue::where('owner_type', 'App\Models\Organiser')->get();
        $this->info("Found {$venues->count()} venues with App\Models\Organiser");
        foreach ($venues as $venue) {
            $venue->update(['owner_type' => 'organiser']);
        }

        $venues = Venue::where('owner_type', 'App\Models\User')->get();
        $this->info("Found {$venues->count()} venues with App\Models\User");
        foreach ($venues as $venue) {
            $venue->update(['owner_type' => 'user']);
        }
    }

    private function updateRatings()
    {
        $this->info('Updating Ratings table...');

        $ratings = Rating::where('rateable_type', 'App\Models\Event')->get();
        $this->info("Found {$ratings->count()} ratings with App\Models\Event");
        foreach ($ratings as $rating) {
            $rating->update(['rateable_type' => 'event']);
        }

        $ratings = Rating::where('rateable_type', 'App\Models\Artist')->get();
        $this->info("Found {$ratings->count()} ratings with App\Models\Artist");
        foreach ($ratings as $rating) {
            $rating->update(['rateable_type' => 'artist']);
        }

        $ratings = Rating::where('rateable_type', 'App\Models\Venue')->get();
        $this->info("Found {$ratings->count()} ratings with App\Models\Venue");
        foreach ($ratings as $rating) {
            $rating->update(['rateable_type' => 'venue']);
        }

        $ratings = Rating::where('rateable_type', 'App\Models\Organiser')->get();
        $this->info("Found {$ratings->count()} ratings with App\Models\Organiser");
        foreach ($ratings as $rating) {
            $rating->update(['rateable_type' => 'organiser']);
        }
    }
}
