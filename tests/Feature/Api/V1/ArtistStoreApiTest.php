<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArtistStoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_cannot_create_artist(): void
    {
        $this->postJson('/api/v1/artists', ['stage_name' => 'New Band'])
            ->assertUnauthorized();
    }

    public function test_member_with_create_events_can_create_artist(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/artists', [
            'stage_name' => 'The Fuzzy Testers',
            'genre' => 'Rock',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.stage_name', 'The Fuzzy Testers')
            ->assertJsonPath('existing', false);

        $this->assertDatabaseHas('artists', [
            'stage_name' => 'The Fuzzy Testers',
            'genre' => 'Rock',
        ]);
    }

    public function test_duplicate_stage_name_returns_existing_artist(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/artists', ['stage_name' => 'Dup Artist']);

        $response = $this->postJson('/api/v1/artists', ['stage_name' => 'dup artist']);

        $response->assertOk()
            ->assertJsonPath('data.stage_name', 'Dup Artist')
            ->assertJsonPath('existing', true);

        $this->assertEquals(1, \App\Models\Artist::query()->whereRaw('LOWER(stage_name) = ?', ['dup artist'])->count());
    }

    private function makeVerifiedUser(): User
    {
        return User::factory()->create([
            'username' => 'apitest_'.uniqid(),
            'is_active' => true,
        ]);
    }
}
