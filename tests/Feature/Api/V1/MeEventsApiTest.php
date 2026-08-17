<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeEventsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_me_events_lists_events_owned_by_user(): void
    {
        $user = User::factory()->create();
        $user->addRole('user');

        $venue = Venue::query()->create([
            'name' => 'Test Venue',
            'capacity' => 100,
        ]);

        $mine = Event::query()->create([
            'name' => 'My Gig',
            'date' => now()->addWeek(),
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'active',
        ]);

        Event::query()->create([
            'name' => 'Someone Else',
            'date' => now()->addWeek(),
            'venue_id' => $venue->id,
            'owner_id' => $user->id + 99,
            'owner_type' => 'user',
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me/events');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id)
            ->assertJsonPath('data.0.name', 'My Gig')
            ->assertJsonPath('data.0.venue_name', 'Test Venue')
            ->assertJsonPath('data.0.user_can_edit', true)
            ->assertJsonPath('data.0.is_past', false);
    }

    public function test_me_events_requires_authentication(): void
    {
        $this->getJson('/api/v1/me/events')->assertUnauthorized();
    }
}
