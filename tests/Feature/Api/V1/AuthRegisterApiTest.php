<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegisterApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Mobile Tester',
            'email' => 'mobile.tester@example.com',
            'password' => 'password123',
            'device_name' => 'phpunit',
        ]);

        $response->assertCreated()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', 'mobile.tester@example.com')
            ->assertJsonPath('user.name', 'Mobile Tester');

        $this->assertNotEmpty($response->json('access_token'));
        $this->assertNotEmpty($response->json('user.username'));

        $user = User::query()->where('email', 'mobile.tester@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('user'));
        $this->assertTrue($user->can('create-events'));
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Another',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_works_without_email_verification_on_api(): void
    {
        $user = User::factory()->create([
            'username' => 'unverified_user',
            'email_verified_at' => null,
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->addRole('user');

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'unverified_user',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonPath('user.username', 'unverified_user');
    }

    public function test_login_grants_create_events_when_role_lacks_permission(): void
    {
        $user = User::factory()->create([
            'username' => 'legacy_app_user',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->addRole('user');
        $this->assertFalse($user->can('create-events'));

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'legacy_app_user',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $user->refresh();
        $this->assertTrue($user->can('create-events'));
    }
}
