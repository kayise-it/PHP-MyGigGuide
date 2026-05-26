<?php

namespace Tests\Unit;

use App\Models\Artist;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOwnershipStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_artist_without_owner_is_unclaimed(): void
    {
        $artist = Artist::query()->create([
            'stage_name' => 'Crowd Band',
            'user_id' => null,
            'claim_status' => 'none',
        ]);

        $this->assertSame('unclaimed', $artist->getPublicOwnershipStatus());
        $this->assertSame('Unclaimed listing', $artist->getPublicOwnershipLabel());
    }

    public function test_artist_with_user_but_unapproved_shows_unclaimed(): void
    {
        $user = User::factory()->create();

        $artist = Artist::query()->create([
            'stage_name' => 'Prep Band',
            'user_id' => $user->id,
            'claim_status' => 'none',
        ]);

        $this->assertSame('unclaimed', $artist->getPublicOwnershipStatus());
    }

    public function test_artist_with_approved_claim_is_official(): void
    {
        $user = User::factory()->create();

        $artist = Artist::query()->create([
            'stage_name' => 'Owned Band',
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        $this->assertSame('official', $artist->getPublicOwnershipStatus());
    }

    public function test_artist_with_pending_claim_shows_pending(): void
    {
        $claimer = User::factory()->create();

        $artist = Artist::query()->create([
            'stage_name' => 'Pending Band',
            'user_id' => null,
            'pending_claim_user_id' => $claimer->id,
            'claim_status' => 'pending',
        ]);

        $this->assertSame('pending', $artist->getPublicOwnershipStatus());
        $this->assertSame('Claim pending', $artist->getPublicOwnershipLabel());
    }

    public function test_venue_without_user_or_owner_is_unclaimed(): void
    {
        $venue = Venue::query()->create([
            'name' => 'Open Mic Room',
            'capacity' => 100,
            'user_id' => null,
            'owner_id' => null,
            'claim_status' => 'none',
        ]);

        $this->assertTrue($venue->isUnclaimed());
        $this->assertSame('unclaimed', $venue->getPublicOwnershipStatus());
    }

    public function test_venue_with_user_is_official(): void
    {
        $user = User::factory()->create();

        $venue = Venue::query()->create([
            'name' => 'Owned Venue',
            'capacity' => 200,
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        $this->assertSame('official', $venue->getPublicOwnershipStatus());
    }

    public function test_venue_with_imported_admin_owner_shows_unclaimed_until_approved(): void
    {
        $venue = Venue::query()->create([
            'name' => 'Imported Venue',
            'capacity' => 100,
            'user_id' => 1,
            'owner_id' => 1,
            'owner_type' => User::class,
            'claim_status' => 'none',
        ]);

        $this->assertSame('unclaimed', $venue->getPublicOwnershipStatus());
    }

    public function test_venue_with_owner_id_only_stays_unclaimed_until_approved(): void
    {
        $venue = Venue::query()->create([
            'name' => 'Promoter Venue',
            'capacity' => 150,
            'user_id' => null,
            'owner_id' => 99,
            'owner_type' => User::class,
            'claim_status' => 'none',
        ]);

        $this->assertFalse($venue->isUnclaimed());
        $this->assertSame('unclaimed', $venue->getPublicOwnershipStatus());
    }

    public function test_imported_venue_with_pending_claim_is_manageable_in_unclaimed_admin(): void
    {
        $claimer = User::factory()->create();

        $venue = Venue::query()->create([
            'name' => 'Imported Pending Venue',
            'capacity' => 100,
            'user_id' => 1,
            'owner_id' => 1,
            'owner_type' => User::class,
            'claim_status' => 'pending',
            'pending_claim_user_id' => $claimer->id,
            'pending_claim_at' => now(),
        ]);

        $this->assertFalse($venue->isUnclaimed());
        $this->assertSame('pending', $venue->getPublicOwnershipStatus());
        $this->assertTrue($venue->isManageableInUnclaimedAdmin());
        $this->assertSame(1, Venue::notOfficiallyOwned()->whereKey($venue->id)->count());
        $this->assertSame(1, Venue::notOfficiallyOwned()->withPendingClaims()->whereKey($venue->id)->count());
    }

    public function test_approved_venue_is_not_manageable_in_unclaimed_admin(): void
    {
        $user = User::factory()->create();

        $venue = Venue::query()->create([
            'name' => 'Official Venue',
            'capacity' => 200,
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        $this->assertSame('official', $venue->getPublicOwnershipStatus());
        $this->assertFalse($venue->isManageableInUnclaimedAdmin());
        $this->assertSame(0, Venue::notOfficiallyOwned()->whereKey($venue->id)->count());
    }

    public function test_artist_show_page_renders_ownership_badge(): void
    {
        $artist = Artist::query()->create([
            'stage_name' => 'Badge Band',
            'user_id' => null,
            'claim_status' => 'none',
        ]);

        $response = $this->get(route('artists.show', $artist));

        $response->assertOk();
        $response->assertSee('Unclaimed listing', false);
    }
}
