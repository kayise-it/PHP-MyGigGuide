<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laratrust\Models\Role;
use Laravel\Sanctum\Sanctum;
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

    public function test_plain_user_role_can_create_even_when_permission_row_missing(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);

        $role = Role::query()->where('name', 'user')->firstOrFail();
        $role->syncPermissions([
            'edit-profile',
            'view-events',
            'view-artists',
            'view-venues',
        ]);
        $user->refresh();
        $this->assertFalse($user->can('create-events'));

        $venue = $this->makeVenue($user);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/events', [
            'name' => 'Crowd Source Gig',
            'date' => now()->addDay()->toDateString(),
            'time' => '20:00',
            'venue_id' => $venue->id,
        ]);

        $response->assertCreated()->assertJsonPath('data.name', 'Crowd Source Gig');
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
            ->assertJsonPath('message', 'Event created successfully.')
            ->assertJsonPath('existing', false);

        $this->assertDatabaseHas('events', [
            'name' => 'API Test Gig',
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);
    }

    public function test_claimed_artist_posts_event_with_artist_page_as_owner(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user', 'artist']);

        $artist = \App\Models\Artist::query()->create([
            'stage_name' => 'Danger Daveed',
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        $venue = $this->makeVenue($user);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/events', [
            'name' => 'Daveed Live',
            'date' => now()->addDays(4)->toDateString(),
            'time' => '21:00',
            'venue_id' => $venue->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.posted_by.via', 'Danger Daveed')
            ->assertJsonPath('data.posted_by.page.id', $artist->id);

        $this->assertDatabaseHas('events', [
            'name' => 'Daveed Live',
            'owner_id' => $artist->id,
            'owner_type' => 'artist',
        ]);
    }

    public function test_duplicate_event_returns_existing_without_creating_row(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $venue = $this->makeVenue($user);

        $date = now()->addDays(5)->toDateString();

        \App\Models\Event::query()->create([
            'name' => 'Friday Jazz Night',
            'date' => $date,
            'time' => '20:00',
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/events', [
            'name' => 'Friday Jazz Night',
            'date' => $date,
            'time' => '20:00',
            'venue_id' => $venue->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Friday Jazz Night')
            ->assertJsonPath('existing', true);

        $this->assertEquals(1, \App\Models\Event::query()->where('venue_id', $venue->id)->count());
    }

    public function test_same_ticket_url_returns_existing_event(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $venue = $this->makeVenue($user);
        $ticketUrl = 'https://tickets.example.com/gig/'.uniqid();

        \App\Models\Event::query()->create([
            'name' => 'Original Title',
            'date' => now()->addDays(4)->toDateString(),
            'time' => '19:00',
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
            'ticket_url' => $ticketUrl,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/events', [
            'name' => 'Different wording',
            'date' => now()->addDays(10)->toDateString(),
            'time' => '21:00',
            'venue_id' => $venue->id,
            'ticket_url' => rtrim($ticketUrl, '/').'/',
        ]);

        $response->assertOk()->assertJsonPath('existing', true);
        $this->assertEquals(1, \App\Models\Event::query()->count());
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
