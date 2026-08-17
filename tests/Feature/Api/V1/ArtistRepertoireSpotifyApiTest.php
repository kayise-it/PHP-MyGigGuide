<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArtistRepertoireSpotifyApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);

        Config::set('spotify.client_id', 'test-client');
        Config::set('spotify.client_secret', 'test-secret');
        Config::set('spotify.redirect_uri', 'https://example.test/spotify/callback');
        Cache::forget('spotify_client_credentials_token');
    }

    public function test_owner_can_import_spotify_track_by_url(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
            'api.spotify.com/v1/tracks/abc123*' => Http::response([
                'id' => 'abc123',
                'name' => 'Wonderwall',
                'artists' => [['name' => 'Oasis']],
            ]),
        ]);

        $owner = User::factory()->create();
        Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/v1/me/artist/songs/spotify', [
            'spotify_url' => 'https://open.spotify.com/track/abc123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Wonderwall')
            ->assertJsonPath('data.original_artist', 'Oasis')
            ->assertJsonPath('data.reference_spotify_id', 'abc123');
    }

    public function test_owner_can_search_spotify_and_add_track(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'token-123', 'expires_in' => 3600]),
            'api.spotify.com/v1/search*' => Http::response([
                'tracks' => [
                    'items' => [
                        [
                            'id' => 'xyz999',
                            'name' => 'Africa',
                            'artists' => [['name' => 'Toto']],
                        ],
                    ],
                ],
            ]),
            'api.spotify.com/v1/tracks/xyz999*' => Http::response([
                'id' => 'xyz999',
                'name' => 'Africa',
                'artists' => [['name' => 'Toto']],
            ]),
        ]);

        $owner = User::factory()->create();
        Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/me/artist/songs/spotify/search?q=toto+africa')
            ->assertOk()
            ->assertJsonPath('data.0.spotify_id', 'xyz999')
            ->assertJsonPath('data.0.title', 'Africa');

        $this->postJson('/api/v1/me/artist/songs/spotify/add', [
            'spotify_id' => 'xyz999',
        ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Africa');
    }

    public function test_playlist_import_requires_spotify_connection(): void
    {
        $owner = User::factory()->create();
        Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/me/artist/songs/spotify', [
            'spotify_url' => 'https://open.spotify.com/playlist/pl123?si=abc',
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Connect your Spotify account first to import playlists. Track links and Search Spotify still work without connecting.');
    }

    public function test_owner_can_import_spotify_playlist_when_connected(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response(['access_token' => 'user-token-123', 'expires_in' => 3600]),
            'api.spotify.com/v1/playlists/pl123/items*' => Http::response([
                'items' => [
                    ['item' => ['type' => 'track', 'id' => 't1', 'name' => 'Song One', 'artists' => [['name' => 'Artist A']]]],
                    ['item' => ['type' => 'track', 'id' => 't2', 'name' => 'Song Two', 'artists' => [['name' => 'Artist B']]]],
                ],
                'next' => null,
            ]),
        ]);

        $owner = User::factory()->create();
        Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
            'spotify_refresh_token' => 'refresh-token-abc',
            'spotify_connected_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/v1/me/artist/songs/spotify', [
            'spotify_url' => 'https://open.spotify.com/playlist/pl123?si=abc',
        ]);

        $response->assertCreated()
            ->assertJsonPath('summary.created', 2)
            ->assertJsonPath('summary.skipped', 0);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/playlists/pl123/items')
                && ! str_contains($request->url(), 'market=')
                && $request->header('Authorization') === ['Bearer user-token-123'];
        });
    }

    public function test_owner_can_get_spotify_connect_url(): void
    {
        $owner = User::factory()->create();
        Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);

        Sanctum::actingAs($owner);

        $this->getJson('/api/v1/me/artist/songs/spotify/connect')
            ->assertOk()
            ->assertJsonStructure(['data' => ['url']]);
    }

    public function test_admin_spotify_callback_connects_target_artist_not_own(): void
    {
        Http::fake([
            'accounts.spotify.com/*' => Http::response([
                'access_token' => 'access-token',
                'refresh_token' => 'refresh-for-sheron',
                'expires_in' => 3600,
            ]),
        ]);

        $admin = User::factory()->create();
        $admin->addRole('superuser');

        $ownArtist = Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $admin->id,
            'claim_status' => 'approved',
        ]);

        $targetArtist = Artist::query()->create([
            'stage_name' => 'Sheron',
            'user_id' => null,
            'claim_status' => 'approved',
        ]);

        Sanctum::actingAs($admin);

        $url = $this->getJson("/api/v1/artists/{$targetArtist->id}/songs/spotify/connect")
            ->assertOk()
            ->json('data.url');

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $state = $query['state'] ?? '';
        $this->assertNotSame('', $state);

        $this->get('/spotify/callback?code=fake-auth-code&state='.urlencode($state))
            ->assertOk()
            ->assertSee('Spotify connected for Sheron', false);

        $targetArtist->refresh();
        $ownArtist->refresh();

        $this->assertSame('refresh-for-sheron', $targetArtist->spotify_refresh_token);
        $this->assertNull($ownArtist->spotify_refresh_token);
    }
}
