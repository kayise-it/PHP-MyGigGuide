<?php

namespace Tests\Feature\Api\V1;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueMapApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_map_returns_only_geocoded_venues(): void
    {
        Venue::create([
            'name' => 'With Coords',
            'address' => '1 Example Rd',
            'city' => 'Johannesburg',
            'contact_email' => 'with-coords+test@example.local',
            'latitude' => -26.2041,
            'longitude' => 28.0473,
        ]);

        Venue::create([
            'name' => 'No Coords',
            'address' => '2 Example Rd',
            'city' => 'Johannesburg',
            'contact_email' => 'no-coords+test@example.local',
        ]);

        $response = $this->getJson('/api/v1/venues/map');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'With Coords')
            ->assertJsonPath('data.0.latitude', -26.2041)
            ->assertJsonPath('data.0.longitude', 28.0473);
    }

    public function test_map_filters_by_radius_when_lat_lng_supplied(): void
    {
        Venue::create([
            'name' => 'Near Centre',
            'address' => '1 Example Rd',
            'city' => 'Johannesburg',
            'contact_email' => 'near+test@example.local',
            'latitude' => -26.2041,
            'longitude' => 28.0473,
        ]);

        Venue::create([
            'name' => 'Far Away',
            'address' => '99 Example Rd',
            'city' => 'Cape Town',
            'contact_email' => 'far+test@example.local',
            'latitude' => -33.9249,
            'longitude' => 18.4241,
        ]);

        $response = $this->getJson('/api/v1/venues/map?'.http_build_query([
            'lat' => -26.2041,
            'lng' => 28.0473,
            'radius_km' => 50,
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Near Centre');
    }
}
