<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class PublicApiTest extends TestCase
{
    public function test_meta_returns_json(): void
    {
        $response = $this->getJson('/api/v1/meta');

        $response->assertOk()
            ->assertJsonStructure(['name', 'api_version']);
    }

    public function test_events_index_returns_json_collection(): void
    {
        $response = $this->getJson('/api/v1/events');

        $response->assertOk()
            ->assertJsonStructure(['data', 'links', 'meta']);
    }
}
