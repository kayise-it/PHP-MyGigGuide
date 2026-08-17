<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecentEventsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_artist_show_includes_five_most_recent_past_gigs_by_date(): void
    {
        $artist = Artist::query()->create([
            'stage_name' => 'Past Player',
            'claim_status' => 'approved',
        ]);

        $venue = Venue::query()->create([
            'name' => 'Historic Hall',
            'capacity' => 100,
        ]);

        $older = Event::query()->create([
            'name' => 'Older Gig',
            'date' => now()->subMonths(2)->toDateString(),
            'venue_id' => $venue->id,
            'status' => 'upcoming',
        ]);
        $older->artists()->attach($artist->id);

        $newer = Event::query()->create([
            'name' => 'Newer Gig',
            'date' => now()->subWeek()->toDateString(),
            'venue_id' => $venue->id,
            'status' => 'upcoming',
        ]);
        $newer->artists()->attach($artist->id);

        Event::query()->create([
            'name' => 'Cancelled Gig',
            'date' => now()->subDays(3)->toDateString(),
            'venue_id' => $venue->id,
            'status' => 'cancelled',
        ])->artists()->attach($artist->id);

        Event::query()->create([
            'name' => 'Still Upcoming',
            'date' => now()->addWeek()->toDateString(),
            'venue_id' => $venue->id,
            'status' => 'upcoming',
        ])->artists()->attach($artist->id);

        for ($i = 0; $i < 6; $i++) {
            $extra = Event::query()->create([
                'name' => "Extra Past {$i}",
                'date' => now()->subDays(10 + $i)->toDateString(),
                'venue_id' => $venue->id,
                'status' => 'upcoming',
            ]);
            $extra->artists()->attach($artist->id);
        }

        $response = $this->getJson('/api/v1/artists/'.$artist->id);

        $response->assertOk()
            ->assertJsonCount(5, 'data.recent_events')
            ->assertJsonPath('data.recent_events.0.id', $newer->id)
            ->assertJsonPath('data.recent_events.0.name', 'Newer Gig');
    }

    public function test_venue_show_includes_recent_past_gigs_at_venue_by_date(): void
    {
        $venue = Venue::query()->create([
            'name' => 'The Room',
            'capacity' => 80,
        ]);

        $otherVenue = Venue::query()->create([
            'name' => 'Elsewhere',
            'capacity' => 80,
        ]);

        $atVenue = Event::query()->create([
            'name' => 'Here Last Month',
            'date' => now()->subMonth()->toDateString(),
            'venue_id' => $venue->id,
            'status' => 'upcoming',
        ]);

        Event::query()->create([
            'name' => 'Wrong Venue',
            'date' => now()->subWeek()->toDateString(),
            'venue_id' => $otherVenue->id,
            'status' => 'upcoming',
        ]);

        $response = $this->getJson('/api/v1/venues/'.$venue->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data.recent_events')
            ->assertJsonPath('data.recent_events.0.id', $atVenue->id);
    }

    public function test_past_event_show_is_public_without_auth_even_when_status_is_upcoming(): void
    {
        $venue = Venue::query()->create([
            'name' => 'Archive Venue',
            'capacity' => 50,
        ]);

        $event = Event::query()->create([
            'name' => 'Finished Show',
            'date' => now()->subWeek()->toDateString(),
            'venue_id' => $venue->id,
            'status' => 'upcoming',
        ]);

        $this->getJson('/api/v1/events/'.$event->id)
            ->assertOk()
            ->assertJsonPath('data.id', $event->id)
            ->assertJsonPath('data.name', 'Finished Show');
    }

    public function test_cancelled_event_show_still_requires_owner(): void
    {
        $user = User::factory()->create();

        $venue = Venue::query()->create([
            'name' => 'Cancelled Venue',
            'capacity' => 50,
        ]);

        $event = Event::query()->create([
            'name' => 'Cancelled Show',
            'date' => now()->subWeek()->toDateString(),
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'cancelled',
        ]);

        $this->getJson('/api/v1/events/'.$event->id)->assertNotFound();
    }
}
