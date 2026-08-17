<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeroGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_week_hero_includes_later_days_beyond_sixty_events(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-23 10:00:00', 'Africa/Johannesburg'));

        $venue = Venue::query()->create([
            'name' => 'Test Venue',
            'capacity' => 100,
        ]);

        for ($i = 0; $i < 65; $i++) {
            Event::query()->create([
                'name' => "Early Gig {$i}",
                'date' => now()->addDay()->toDateString(),
                'time' => sprintf('%02d:00', 10 + ($i % 10)),
                'venue_id' => $venue->id,
                'status' => 'upcoming',
            ]);
        }

        $lastDayGig = Event::query()->create([
            'name' => 'Sunday Closing Gig',
            'date' => now()->addDays(6)->toDateString(),
            'time' => '20:00',
            'venue_id' => $venue->id,
            'status' => 'upcoming',
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertViewHas('heroEventCatalog', function (array $catalog) use ($lastDayGig) {
                $week = $catalog[''][7] ?? [];
                $names = array_column($week, 'name');

                return in_array('Sunday Closing Gig', $names, true)
                    && ($names[0] ?? '') === 'Early Gig 0'
                    && ($names[array_key_last($names)] ?? '') === 'Sunday Closing Gig';
            });

        Carbon::setTestNow();
    }
}
