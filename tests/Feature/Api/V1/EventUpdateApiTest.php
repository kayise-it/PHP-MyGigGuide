<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Laratrust\Models\Role;
use Tests\TestCase;

class EventUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_cannot_update_event(): void
    {
        $event = $this->makeOwnedEvent();

        $response = $this->putJson("/api/v1/events/{$event->id}", [
            'name' => 'Updated',
            'date' => now()->addDay()->toDateString(),
            'time' => '20:00',
            'venue_id' => $event->venue_id,
        ]);

        $response->assertUnauthorized();
    }

    public function test_non_owner_cannot_update_event(): void
    {
        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $event = $this->makeOwnedEvent($owner);

        $other = $this->makeVerifiedUser();
        $other->syncRoles(['user']);
        Sanctum::actingAs($other);

        $response = $this->putJson("/api/v1/events/{$event->id}", [
            'name' => 'Hijacked',
            'date' => now()->addDay()->toDateString(),
            'time' => '20:00',
            'venue_id' => $event->venue_id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('events', ['id' => $event->id, 'name' => $event->name]);
    }

    public function test_owner_can_update_event(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $event = $this->makeOwnedEvent($user);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/events/{$event->id}", [
            'name' => 'Updated Gig Title',
            'description' => 'New blurb',
            'date' => now()->addDays(2)->toDateString(),
            'time' => '21:00',
            'venue_id' => $event->venue_id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Gig Title')
            ->assertJsonPath('data.user_can_edit', true)
            ->assertJsonPath('message', 'Event updated successfully.');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Updated Gig Title',
            'description' => 'New blurb',
        ]);
    }

    public function test_owner_can_set_event_tiktok_link(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $event = $this->makeOwnedEvent($user);

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/events/{$event->id}", [
            'name' => $event->name,
            'date' => $event->date->toDateString(),
            'time' => '20:00',
            'venue_id' => $event->venue_id,
            'tiktok' => 'https://www.tiktok.com/@gignight',
        ])->assertOk()
            ->assertJsonPath('data.tiktok', 'https://www.tiktok.com/@gignight');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'tiktok' => 'https://www.tiktok.com/@gignight',
        ]);
    }

    public function test_owner_can_update_event_via_multipart_post_method_spoof(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $event = $this->makeOwnedEvent($user);

        Sanctum::actingAs($user);

        $response = $this->post("/api/v1/events/{$event->id}", [
            'name' => 'Updated via app multipart',
            'date' => now()->addDays(2)->toDateString(),
            'time' => '21:00',
            'venue_id' => $event->venue_id,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated via app multipart');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Updated via app multipart',
        ]);
    }

    public function test_show_includes_user_can_edit_for_owner(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $event = $this->makeOwnedEvent($user);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertOk()->assertJsonPath('data.user_can_edit', true);
    }

    public function test_show_user_can_edit_false_for_other_user(): void
    {
        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $event = $this->makeOwnedEvent($owner);

        $viewer = $this->makeVerifiedUser();
        $viewer->syncRoles(['user']);
        Sanctum::actingAs($viewer);

        $response = $this->getJson("/api/v1/events/{$event->id}");

        $response->assertOk()->assertJsonPath('data.user_can_edit', false);
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
