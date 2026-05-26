<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Rating;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorySortApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_artists_index_sorts_by_rating(): void
    {
        $low = Artist::query()->create(['stage_name' => 'Low Rated', 'genre' => 'rock']);
        $high = Artist::query()->create(['stage_name' => 'High Rated', 'genre' => 'rock']);
        $user = User::factory()->create();

        Rating::query()->create([
            'user_id' => $user->id,
            'rateable_type' => Artist::class,
            'rateable_id' => $low->id,
            'rating' => 2,
        ]);
        Rating::query()->create([
            'user_id' => $user->id,
            'rateable_type' => Artist::class,
            'rateable_id' => $high->id,
            'rating' => 5,
        ]);

        $response = $this->getJson('/api/v1/artists?sort=rating&per_page=50');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('stage_name')->all();
        $this->assertSame('High Rated', $names[0]);
    }

    public function test_venues_index_sorts_by_events_count(): void
    {
        $owner = User::factory()->create();
        $busy = Venue::query()->create([
            'name' => 'Busy Venue',
            'contact_email' => 'busy@example.com',
            'user_id' => $owner->id,
            'owner_id' => $owner->id,
            'owner_type' => 'user',
        ]);
        $quiet = Venue::query()->create([
            'name' => 'Quiet Venue',
            'contact_email' => 'quiet@example.com',
            'user_id' => $owner->id,
            'owner_id' => $owner->id,
            'owner_type' => 'user',
        ]);

        Event::query()->create([
            'name' => 'Gig A',
            'date' => now()->addDay(),
            'time' => '20:00',
            'venue_id' => $busy->id,
            'owner_id' => $owner->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);
        Event::query()->create([
            'name' => 'Gig B',
            'date' => now()->addDays(2),
            'time' => '20:00',
            'venue_id' => $busy->id,
            'owner_id' => $owner->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);

        $response = $this->getJson('/api/v1/venues?sort=events&per_page=50');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame('Busy Venue', $names[0]);
        $this->assertGreaterThan(
            collect($response->json('data'))->firstWhere('name', 'Quiet Venue')['events_count'],
            collect($response->json('data'))->firstWhere('name', 'Busy Venue')['events_count'],
        );
    }
}
