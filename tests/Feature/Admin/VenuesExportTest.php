<?php

namespace Tests\Feature\Admin;

use App\Models\Organiser;
use App\Models\Role;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenuesExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_venues_csv(): void
    {
        Role::firstOrCreate(['name' => 'admin']);

        $admin = User::factory()->create();
        $admin->addRole('admin');

        $venueUser = User::factory()->create();
        $organiser = Organiser::create([
            'user_id' => $venueUser->id,
            'organisation_name' => 'Test Org',
            'contact_email' => 'org@example.com',
        ]);

        Venue::create([
            'name' => 'Test Venue',
            'contact_email' => 'venue@example.com',
            'user_id' => $venueUser->id,
            'owner_id' => $organiser->id,
            'owner_type' => Organiser::class,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.venues.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeaderContains('Content-Disposition', 'attachment;');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('ID,Name,Description,Address,City,Capacity,Contact Email', $csv);
        $this->assertStringContainsString('Test Venue', $csv);
    }
}


