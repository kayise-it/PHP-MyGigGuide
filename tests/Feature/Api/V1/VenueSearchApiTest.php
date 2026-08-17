<?php

namespace Tests\Feature\Api\V1;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueSearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_shorter_listing_name_when_user_types_longer_phrase(): void
    {
        Venue::create([
            'name' => 'Higher Ground',
            'address' => '1 Example Rd, Johannesburg',
            'city' => 'Johannesburg',
            'contact_email' => 'higher-ground+test@example.local',
        ]);

        $response = $this->getJson('/api/v1/venues?search='.urlencode('Higher Ground Restaurant'));

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Higher Ground');
    }

    public function test_search_finds_listing_when_user_types_prefix(): void
    {
        Venue::create([
            'name' => 'Higher Ground Restaurant',
            'address' => '1 Example Rd, Johannesburg',
            'city' => 'Johannesburg',
            'contact_email' => 'higher-ground-restaurant+test@example.local',
        ]);

        $response = $this->getJson('/api/v1/venues?search='.urlencode('Higher Ground'));

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Higher Ground Restaurant');
    }

    public function test_search_finds_listing_when_apostrophe_differs(): void
    {
        Venue::create([
            'name' => "Santi's",
            'address' => '1 Example Rd, Johannesburg',
            'city' => 'Johannesburg',
            'contact_email' => 'santis+test@example.local',
        ]);

        $response = $this->getJson('/api/v1/venues?search='.urlencode('santis restaurant and bar'));

        $response->assertOk()
            ->assertJsonPath('data.0.name', "Santi's");
    }

    public function test_search_finds_listing_when_hyphen_differs(): void
    {
        Venue::create([
            'name' => 'Fu-Bar',
            'address' => '12 Example St, Observatory',
            'city' => 'Cape Town',
            'contact_email' => 'fu-bar+test@example.local',
        ]);

        $response = $this->getJson('/api/v1/venues?search='.urlencode('Fubar'));

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Fu-Bar');
    }
}
