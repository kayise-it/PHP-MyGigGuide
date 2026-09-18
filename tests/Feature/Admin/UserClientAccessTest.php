<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserClientAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
    }

    public function test_admin_user_page_shows_first_party_access(): void
    {
        $admin = User::factory()->create();
        $admin->addRole('admin');

        $user = User::factory()->create([
            'first_seen_at' => now()->subDays(3),
            'last_access_at' => now()->subHour(),
            'first_client' => 'fm919',
            'first_platform' => 'android',
            'last_client' => 'fm919',
            'last_platform' => 'ios',
        ]);
        $user->createToken('fm919-ios');

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user))
            ->assertOk()
            ->assertSee('App / site access')
            ->assertSee('919 FM')
            ->assertSee('Android')
            ->assertSee('iOS')
            ->assertSee('fm919-ios');
    }
}
