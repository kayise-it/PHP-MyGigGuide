<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistPostedEventsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_artist_show_includes_posted_events_for_page_owner(): void
    {
        $user = User::factory()->create();

        $artist = Artist::query()->create([
            'stage_name' => 'My Band',
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        $venue = Venue::query()->create([
            'name' => 'Test Venue',
            'capacity' => 100,
        ]);

        $posted = Event::query()->create([
            'name' => 'Listed By Band',
            'date' => now()->addWeek(),
            'venue_id' => $venue->id,
            'owner_id' => $artist->id,
            'owner_type' => 'artist',
            'status' => 'upcoming',
        ]);

        Event::query()->create([
            'name' => 'Someone Else Listed',
            'date' => now()->addWeeks(2),
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);

        $response = $this->getJson('/api/v1/artists/'.$artist->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data.posted_events')
            ->assertJsonPath('data.posted_events.0.id', $posted->id)
            ->assertJsonPath('data.posted_events.0.name', 'Listed By Band');
    }

    public function test_artist_show_posted_events_respects_owner_type_variants(): void
    {
        $user = User::factory()->create();

        $artist = Artist::query()->create([
            'stage_name' => 'Legacy Owner Type',
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        $venue = Venue::query()->create([
            'name' => 'Test Venue',
            'capacity' => 100,
        ]);

        $posted = Event::query()->create([
            'name' => 'Fully Qualified Owner',
            'date' => now()->addDays(3),
            'venue_id' => $venue->id,
            'owner_id' => $artist->id,
            'owner_type' => Artist::class,
            'status' => 'upcoming',
        ]);

        $response = $this->getJson('/api/v1/artists/'.$artist->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data.posted_events')
            ->assertJsonPath('data.posted_events.0.id', $posted->id);
    }
}
