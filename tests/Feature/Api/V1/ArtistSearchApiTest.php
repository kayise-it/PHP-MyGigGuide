<?php

namespace Tests\Feature\Api\V1;

use App\Models\Artist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistSearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_artist_when_user_types_longer_phrase(): void
    {
        Artist::create([
            'stage_name' => 'The Shades',
            'real_name' => 'The Shades',
            'genre' => 'live-cover-musician',
            'contact_email' => 'the-shades+test@example.local',
        ]);

        $response = $this->getJson('/api/v1/artists?search='.urlencode('The Shades band'));

        $response->assertOk()
            ->assertJsonPath('data.0.stage_name', 'The Shades');
    }

    public function test_search_finds_artist_by_single_token(): void
    {
        Artist::create([
            'stage_name' => 'The Shades',
            'real_name' => 'The Shades',
            'genre' => 'live-cover-musician',
            'contact_email' => 'the-shades-token+test@example.local',
        ]);

        $response = $this->getJson('/api/v1/artists?search='.urlencode('Shades'));

        $response->assertOk()
            ->assertJsonPath('data.0.stage_name', 'The Shades');
    }
}
