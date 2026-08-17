<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PageAlertsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    private function makeOwnedVenue(User $owner): Venue
    {
        return Venue::query()->create([
            'name' => 'Windmill Pub '.uniqid(),
            'address' => '1 Test St',
            'city' => 'Johannesburg',
            'capacity' => 100,
            'contact_email' => 'pub+'.uniqid().'@example.com',
            'user_id' => $owner->id,
            'owner_type' => User::class,
            'owner_id' => $owner->id,
            'claim_status' => 'approved',
        ]);
    }

    private function makeOwnedArtist(User $owner): Artist
    {
        return Artist::query()->create([
            'stage_name' => 'Shades1 '.uniqid(),
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);
    }

    private function makeUserPostedEvent(array $attrs): Event
    {
        return Event::query()->create(array_merge([
            'time' => '20:00',
            'status' => 'upcoming',
            'owner_type' => 'user',
        ], $attrs));
    }

    public function test_page_alerts_returns_user_posted_gigs_tagging_owned_venue_and_artist(): void
    {
        $owner = User::factory()->create();
        $poster = User::factory()->create();

        $venue = $this->makeOwnedVenue($owner);
        $artist = $this->makeOwnedArtist($owner);

        $venueGig = $this->makeUserPostedEvent([
            'name' => 'Pub Night',
            'date' => now()->addDays(5),
            'venue_id' => $venue->id,
            'owner_id' => $poster->id,
        ]);

        $artistGig = $this->makeUserPostedEvent([
            'name' => 'Band Set',
            'date' => now()->addDays(10),
            'venue_id' => $venue->id,
            'owner_id' => $poster->id,
        ]);
        $artistGig->artists()->attach($artist->id);

        $this->makeUserPostedEvent([
            'name' => 'Unrelated Gig',
            'date' => now()->addDays(6),
            'venue_id' => Venue::query()->create([
                'name' => 'Other Place',
                'address' => '9 Elsewhere Rd',
                'city' => 'Durban',
                'capacity' => 50,
                'contact_email' => 'other@example.com',
                'owner_type' => User::class,
                'owner_id' => $poster->id,
            ])->id,
            'owner_id' => $poster->id,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/v1/me/page-alerts');

        $response->assertOk()
            ->assertJsonPath('summary.total', 2)
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$venueGig->id, $artistGig->id], $ids);
    }

    public function test_page_alerts_excludes_quicket_imports_and_own_posts(): void
    {
        config(['quicket.owner_user_id' => 9999]);

        $owner = User::factory()->create();
        $poster = User::factory()->create();

        $venue = $this->makeOwnedVenue($owner);

        $this->makeUserPostedEvent([
            'name' => 'Quicket Import',
            'date' => now()->addDays(4),
            'venue_id' => $venue->id,
            'owner_id' => 9999,
            'ticket_url' => 'https://www.quicket.co.za/events/123-test/',
        ]);

        $this->makeUserPostedEvent([
            'name' => 'My Own Gig',
            'date' => now()->addDays(8),
            'venue_id' => $venue->id,
            'owner_id' => $owner->id,
        ]);

        $this->makeUserPostedEvent([
            'name' => 'Fresh User Gig',
            'date' => now()->addDays(9),
            'venue_id' => $venue->id,
            'owner_id' => $poster->id,
        ])->forceFill([
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ])->saveQuietly();

        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/v1/me/page-alerts');

        $response->assertOk()
            ->assertJsonPath('summary.total', 1);

        $this->assertSame('Fresh User Gig', $response->json('data.0.name'));
    }

    public function test_page_alerts_since_filters_by_created_at(): void
    {
        $owner = User::factory()->create();
        $poster = User::factory()->create();

        $venue = $this->makeOwnedVenue($owner);

        $this->makeUserPostedEvent([
            'name' => 'Older Listing',
            'date' => now()->addDays(12),
            'venue_id' => $venue->id,
            'owner_id' => $poster->id,
        ])->forceFill([
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ])->saveQuietly();

        $newListing = $this->makeUserPostedEvent([
            'name' => 'Fresh Listing',
            'date' => now()->addDays(14),
            'venue_id' => $venue->id,
            'owner_id' => $poster->id,
        ]);
        $newListing->forceFill([
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ])->saveQuietly();

        Sanctum::actingAs($owner);

        $since = now()->subDay()->toIso8601String();

        $response = $this->getJson('/api/v1/me/page-alerts?since='.urlencode($since));

        $response->assertOk()
            ->assertJsonPath('summary.total', 1)
            ->assertJsonPath('data.0.id', $newListing->id);
    }

    public function test_page_alerts_requires_auth(): void
    {
        $this->getJson('/api/v1/me/page-alerts')->assertUnauthorized();
    }
}
