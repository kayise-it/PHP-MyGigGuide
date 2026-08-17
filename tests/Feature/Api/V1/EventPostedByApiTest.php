<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPostedByApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_event_show_includes_posted_by_for_user_owner(): void
    {
        $user = User::factory()->create([
            'name' => 'Dave Smith',
            'username' => 'dave',
        ]);

        $venue = Venue::query()->create([
            'name' => 'Test Venue',
            'capacity' => 100,
        ]);

        $event = Event::query()->create([
            'name' => 'Friday Jazz',
            'date' => now()->addWeek(),
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);

        $response = $this->getJson('/api/v1/events/'.$event->id);

        $response->assertOk()
            ->assertJsonPath('data.posted_by.name', 'Dave Smith')
            ->assertJsonPath('data.posted_by.username', 'dave')
            ->assertJsonPath('data.posted_by.owner_type', 'user')
            ->assertJsonPath('data.posted_by.via', null);
    }

    public function test_event_show_includes_via_for_artist_owner(): void
    {
        $user = User::factory()->create([
            'name' => 'Dave Smith',
            'username' => 'dave',
        ]);

        $artist = Artist::query()->create([
            'stage_name' => 'My Band',
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        $venue = Venue::query()->create([
            'name' => 'Test Venue',
            'capacity' => 100,
        ]);

        $event = Event::query()->create([
            'name' => 'Band Night',
            'date' => now()->addWeek(),
            'venue_id' => $venue->id,
            'owner_id' => $artist->id,
            'owner_type' => 'artist',
            'status' => 'upcoming',
        ]);

        $response = $this->getJson('/api/v1/events/'.$event->id);

        $response->assertOk()
            ->assertJsonPath('data.posted_by.name', 'Dave Smith')
            ->assertJsonPath('data.posted_by.via', 'My Band')
            ->assertJsonPath('data.posted_by.owner_type', 'artist')
            ->assertJsonPath('data.posted_by.page.type', 'artist')
            ->assertJsonPath('data.posted_by.page.id', $artist->id)
            ->assertJsonPath('data.posted_by.page.name', 'My Band');
    }

    public function test_event_show_hides_posted_by_for_superuser(): void
    {
        $user = User::factory()->create([
            'name' => 'Dave Admin',
            'username' => 'dave',
        ]);
        $user->addRole('superuser');

        $venue = Venue::query()->create([
            'name' => 'Test Venue',
            'capacity' => 100,
        ]);

        $event = Event::query()->create([
            'name' => 'Admin Gig',
            'date' => now()->addWeek(),
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);

        $response = $this->getJson('/api/v1/events/'.$event->id);

        $response->assertOk()
            ->assertJsonPath('data.posted_by', null);
    }

    public function test_event_show_resolves_legacy_misfiled_artist_owner(): void
    {
        $user = User::factory()->create([
            'name' => 'DJ Dave',
            'username' => 'djdave',
        ]);

        $artist = Artist::query()->create([
            'stage_name' => 'Danger Daveed',
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        $venue = Venue::query()->create([
            'name' => 'Test Venue',
            'capacity' => 100,
        ]);

        $event = Event::query()->create([
            'name' => 'Legacy Owner Row',
            'date' => now()->addWeek(),
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'artist',
            'status' => 'upcoming',
        ]);

        $response = $this->getJson('/api/v1/events/'.$event->id);

        $response->assertOk()
            ->assertJsonPath('data.posted_by.via', 'Danger Daveed')
            ->assertJsonPath('data.posted_by.page.id', $artist->id);
    }
}
