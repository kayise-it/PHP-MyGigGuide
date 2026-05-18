<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FirebaseLinkApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_link_firebase_stores_uid_when_token_valid(): void
    {
        config(['services.firebase.web_api_key' => 'test-firebase-key']);

        Http::fake([
            'identitytoolkit.googleapis.com/*' => Http::response([
                'users' => [
                    [
                        'localId' => 'firebase-uid-abc',
                        'email' => 'member@example.com',
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->create([
            'email' => 'member@example.com',
            'email_verified_at' => now(),
            'firebase_uid' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/me/link-firebase', [
            'id_token' => 'valid.jwt.token',
        ]);

        $response->assertOk()
            ->assertJsonPath('firebase_linked', true);

        $this->assertSame('firebase-uid-abc', $user->fresh()->firebase_uid);
    }

    public function test_firebase_login_issues_token_when_uid_linked(): void
    {
        config(['services.firebase.web_api_key' => 'test-firebase-key']);

        Http::fake([
            'identitytoolkit.googleapis.com/*' => Http::response([
                'users' => [
                    [
                        'localId' => 'firebase-uid-xyz',
                        'email' => 'linked@example.com',
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->create([
            'email' => 'linked@example.com',
            'email_verified_at' => now(),
            'firebase_uid' => 'firebase-uid-xyz',
        ]);

        $response = $this->postJson('/api/v1/auth/firebase', [
            'id_token' => 'valid.jwt.token',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['access_token', 'user' => ['id', 'firebase_linked']])
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.firebase_linked', true);
    }

    public function test_firebase_login_auto_links_by_email(): void
    {
        config(['services.firebase.web_api_key' => 'test-firebase-key']);

        Http::fake([
            'identitytoolkit.googleapis.com/*' => Http::response([
                'users' => [
                    [
                        'localId' => 'new-firebase-uid',
                        'email' => 'autolink@example.com',
                    ],
                ],
            ]),
        ]);

        $user = User::factory()->create([
            'email' => 'autolink@example.com',
            'email_verified_at' => now(),
            'firebase_uid' => null,
        ]);

        $response = $this->postJson('/api/v1/auth/firebase', [
            'id_token' => 'valid.jwt.token',
        ]);

        $response->assertOk()->assertJsonPath('user.id', $user->id);
        $this->assertSame('new-firebase-uid', $user->fresh()->firebase_uid);
    }
}
