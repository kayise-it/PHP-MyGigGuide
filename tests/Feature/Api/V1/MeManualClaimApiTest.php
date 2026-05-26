<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeManualClaimApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_logged_in_user_can_request_manual_claim_for_unclaimed_artist(): void
    {
        $user = User::factory()->create([
            'email' => 'claimant@gmail.com',
            'email_verified_at' => now(),
        ]);
        $user->addRole('user');

        $artist = Artist::query()->create([
            'stage_name' => 'No Email Match',
            'user_id' => null,
            'contact_email' => 'someone-else@gmail.com',
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/me/claims/request', [
            'type' => 'artist',
            'id' => $artist->id,
            'message' => 'I am the band leader.',
        ]);

        $response->assertOk()
            ->assertJsonPath('pending.0.type', 'artist')
            ->assertJsonPath('pending.0.id', $artist->id);

        $artist->refresh();
        $this->assertSame('pending', $artist->claim_status);
        $this->assertSame($user->id, $artist->pending_claim_user_id);
        $this->assertSame('I am the band leader.', $artist->claim_request_message);
        $this->assertNull($artist->user_id);
    }

    public function test_manual_claim_does_not_auto_approve(): void
    {
        $user = User::factory()->create([
            'email' => 'claimant@gmail.com',
            'email_verified_at' => now(),
        ]);
        $user->addRole('user');

        $artist = Artist::query()->create([
            'stage_name' => 'Manual Artist',
            'user_id' => null,
            'contact_email' => 'owner@band.com',
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/me/claims/request', [
            'type' => 'artist',
            'id' => $artist->id,
        ])->assertOk();

        $artist->refresh();
        $this->assertNull($artist->user_id);
        $this->assertSame('pending', $artist->claim_status);
        $this->assertFalse($user->fresh()->hasRole('artist'));
    }

    public function test_returns_422_when_page_already_has_pending_claim_from_other_user(): void
    {
        $claimant = User::factory()->create(['email_verified_at' => now()]);
        $claimant->addRole('user');
        $other = User::factory()->create(['email_verified_at' => now()]);
        $other->addRole('user');

        $artist = Artist::query()->create([
            'stage_name' => 'Taken',
            'user_id' => null,
            'contact_email' => 'x@y.com',
            'claim_status' => 'pending',
            'pending_claim_user_id' => $other->id,
            'pending_claim_at' => now(),
        ]);

        Sanctum::actingAs($claimant);

        $this->postJson('/api/v1/me/claims/request', [
            'type' => 'artist',
            'id' => $artist->id,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Someone else has already requested this page.');
    }

    public function test_logged_in_user_can_request_manual_claim_for_imported_unapproved_venue(): void
    {
        $user = User::factory()->create([
            'email' => 'venue.claimant@gmail.com',
            'email_verified_at' => now(),
        ]);
        $user->addRole('user');

        $venue = Venue::query()->create([
            'name' => 'Imported Venue',
            'capacity' => 100,
            'user_id' => 1,
            'owner_id' => 1,
            'owner_type' => User::class,
            'contact_email' => 'someone-else@gmail.com',
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/me/claims/request', [
            'type' => 'venue',
            'id' => $venue->id,
            'message' => 'I manage this venue.',
        ]);

        $response->assertOk()
            ->assertJsonPath('pending.0.type', 'venue')
            ->assertJsonPath('pending.0.id', $venue->id);

        $venue->refresh();
        $this->assertSame('pending', $venue->claim_status);
        $this->assertSame($user->id, $venue->pending_claim_user_id);
        $this->assertSame('I manage this venue.', $venue->claim_request_message);
    }

    public function test_venue_detail_shows_can_request_claim_for_imported_unapproved_venue(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->addRole('user');

        $venue = Venue::query()->create([
            'name' => 'Claim Target Venue',
            'capacity' => 120,
            'user_id' => 1,
            'owner_id' => 1,
            'owner_type' => User::class,
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/venues/{$venue->id}")
            ->assertOk()
            ->assertJsonPath('data.ownership_status', 'unclaimed')
            ->assertJsonPath('data.can_request_claim', true)
            ->assertJsonPath('data.user_claim_pending', false);
    }

    public function test_artist_detail_includes_ownership_fields_for_authenticated_user(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->addRole('user');

        $artist = Artist::query()->create([
            'stage_name' => 'Claim Target',
            'user_id' => null,
            'contact_email' => 'other@gmail.com',
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/artists/{$artist->id}")
            ->assertOk()
            ->assertJsonPath('data.ownership_status', 'unclaimed')
            ->assertJsonPath('data.can_request_claim', true)
            ->assertJsonPath('data.user_claim_pending', false);
    }
}
