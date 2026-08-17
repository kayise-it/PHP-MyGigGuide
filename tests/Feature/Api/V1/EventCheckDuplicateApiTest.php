<?php

namespace Tests\Feature\Api\V1;

use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventCheckDuplicateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_cannot_check_duplicate(): void
    {
        $this->getJson('/api/v1/events/check-duplicate?name=Gig&date=2026-06-01&time=20:00&venue_id=1')
            ->assertUnauthorized();
    }

    public function test_no_match_returns_duplicate_false(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $venue = $this->makeVenue($user);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/events/check-duplicate?'.http_build_query([
            'name' => 'Unique Gig',
            'date' => now()->addDays(6)->toDateString(),
            'time' => '20:00',
            'venue_id' => $venue->id,
        ]))
            ->assertOk()
            ->assertJsonPath('duplicate', false)
            ->assertJsonPath('data', null);
    }

    public function test_match_returns_existing_event(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $venue = $this->makeVenue($user);
        $date = now()->addDays(5)->toDateString();

        Event::query()->create([
            'name' => 'Friday Jazz Night',
            'date' => $date,
            'time' => '20:00',
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/events/check-duplicate?'.http_build_query([
            'name' => 'Friday Jazz Night',
            'date' => $date,
            'time' => '20:00',
            'venue_id' => $venue->id,
        ]))
            ->assertOk()
            ->assertJsonPath('duplicate', true)
            ->assertJsonPath('data.name', 'Friday Jazz Night');
    }

    public function test_validation_requires_core_fields(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/events/check-duplicate?name=Only')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['date', 'time', 'venue_id']);
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
