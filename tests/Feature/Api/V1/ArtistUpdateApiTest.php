<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArtistUpdateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_guest_cannot_update_artist_videos(): void
    {
        $artist = Artist::create([
            'stage_name' => 'Unclaimed '.uniqid(),
            'real_name' => 'Unclaimed',
            'genre' => 'Rock',
            'contact_email' => 'unclaimed+'.uniqid().'@example.local',
            'user_id' => null,
            'claim_status' => 'approved',
        ]);

        $this->patchJson("/api/v1/artists/{$artist->id}", [
            'youtube_videos' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ])->assertUnauthorized();
    }

    public function test_stranger_cannot_update_artist_videos(): void
    {
        $owner = $this->makeVerifiedUser();
        $artist = $this->makeOfficialArtist($owner);

        $stranger = $this->makeVerifiedUser();
        $stranger->syncRoles(['user']);
        Sanctum::actingAs($stranger);

        $this->patchJson("/api/v1/artists/{$artist->id}", [
            'youtube_videos' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ])->assertForbidden();
    }

    public function test_owner_can_replace_artist_videos(): void
    {
        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $artist = $this->makeOfficialArtist($owner);

        Sanctum::actingAs($owner);

        $response = $this->patchJson("/api/v1/artists/{$artist->id}", [
            'youtube_videos' => [
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'https://youtu.be/9bZkp7q19f0',
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.can_edit_videos', true)
            ->assertJsonCount(2, 'data.youtube_videos');

        $this->assertDatabaseHas('youtube_videos', [
            'videoable_type' => Artist::class,
            'videoable_id' => $artist->id,
            'youtube_video_id' => 'dQw4w9WgXcQ',
        ]);
        $this->assertDatabaseHas('youtube_videos', [
            'videoable_type' => Artist::class,
            'videoable_id' => $artist->id,
            'youtube_video_id' => '9bZkp7q19f0',
        ]);
    }

    public function test_owner_can_clear_all_artist_videos(): void
    {
        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $artist = $this->makeOfficialArtist($owner);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/v1/artists/{$artist->id}", [
            'youtube_videos' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ])->assertOk();

        $this->patchJson("/api/v1/artists/{$artist->id}", [
            'youtube_videos' => [],
        ])->assertOk()
            ->assertJsonCount(0, 'data.youtube_videos');

        $this->assertDatabaseMissing('youtube_videos', [
            'videoable_type' => Artist::class,
            'videoable_id' => $artist->id,
        ]);
    }

    public function test_admin_can_update_any_artist_videos(): void
    {
        $owner = $this->makeVerifiedUser();
        $artist = $this->makeOfficialArtist($owner);

        $admin = $this->makeVerifiedUser();
        $admin->syncRoles(['admin']);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/v1/artists/{$artist->id}", [
            'youtube_videos' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
        ])->assertOk()
            ->assertJsonPath('data.youtube_videos.0.youtube_video_id', 'dQw4w9WgXcQ');
    }

    public function test_invalid_youtube_url_returns_validation_error(): void
    {
        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $artist = $this->makeOfficialArtist($owner);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/v1/artists/{$artist->id}", [
            'youtube_videos' => ['not-a-youtube-link'],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['youtube_videos.0']);
    }

    public function test_owner_can_update_artist_profile_fields(): void
    {
        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $artist = $this->makeOfficialArtist($owner);

        Sanctum::actingAs($owner);

        $this->patchJson("/api/v1/artists/{$artist->id}", [
            'bio' => 'Updated bio for the app.',
            'instagram' => 'https://instagram.com/testartist',
            'phone_number' => '0821234567',
        ])->assertOk()
            ->assertJsonPath('data.bio', 'Updated bio for the app.')
            ->assertJsonPath('data.instagram', 'https://instagram.com/testartist')
            ->assertJsonPath('data.phone_number', '0821234567')
            ->assertJsonPath('data.can_edit_profile', true);

        $this->assertDatabaseHas('artists', [
            'id' => $artist->id,
            'bio' => 'Updated bio for the app.',
            'instagram' => 'https://instagram.com/testartist',
            'phone_number' => '0821234567',
        ]);
    }

    public function test_stranger_cannot_update_artist_profile(): void
    {
        $owner = $this->makeVerifiedUser();
        $artist = $this->makeOfficialArtist($owner);

        $stranger = $this->makeVerifiedUser();
        Sanctum::actingAs($stranger);

        $this->patchJson("/api/v1/artists/{$artist->id}", [
            'bio' => 'Hacked bio',
        ])->assertForbidden();
    }

    public function test_owner_can_upload_artist_profile_picture_via_post(): void
    {
        Storage::fake('public');

        $owner = $this->makeVerifiedUser();
        $owner->syncRoles(['user']);
        $artist = $this->makeOfficialArtist($owner);

        Sanctum::actingAs($owner);

        $response = $this->post("/api/v1/artists/{$artist->id}", [
            'stage_name' => $artist->stage_name,
            'profile_picture' => UploadedFile::fake()->image('profile.jpg', 800, 800),
        ]);

        $response->assertOk()
            ->assertJsonPath('data.profile_picture_url', fn ($url) => filled($url));

        $artist->refresh();
        $this->assertNotNull($artist->profile_picture);
        Storage::disk('public')->assertExists($artist->profile_picture);
    }

    private function makeVerifiedUser(): User
    {
        return User::factory()->create([
            'username' => 'apitest_'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function makeOfficialArtist(User $owner): Artist
    {
        return Artist::create([
            'stage_name' => 'Test Artist '.uniqid(),
            'real_name' => 'Test Artist',
            'genre' => 'Rock',
            'contact_email' => 'artist+'.uniqid().'@example.local',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);
    }
}
