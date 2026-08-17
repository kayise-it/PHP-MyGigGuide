<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VenueResolveApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LaratrustSeeder::class);
        config(['services.google_maps.api_key' => 'test-google-key']);
        Http::fake([
            'maps.googleapis.com/maps/api/place/details/json*' => Http::response([
                'status' => 'OK',
                'result' => ['photos' => []],
            ], 200),
            'maps.googleapis.com/maps/api/place/photo*' => Http::response('fake-image-bytes', 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);
        Storage::fake('public');
    }

    public function test_guest_cannot_resolve_venue(): void
    {
        $this->postJson('/api/v1/venues/resolve', [
            'google_place_id' => 'ChIJtest123',
            'name' => 'The Bassline',
            'address' => '10 Henry Ngcuka, Newtown, Johannesburg',
            'latitude' => -26.2041,
            'longitude' => 28.0473,
        ])->assertUnauthorized();
    }

    public function test_resolve_creates_venue_from_client_place_payload(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/venues/resolve', [
            'google_place_id' => 'ChIJnewplace456',
            'name' => 'Crowd Source Pub',
            'address' => '12 Long Street, Cape Town',
            'city' => 'Cape Town',
            'latitude' => -33.9249,
            'longitude' => 18.4241,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Crowd Source Pub')
            ->assertJsonPath('data.google_place_id', 'ChIJnewplace456')
            ->assertJsonPath('existing', false)
            ->assertJsonPath('match_method', 'created');

        $this->assertDatabaseHas('venues', [
            'name' => 'Crowd Source Pub',
            'google_place_id' => 'ChIJnewplace456',
        ]);
    }

    public function test_resolve_stores_google_place_photo_on_create(): void
    {
        Http::fake([
            'maps.googleapis.com/maps/api/place/details/json*' => Http::response([
                'status' => 'OK',
                'result' => [
                    'photos' => [
                        ['photo_reference' => 'photo-ref-123'],
                    ],
                ],
            ], 200),
            'maps.googleapis.com/maps/api/place/photo*' => Http::response('fake-image-bytes', 200, [
                'Content-Type' => 'image/jpeg',
            ]),
        ]);

        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/venues/resolve', [
            'google_place_id' => 'ChIJphotovenue999',
            'name' => 'Photo Pub',
            'address' => '12 Long Street, Cape Town',
            'city' => 'Cape Town',
            'latitude' => -33.9249,
            'longitude' => 18.4241,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Photo Pub');

        $venue = Venue::query()->where('google_place_id', 'ChIJphotovenue999')->first();
        $this->assertNotNull($venue);
        $this->assertNotNull($venue->main_picture);
        Storage::disk('public')->assertExists($venue->main_picture);
    }

    public function test_resolve_returns_existing_venue_by_google_place_id(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $venue = Venue::query()->create([
            'name' => 'The Bassline',
            'address' => '10 Henry Ngcuka, Newtown, Johannesburg',
            'latitude' => -26.2041,
            'longitude' => 28.0473,
            'google_place_id' => 'ChIJexisting789',
            'contact_email' => 'bassline@example.com',
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/venues/resolve', [
            'google_place_id' => 'ChIJexisting789',
            'name' => 'Different Name',
            'address' => 'Different Address',
            'latitude' => -26.2041,
            'longitude' => 28.0473,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $venue->id)
            ->assertJsonPath('existing', true)
            ->assertJsonPath('match_method', 'google_place_id');
    }

    public function test_resolve_matches_nearby_venue_and_backfills_place_id(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $venue = Venue::query()->create([
            'name' => 'The Bassline',
            'address' => '10 Henry Ngcuka, Newtown, Johannesburg',
            'latitude' => -26.2041000,
            'longitude' => 28.0473000,
            'contact_email' => 'bassline@example.com',
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/venues/resolve', [
            'google_place_id' => 'ChIJproximity999',
            'name' => 'Bassline',
            'address' => '10 Henry Ngcuka, Newtown, Johannesburg, South Africa',
            'latitude' => -26.2041008,
            'longitude' => 28.0473008,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $venue->id)
            ->assertJsonPath('existing', true)
            ->assertJsonPath('match_method', 'proximity');

        $this->assertDatabaseHas('venues', [
            'id' => $venue->id,
            'google_place_id' => 'ChIJproximity999',
        ]);
    }

    public function test_resolve_matches_name_and_address_without_coordinates(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        $venue = Venue::query()->create([
            'name' => 'The Bassline',
            'address' => '10 Henry Ngcuka, Newtown, Johannesburg',
            'contact_email' => 'bassline@example.com',
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'owner_type' => 'user',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/venues/resolve', [
            'google_place_id' => 'ChIJnameaddress111',
            'name' => 'The Bassline',
            'address' => '10 Henry Ngcuka, Newtown, Johannesburg',
            'latitude' => -26.2041,
            'longitude' => 28.0473,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $venue->id)
            ->assertJsonPath('match_method', 'name_address');
    }

    public function test_resolve_requires_google_place_id(): void
    {
        $user = $this->makeVerifiedUser();
        $user->syncRoles(['user']);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/venues/resolve', [
            'name' => 'Missing Place ID',
            'address' => '1 Main Street',
            'latitude' => -26.2,
            'longitude' => 28.0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['google_place_id']);
    }

    private function makeVerifiedUser(): User
    {
        return User::factory()->create([
            'username' => 'apitest_'.uniqid(),
            'is_active' => true,
        ]);
    }
}
