<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Laratrust\Models\Role;
use Tests\TestCase;

class EventStoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_cannot_create_event(): void
    {
        $response = $this->postJson('/api/v1/events', [
            'name' => 'Test Gig',
            'date' => now()->addDay()->toDateString(),
            'time' => '20:00',
            'venue_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_without_permission_cannot_create_event(): void
    {
        $user = $this->makeVerifiedUser();
        $limited = Role::firstOrCreate(
            ['name' => 'limited_test'],
            ['display_name' => 'Limited test', 'description' => 'No event create']
        );
        $limited->syncPermissions([]);
        $user->syncRoles([$limited->name]);

        $venue = $this->makeVenue($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/events', [
            'name' => 'Blocked Gig',
            'date' => now()->addDay()->toDateString(),
            'time' => '20:00',
            'venue_id' => $venue->id,
        ]);

        $response->assertForbidden();
    }

    public function test_member_with_create_events_can_create_event(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);

        $venue = $this->makeVenue($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/events', [
            'name' => 'API Test Gig',
            'description' => 'Created via API test',
            'date' => now()->addDays(3)->toDateString(),
            'time' => '19:30',
            'price' => 50,
            'venue_id' => $venue->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'API Test Gig')
            ->assertJsonPath('message', 'Event created successfully.');

        $this->assertDatabaseHas('events', [
            'name' => 'API Test Gig',
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);
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
}
