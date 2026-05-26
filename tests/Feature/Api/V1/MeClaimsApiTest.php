<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeClaimsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
        Mail::fake();
    }

    public function test_verified_user_can_claim_matching_artist_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'band@gmail.com',
            'email_verified_at' => now(),
        ]);
        $user->addRole('user');

        $artist = Artist::query()->create([
            'stage_name' => 'Match Me',
            'user_id' => null,
            'contact_email' => 'band@gmail.com',
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/me/claims/initiate', [
            'type' => 'artist',
            'id' => $artist->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Page claimed: Match Me')
            ->assertJsonPath('approved.0.type', 'artist')
            ->assertJsonPath('approved.0.id', $artist->id)
            ->assertJsonCount(1, 'owned_pages')
            ->assertJsonCount(0, 'claimable_pages');

        $artist->refresh();
        $this->assertSame($user->id, $artist->user_id);
        $this->assertSame('approved', $artist->claim_status);
        $this->assertTrue($user->fresh()->hasRole('artist'));
    }

    public function test_claim_all_matching_pages_when_type_and_id_omitted(): void
    {
        $user = User::factory()->create([
            'email' => 'band@gmail.com',
            'email_verified_at' => now(),
        ]);
        $user->addRole('user');

        Artist::query()->create([
            'stage_name' => 'Match Me',
            'user_id' => null,
            'contact_email' => 'band@gmail.com',
            'claim_status' => 'none',
        ]);

        Venue::query()->create([
            'name' => 'My Venue',
            'capacity' => 50,
            'user_id' => null,
            'owner_id' => null,
            'contact_email' => 'band@gmail.com',
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/me/claims/initiate');

        $response->assertOk()
            ->assertJsonCount(2, 'approved')
            ->assertJsonCount(2, 'owned_pages')
            ->assertJsonCount(0, 'claimable_pages');
    }

    public function test_unverified_user_gets_pending_not_approved(): void
    {
        $user = User::factory()->create([
            'email' => 'band@gmail.com',
            'email_verified_at' => null,
        ]);
        $user->addRole('user');

        $artist = Artist::query()->create([
            'stage_name' => 'Match Me',
            'user_id' => null,
            'contact_email' => 'band@gmail.com',
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/me/claims/initiate');

        $response->assertOk()
            ->assertJsonPath('approved', [])
            ->assertJsonCount(1, 'pending')
            ->assertJsonPath('pending.0.id', $artist->id);

        $artist->refresh();
        $this->assertSame('pending', $artist->claim_status);
        $this->assertSame($user->id, $artist->pending_claim_user_id);
    }

    public function test_returns_422_when_no_matching_listings(): void
    {
        $user = User::factory()->create([
            'email' => 'nobody@gmail.com',
            'email_verified_at' => now(),
        ]);
        $user->addRole('user');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/me/claims/initiate');

        $response->assertStatus(422)
            ->assertJsonPath('message', 'No unclaimed listings match your account email.');
    }
}
