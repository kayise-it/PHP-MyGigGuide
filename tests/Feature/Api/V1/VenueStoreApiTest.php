<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VenueStoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_cannot_create_venue(): void
    {
        $this->postJson('/api/v1/venues', [
            'name' => 'Test Pub',
            'address' => '1 Main St',
        ])->assertUnauthorized();
    }

    public function test_member_with_create_events_can_create_venue(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/venues', [
            'name' => 'Crowd Source Pub',
            'address' => '12 Long Street, Cape Town',
            'city' => 'Cape Town',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Crowd Source Pub')
            ->assertJsonPath('existing', false);

        $this->assertDatabaseHas('venues', [
            'name' => 'Crowd Source Pub',
            'city' => 'Cape Town',
        ]);
    }

    public function test_venue_requires_address(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/venues', ['name' => 'No Address Bar'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['address']);
    }

    private function makeVerifiedUser(): User
    {
        return User::factory()->create([
            'username' => 'apitest_'.uniqid(),
            'is_active' => true,
        ]);
    }
}
