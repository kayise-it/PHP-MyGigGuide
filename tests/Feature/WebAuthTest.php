<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_register_logs_in_immediately_without_verified_email(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'New Fan',
            'username' => 'newfan',
            'email' => 'newfan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => '1',
        ]);

        $user = User::query()->where('email', 'newfan@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('user'));
        $this->assertNotNull($user->last_login_at);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_web_login_allows_first_password_login_for_unverified_user(): void
    {
        $user = User::factory()->create([
            'username' => 'grace_user',
            'email_verified_at' => null,
            'last_login_at' => null,
        ]);
        $user->addRole('user');

        $response = $this->post('/login', [
            'username' => 'grace_user',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_web_login_blocks_repeat_password_login_for_unverified_user(): void
    {
        $user = User::factory()->create([
            'username' => 'blocked_user',
            'email_verified_at' => null,
            'last_login_at' => now()->subDay(),
        ]);
        $user->addRole('user');

        $response = $this->post('/login', [
            'username' => 'blocked_user',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertGuest();
    }

    public function test_firebase_web_login_establishes_session(): void
    {
        config(['services.firebase.web_api_key' => 'test-firebase-key']);

        Http::fake([
            'identitytoolkit.googleapis.com/*' => Http::response([
                'users' => [
                    [
                        'localId' => 'firebase-web-uid',
                        'email' => 'google@example.com',
                        'displayName' => 'Google User',
                    ],
                ],
            ]),
        ]);

        $response = $this->post('/auth/firebase', [
            'id_token' => 'valid.jwt.token',
        ]);

        $user = User::query()->where('email', 'google@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('firebase-web-uid', $user->firebase_uid);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
