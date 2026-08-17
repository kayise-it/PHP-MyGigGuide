<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoriteUpdatesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_favorite_updates_returns_upcoming_gigs_for_saved_venue_and_artist(): void
    {
        $user = User::factory()->create();

        $venue = Venue::query()->create([
            'name' => 'Windmill Pub',
            'capacity' => 100,
        ]);

        $artist = Artist::query()->create([
            'stage_name' => 'Shades1',
        ]);

        $venueGig = Event::query()->create([
            'name' => 'Pub Night',
            'date' => now()->addDays(5),
            'venue_id' => $venue->id,
            'status' => 'upcoming',
        ]);

        $artistGig = Event::query()->create([
            'name' => 'Band Set',
            'date' => now()->addDays(10),
            'venue_id' => $venue->id,
            'status' => 'upcoming',
        ]);
        $artistGig->artists()->attach($artist->id);

        Event::query()->create([
            'name' => 'Unrelated Gig',
            'date' => now()->addDays(6),
            'venue_id' => Venue::query()->create(['name' => 'Other Place', 'capacity' => 50])->id,
            'status' => 'upcoming',
        ]);

        $user->favoriteVenues()->attach($venue->id);
        $user->favoriteArtists()->attach($artist->id);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me/favorites/updates');

        $response->assertOk()
            ->assertJsonPath('summary.total', 2)
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$venueGig->id, $artistGig->id], $ids);
    }

    public function test_favorite_updates_since_filters_new_listings_but_keeps_saved_event_reminders(): void
    {
        $user = User::factory()->create();

        $venue = Venue::query()->create([
            'name' => 'Windmill Pub',
            'capacity' => 100,
        ]);

        $oldSaved = Event::query()->create([
            'name' => 'Saved Soon',
            'date' => now()->addDays(3),
            'venue_id' => $venue->id,
            'status' => 'upcoming',
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(30),
        ]);

        $newListing = Event::query()->create([
            'name' => 'Fresh Listing',
            'date' => now()->addDays(12),
            'venue_id' => $venue->id,
            'status' => 'upcoming',
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        $user->favoriteVenues()->attach($venue->id);
        $user->favoriteEvents()->attach($oldSaved->id);

        Sanctum::actingAs($user);

        $since = now()->subDay()->toIso8601String();

        $response = $this->getJson('/api/v1/me/favorites/updates?since='.urlencode($since));

        $response->assertOk()
            ->assertJsonPath('summary.total', 2)
            ->assertJsonPath('summary.new_count', 1)
            ->assertJsonPath('summary.reminder_count', 1);

        $payload = collect($response->json('data'))->keyBy('id');
        $this->assertFalse($payload[$newListing->id]['is_reminder']);
        $this->assertTrue($payload[$oldSaved->id]['is_reminder']);
    }

    public function test_favorite_updates_requires_auth(): void
    {
        $this->getJson('/api/v1/me/favorites/updates')->assertUnauthorized();
    }
}
