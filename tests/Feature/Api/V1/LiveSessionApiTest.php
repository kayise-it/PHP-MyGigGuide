<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use App\Models\ArtistSong;
use App\Models\Event;
use App\Models\LiveSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LiveSessionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    private function makeEvent(User $owner, array $attrs = []): Event
    {
        return Event::query()->create(array_merge([
            'name' => 'Test Gig',
            'date' => now()->addDay(),
            'time' => '20:00',
            'status' => 'upcoming',
            'owner_type' => 'user',
            'owner_id' => $owner->id,
        ], $attrs));
    }

    public function test_artist_can_go_live_submit_and_manage_queue(): void
    {
        $owner = User::factory()->create();
        $fan = User::factory()->create();
        $artist = Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);
        $event = $this->makeEvent($owner, ['name' => 'Friday Gig']);
        $event->artists()->attach($artist->id);

        $song = ArtistSong::query()->create([
            'artist_id' => $artist->id,
            'title' => 'Wonderwall',
            'original_artist' => 'Oasis',
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($owner);

        $start = $this->postJson('/api/v1/me/live-sessions', [
            'event_id' => $event->id,
            'artist_id' => $artist->id,
        ]);

        $start->assertCreated()
            ->assertJsonPath('data.status', 'live')
            ->assertJsonPath('data.artist_id', $artist->id);

        $sessionId = $start->json('data.id');

        $this->getJson("/api/v1/events/{$event->id}/live")
            ->assertOk()
            ->assertJsonPath('meta.live_count', 1);

        Sanctum::actingAs($fan);

        $request = $this->postJson("/api/v1/live-sessions/{$sessionId}/requests", [
            'artist_song_id' => $song->id,
            'message' => 'Please play this!',
        ]);

        $request->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.label', 'Wonderwall — Oasis');

        $requestId = $request->json('data.id');

        Sanctum::actingAs($owner);

        $this->getJson("/api/v1/live-sessions/{$sessionId}/queue")
            ->assertOk()
            ->assertJsonPath('meta.total', 1);

        $this->patchJson("/api/v1/live-sessions/{$sessionId}/requests/{$requestId}", [
            'status' => 'accepted',
        ])->assertOk()->assertJsonPath('data.status', 'accepted');

        $this->patchJson("/api/v1/live-sessions/{$sessionId}/requests/{$requestId}", [
            'status' => 'played',
        ])->assertOk()->assertJsonPath('data.status', 'played');

        $this->getJson("/api/v1/live-sessions/{$sessionId}/queue")
            ->assertOk()
            ->assertJsonPath('data.0.status', 'played');

        $this->postJson("/api/v1/live-sessions/{$sessionId}/end")
            ->assertOk()
            ->assertJsonPath('data.status', 'ended');

        Sanctum::actingAs($fan);

        $this->postJson("/api/v1/live-sessions/{$sessionId}/requests", [
            'message' => 'Too late',
        ])->assertStatus(422);
    }

    public function test_fan_can_request_free_text_when_live(): void
    {
        $owner = User::factory()->create();
        $fan = User::factory()->create();
        $artist = Artist::query()->create([
            'stage_name' => 'Sheron',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);
        $event = $this->makeEvent($owner, [
            'name' => 'Acoustic Night',
            'date' => now(),
        ]);
        $event->artists()->attach($artist->id);

        Sanctum::actingAs($owner);
        $sessionId = $this->postJson('/api/v1/me/live-sessions', [
            'event_id' => $event->id,
            'artist_id' => $artist->id,
        ])->json('data.id');

        Sanctum::actingAs($fan);

        $this->postJson("/api/v1/live-sessions/{$sessionId}/requests", [
            'message' => 'Something by Springsteen?',
        ])
            ->assertCreated()
            ->assertJsonPath('data.label', 'Something by Springsteen?');
    }

    public function test_submit_request_returns_snapscan_tip_when_artist_has_code(): void
    {
        $owner = User::factory()->create();
        $fan = User::factory()->create();
        $artist = Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
            'snapscan_code' => '4Z1hJCyG',
        ]);
        $event = $this->makeEvent($owner, ['name' => 'Tip Test Gig']);
        $event->artists()->attach($artist->id);

        $song = ArtistSong::query()->create([
            'artist_id' => $artist->id,
            'title' => 'Test Track',
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($owner);

        $sessionId = $this->postJson('/api/v1/me/live-sessions', [
            'event_id' => $event->id,
            'artist_id' => $artist->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.tips.enabled', true)
            ->assertJsonPath('data.tips.snapscan_code', '4Z1hJCyG')
            ->json('data.id');

        Sanctum::actingAs($fan);

        $this->postJson("/api/v1/live-sessions/{$sessionId}/requests", [
            'artist_song_id' => $song->id,
        ])
            ->assertCreated()
            ->assertJsonPath('tip.enabled', true)
            ->assertJsonPath('tip.snapscan_code', '4Z1hJCyG')
            ->assertJsonPath('tip.amounts_zar', [20, 50, 100, 200])
            ->assertJsonStructure(['tip' => ['reference']]);
    }

    public function test_fan_can_record_tip_intent_and_artist_sees_amount_on_queue(): void
    {
        $owner = User::factory()->create();
        $fan = User::factory()->create();
        $artist = Artist::query()->create([
            'stage_name' => 'Sheron',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
            'snapscan_code' => 'peMSrHSr',
        ]);
        $event = $this->makeEvent($owner, ['name' => 'Tip Intent Gig']);
        $event->artists()->attach($artist->id);

        $song = ArtistSong::query()->create([
            'artist_id' => $artist->id,
            'title' => 'Africa',
            'sort_order' => 1,
        ]);

        Sanctum::actingAs($owner);
        $sessionId = $this->postJson('/api/v1/me/live-sessions', [
            'event_id' => $event->id,
            'artist_id' => $artist->id,
        ])->json('data.id');

        Sanctum::actingAs($fan);
        $requestId = $this->postJson("/api/v1/live-sessions/{$sessionId}/requests", [
            'artist_song_id' => $song->id,
        ])->json('data.id');

        $this->patchJson("/api/v1/live-sessions/{$sessionId}/requests/{$requestId}/tip-intent", [
            'amount_zar' => 50,
        ])
            ->assertOk()
            ->assertJsonPath('data.tip_amount_zar', null);

        Sanctum::actingAs($owner);

        $this->getJson("/api/v1/live-sessions/{$sessionId}/queue")
            ->assertOk()
            ->assertJsonPath('data.0.tip_amount_zar', 50);

        Sanctum::actingAs($fan);

        $this->patchJson("/api/v1/live-sessions/{$sessionId}/requests/{$requestId}/tip-intent", [
            'amount_zar' => 999,
        ])->assertStatus(422);
    }

    public function test_only_one_live_session_per_artist(): void
    {
        $owner = User::factory()->create();
        $artist = Artist::query()->create([
            'stage_name' => 'Danger Dave',
            'user_id' => $owner->id,
            'claim_status' => 'approved',
        ]);
        $event = $this->makeEvent($owner, [
            'name' => 'Gig A',
            'date' => now(),
        ]);
        $event->artists()->attach($artist->id);

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/me/live-sessions', [
            'event_id' => $event->id,
            'artist_id' => $artist->id,
        ])->assertCreated();

        $this->postJson('/api/v1/me/live-sessions', [
            'event_id' => $event->id,
            'artist_id' => $artist->id,
        ])->assertStatus(422);
    }
}
