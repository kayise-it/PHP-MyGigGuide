<?php

namespace Tests\Feature\Admin;

use App\Models\Organiser;
use App\Models\Role;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_organiser_user_with_linked_venue(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'organiser']);

        $admin = User::factory()->create();
        $admin->addRole('admin');

        $organiserUser = User::factory()->create();
        $organiserUser->addRole('organiser');

        $organiser = Organiser::create([
            'user_id' => $organiserUser->id,
            'organisation_name' => 'Cleanup Org',
            'contact_email' => 'cleanup-org@example.com',
        ]);

        Venue::create([
            'name' => 'Cleanup Venue',
            'contact_email' => 'cleanup-venue@example.com',
            'user_id' => $organiserUser->id,
            'owner_id' => $organiser->id,
            'owner_type' => Organiser::class,
        ]);

        $response = $this->actingAs($admin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->deleteJson(route('admin.users.destroy', $organiserUser));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('users', ['id' => $organiserUser->id]);
        $this->assertDatabaseHas('organisers', [
            'id' => $organiser->id,
            'user_id' => null,
        ]);
        $this->assertDatabaseHas('venues', [
            'id' => Venue::query()->value('id'),
            'user_id' => null,
        ]);
    }
}
