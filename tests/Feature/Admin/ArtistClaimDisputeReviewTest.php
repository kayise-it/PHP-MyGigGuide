<?php

namespace Tests\Feature\Admin;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistClaimDisputeReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_admin_can_review_pending_manual_claim(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->addRole('admin');

        $claimant = User::factory()->create(['email_verified_at' => now()]);
        $claimant->addRole('user');

        $artist = Artist::query()->create([
            'stage_name' => 'Manual Pending',
            'user_id' => null,
            'contact_email' => 'other@example.com',
            'claim_status' => 'pending',
            'pending_claim_user_id' => $claimant->id,
            'pending_claim_at' => now(),
            'claim_request_message' => 'I manage this band.',
            'dispute_raised' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.artist-disputes.show', $artist));

        $response->assertOk()
            ->assertSee('Review Pending Claim')
            ->assertSee('I manage this band.');
    }
}
