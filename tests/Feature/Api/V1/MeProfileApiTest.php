<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\Event;
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

    public function test_me_lists_legacy_owned_venue_without_approved_claim_status(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->addRole('user');

        $venue = Venue::query()->create([
            'name' => 'Legacy Linked Venue',
            'capacity' => 80,
            'user_id' => $user->id,
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('owned_pages.0.type', 'venue')
            ->assertJsonPath('owned_pages.0.id', $venue->id)
            ->assertJsonPath('owned_pages.0.name', 'Legacy Linked Venue');
    }

    public function test_venue_show_returns_can_edit_profile_for_owner_bearer(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $owner->addRole('user');

        $venue = Venue::query()->create([
            'name' => 'Bearer Venue',
            'capacity' => 50,
            'user_id' => $owner->id,
            'claim_status' => 'none',
        ]);

        Sanctum::actingAs($owner);

        $this->getJson("/api/v1/venues/{$venue->id}")
            ->assertOk()
            ->assertJsonPath('data.can_edit_profile', true)
            ->assertJsonPath('data.can_edit_videos', true);
    }

    public function test_me_includes_contributor_stats(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->addRole('user');

        Artist::query()->create([
            'stage_name' => 'My Band',
            'user_id' => $user->id,
            'claim_status' => 'approved',
        ]);

        $venue = Venue::query()->create([
            'name' => 'Stats Venue',
            'capacity' => 50,
        ]);

        Event::query()->create([
            'name' => 'Upcoming Gig',
            'date' => now()->addWeek(),
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);

        Event::query()->create([
            'name' => 'Past Gig',
            'date' => now()->subWeek(),
            'venue_id' => $venue->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
            'status' => 'upcoming',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonPath('stats.events_posted', 2)
            ->assertJsonPath('stats.events_upcoming', 1)
            ->assertJsonPath('stats.events_past', 1)
            ->assertJsonPath('stats.pages_managed', 1)
            ->assertJsonStructure(['stats' => ['member_since']]);
    }
}
