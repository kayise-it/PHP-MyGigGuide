<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_me_includes_owned_and_claimable_pages(): void
    {
        $user = User::factory()->create([
            'email' => 'band@gmail.com',
            'email_verified_at' => now(),
        ]);
        $user->addRole('user');

        Artist::query()->create([
            'stage_name' => 'My Band',
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        Artist::query()->create([
            'stage_name' => 'Match Me',
            'user_id' => null,
            'contact_email' => 'band@gmail.com',
            'claim_status' => 'none',
        ]);

        Venue::query()->create([
            'name' => 'Other Venue',
            'capacity' => 50,
            'user_id' => null,
            'owner_id' => null,
            'contact_email' => 'band@gmail.com',
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonPath('email_verified', true)
            ->assertJsonPath('owned_pages.0.type', 'artist')
            ->assertJsonPath('owned_pages.0.name', 'My Band')
            ->assertJsonCount(2, 'claimable_pages')
            ->assertJsonPath('website_claim_url', route('register'));
    }
}
