<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VenueUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_cannot_update_venue_videos(): void
    {
        $venue = Venue::create([
            'name' => 'Test Venue '.uniqid(),
            'address' => '1 Test St',
            'city' => 'Johannesburg',
            'contact_email' => 'venue+'.uniqid().'@example.local',
            'claim_status' => 'approved',
        ]);

        $this->patchJson("/api/v1/venues/{$venue->id}", [
            'youtube_videos' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ])->assertUnauthorized();
    }

    public function test_owner_can_replace_venue_videos(): void
    {
        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $venue = $this->makeOfficialVenue($owner);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/v1/venues/{$venue->id}", [
            'youtube_videos' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ])->assertOk()
            ->assertJsonPath('data.can_edit_videos', true)
            ->assertJsonPath('data.youtube_videos.0.youtube_video_id', 'dQw4w9WgXcQ');
    }

    public function test_admin_can_update_any_venue_videos(): void
    {
        $owner = $this->makeVerifiedUser();
        $venue = $this->makeOfficialVenue($owner);

        $admin = $this->makeVerifiedUser();
        $admin->syncRoles(['admin']);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/v1/venues/{$venue->id}", [
            'youtube_videos' => ['https://youtu.be/9bZkp7q19f0'],
        ])->assertOk()
            ->assertJsonPath('data.youtube_videos.0.youtube_video_id', '9bZkp7q19f0');
    }

    public function test_owner_can_update_venue_profile_fields(): void
    {
        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $venue = $this->makeOfficialVenue($owner);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/v1/venues/{$venue->id}", [
            'description' => 'Updated venue description.',
            'phone_number' => '0821234567',
            'website' => 'https://example.com',
        ])->assertOk()
            ->assertJsonPath('data.description', 'Updated venue description.')
            ->assertJsonPath('data.phone_number', '0821234567')
            ->assertJsonPath('data.website', 'https://example.com')
            ->assertJsonPath('data.can_edit_profile', true);

        $this->assertDatabaseHas('venues', [
            'id' => $venue->id,
            'description' => 'Updated venue description.',
            'phone_number' => '0821234567',
            'website' => 'https://example.com',
        ]);
    }

    public function test_stranger_cannot_update_venue_profile(): void
    {
        $owner = $this->makeVerifiedUser();
        $venue = $this->makeOfficialVenue($owner);

        $stranger = $this->makeVerifiedUser();
        Sanctum::actingAs($stranger);

        $this->patchJson("/api/v1/venues/{$venue->id}", [
            'description' => 'Hacked description',
        ])->assertForbidden();
    }

    public function test_owner_can_upload_venue_main_picture_via_post(): void
    {
        Storage::fake('public');

        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $venue = $this->makeOfficialVenue($owner);

        Sanctum::actingAs($owner);

        $response = $this->post("/api/v1/venues/{$venue->id}", [
            'name' => $venue->name,
            'main_picture' => UploadedFile::fake()->image('venue.jpg', 800, 600),
        ]);

        $response->assertOk()
            ->assertJsonPath('data.main_picture_url', fn ($url) => filled($url));

        $venue->refresh();
        $this->assertNotNull($venue->main_picture);
        Storage::disk('public')->assertExists($venue->main_picture);
    }

    private function makeVerifiedUser(): User
    {
        return User::factory()->create([
            'username' => 'apitest_'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function makeOfficialVenue(User $owner): Venue
    {
        return Venue::create([
            'name' => 'Owned Venue '.uniqid(),
            'address' => '2 Gig Lane',
            'city' => 'Cape Town',
            'contact_email' => 'owned+'.uniqid().'@example.local',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);
    }
}
