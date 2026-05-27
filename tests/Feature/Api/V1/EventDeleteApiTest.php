<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventDeleteApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_cannot_delete_event(): void
    {
        $event = $this->makeOwnedEvent();

        $response = $this->deleteJson("/api/v1/events/{$event->id}");

        $response->assertUnauthorized();
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_non_owner_cannot_delete_event(): void
    {
        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $event = $this->makeOwnedEvent($owner);

        $other = $this->makeVerifiedUser();
        $other->syncRoles(['user']);
        Sanctum::actingAs($other);

        $response = $this->deleteJson("/api/v1/events/{$event->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $event->id]);
    }

    public function test_owner_can_delete_event(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $event = $this->makeOwnedEvent($user);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/events/{$event->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Event deleted successfully.');

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    private function makeVerifiedUser(): User
    {
        return User::factory()->create([
            'username' => 'apitest_'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function makeVenue(User $user): Venue
    {
        return Venue::query()->create([
            'name' => 'Test Venue '.uniqid(),
            'contact_email' => 'venue_'.uniqid().'@example.com',
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
        ]);
    }

    private function makeOwnedEvent(?User $user = null): Event
    {
        $user ??= $this->makeVerifiedUser();
        $venue = $this->makeVenue($user);

        return Event::query()->create([
            'name' => 'Original Gig',
            'date' => now()->addDay()->toDateString(),
            'time' => '20:00',
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);
    }
}
