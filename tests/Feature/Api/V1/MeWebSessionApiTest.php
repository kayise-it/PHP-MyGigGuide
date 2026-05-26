<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeWebSessionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_authenticated_user_can_issue_web_session_url(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->addRole('user');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/me/web-session', [
            'redirect' => '/dashboard',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['url', 'expires_in', 'redirect'])
            ->assertJsonPath('redirect', '/dashboard');

        $url = $response->json('url');
        $this->assertStringContainsString('/auth/app-session', $url);
        $this->assertStringContainsString('token=', $url);
    }

    public function test_web_session_link_logs_user_in_once(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->addRole('user');

        Sanctum::actingAs($user);

        $issue = $this->postJson('/api/v1/me/web-session');
        $issue->assertOk();

        $loginUrl = $issue->json('url');
        $this->assertNotNull($loginUrl);

        $first = $this->get($loginUrl);
        $first->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        auth()->logout();

        $second = $this->get($loginUrl);
        $second->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_cannot_issue_web_session_url(): void
    {
        $this->postJson('/api/v1/me/web-session')
            ->assertUnauthorized();
    }
}
