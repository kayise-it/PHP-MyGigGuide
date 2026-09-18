<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientAccessApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_login_stores_brand_platform_and_first_seen(): void
    {
        $user = User::factory()->create([
            'username' => 'app_fan',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->addRole('user');

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'app_fan',
            'password' => 'password123',
            'brand' => 'fm919',
            'platform' => 'android',
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertSame('fm919', $user->first_client);
        $this->assertSame('android', $user->first_platform);
        $this->assertSame('fm919', $user->last_client);
        $this->assertSame('android', $user->last_platform);
        $this->assertNotNull($user->first_seen_at);
        $this->assertNotNull($user->last_access_at);
        $this->assertSame('fm919-android', $user->tokens()->first()?->name);
    }

    public function test_device_name_can_supply_brand_and_platform(): void
    {
        $user = User::factory()->create([
            'username' => 'named_token',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->addRole('user');

        $this->postJson('/api/v1/auth/login', [
            'username' => 'named_token',
            'password' => 'password123',
            'device_name' => 'mygigguide-ios-12',
        ])->assertOk();

        $user->refresh();
        $this->assertSame('mygigguide', $user->first_client);
        $this->assertSame('ios', $user->first_platform);
        $this->assertSame('mygigguide-ios-12', $user->tokens()->first()?->name);
    }

    public function test_first_client_is_not_overwritten(): void
    {
        $user = User::factory()->create();

        $user->recordLogin('fm919', 'android');
        $user->recordLogin('mygigguide', 'ios');

        $user->refresh();
        $this->assertSame('fm919', $user->first_client);
        $this->assertSame('android', $user->first_platform);
        $this->assertSame('mygigguide', $user->last_client);
        $this->assertSame('ios', $user->last_platform);
    }

    public function test_unknown_brand_is_ignored(): void
    {
        $user = User::factory()->create([
            'username' => 'plain_fan',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);
        $user->addRole('user');

        $this->postJson('/api/v1/auth/login', [
            'username' => 'plain_fan',
            'password' => 'password123',
            'brand' => 'not-a-real-skin',
            'platform' => 'android',
        ])->assertOk();

        $user->refresh();
        $this->assertNull($user->first_client);
        $this->assertSame('android', $user->first_platform);
        $this->assertSame('mobile-app', $user->tokens()->first()?->name);
    }

    public function test_authenticated_request_headers_update_last_access(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'first_client' => 'mygigguide',
            'first_platform' => 'android',
            'last_client' => 'mygigguide',
            'last_platform' => 'android',
        ]);
        $user->addRole('user');

        Sanctum::actingAs($user);

        $this->withHeaders([
            'X-Client-Brand' => 'fm919',
            'X-Client-Platform' => 'ios',
        ])->getJson('/api/v1/me')->assertOk();

        $user->refresh();
        $this->assertSame('mygigguide', $user->first_client);
        $this->assertSame('fm919', $user->last_client);
        $this->assertSame('ios', $user->last_platform);
        $this->assertNotNull($user->last_access_at);
    }
}
