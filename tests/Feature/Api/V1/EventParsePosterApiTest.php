<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventParsePosterApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
        config([
            'miggs_bridge.url' => 'http://127.0.0.1:8787',
            'miggs_bridge.poster_secret' => 'test-bridge-secret',
        ]);
    }

    public function test_guest_cannot_parse_poster(): void
    {
        $response = $this->post('/api/v1/events/parse-poster', [
            'file' => UploadedFile::fake()->image('poster.jpg'),
        ]);

        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_parse_poster_via_bridge(): void
    {
        Http::fake([
            '127.0.0.1:8787/app/parse-poster' => Http::response([
                'ok' => true,
                'parsed' => [
                    'name' => 'Friday Jazz',
                    'venue' => 'The Bassline',
                    'date' => '2026-06-01',
                    'time' => '20:00',
                    'artist' => 'Jazz Trio',
                ],
            ], 200),
        ]);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/events/parse-poster', [
            'file' => UploadedFile::fake()->image('poster.jpg'),
            'hint' => 'Jay @ Stoetbul 4 April',
        ]);

        $response->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('parsed.name', 'Friday Jazz');

        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:8787/app/parse-poster'
                && $request->hasHeader('X-App-Poster-Secret', 'test-bridge-secret');
        });
    }

    public function test_returns_503_when_bridge_not_configured(): void
    {
        config(['miggs_bridge.poster_secret' => '']);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $response = $this->post('/api/v1/events/parse-poster', [
            'file' => UploadedFile::fake()->image('poster.jpg'),
        ]);

        $response->assertStatus(503);
    }
}
