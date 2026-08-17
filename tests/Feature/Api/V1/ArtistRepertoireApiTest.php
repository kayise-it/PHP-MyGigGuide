<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\ArtistSong;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArtistRepertoireApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_public_repertoire_lists_songs_alphabetically(): void
    {
        $artist = Artist::query()->create(['stage_name' => 'Sheron']);

        ArtistSong::query()->create([
            'artist_id' => $artist->id,
            'title' => 'Zebra Song',
            'sort_order' => 2,
        ]);
        ArtistSong::query()->create([
            'artist_id' => $artist->id,
            'title' => 'Angel',
            'original_artist' => 'Shakira',
            'sort_order' => 1,
        ]);

        $response = $this->getJson("/api/v1/artists/{$artist->id}/repertoire");

        $response->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('data.0.title', 'Angel')
            ->assertJsonPath('data.0.original_artist', 'Shakira')
            ->assertJsonMissingPath('data.0.notes');
    }

    public function test_owner_can_add_bulk_and_delete_songs(): void
    {
        $owner = User::factory()->create();
        $artist = Artist::query()->create([
            'stage_name' => 'Sheron',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);

        Sanctum::actingAs($owner);

        $bulk = $this->postJson('/api/v1/me/artist/songs/bulk', [
            'text' => "Oasis - Wonderwall\nMy Original Tune\nOasis - Wonderwall",
        ]);

        $bulk->assertCreated()
            ->assertJsonPath('summary.created', 2)
            ->assertJsonPath('summary.skipped', 1);

        $create = $this->postJson('/api/v1/me/artist/songs', [
            'title' => 'Single Add',
            'original_artist' => 'Test Artist',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.title', 'Single Add');

        $songId = $create->json('data.id');

        $this->getJson('/api/v1/me/artist/songs')
            ->assertOk()
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0.notes', null);

        $this->deleteJson("/api/v1/me/artist/songs/{$songId}")
            ->assertOk();

        $this->getJson('/api/v1/me/artist/songs')
            ->assertJsonPath('meta.total', 2);
    }

    public function test_non_owner_cannot_manage_repertoire(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $artist = Artist::query()->create([
            'stage_name' => 'Sheron',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);

        Sanctum::actingAs($other);

        $this->postJson('/api/v1/me/artist/songs', ['title' => 'Nope'])
            ->assertNotFound();

        $this->postJson("/api/v1/artists/{$artist->id}/songs/bulk", [
            'text' => "Oasis - Wonderwall\n",
        ])->assertForbidden();
    }

    public function test_admin_can_bulk_import_for_another_artist(): void
    {
        $admin = User::factory()->create();
        $admin->addRole('superuser');

        $daveArtist = Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $admin->id,
            'claim_status' => 'approved',
        ]);

        $sheron = Artist::query()->create([
            'stage_name' => 'Sheron',
            'claim_status' => 'approved',
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/artists/{$sheron->id}/songs/bulk", [
            'text' => "Oasis - Wonderwall\nSheron - Glow",
        ])
            ->assertCreated()
            ->assertJsonPath('summary.created', 2);

        $this->getJson("/api/v1/artists/{$sheron->id}/repertoire")
            ->assertOk()
            ->assertJsonPath('meta.total', 2);

        $this->getJson('/api/v1/me/artist/songs')
            ->assertOk()
            ->assertJsonPath('meta.total', 0);

        $this->assertSame(0, ArtistSong::query()->where('artist_id', $daveArtist->id)->count());
    }
}
