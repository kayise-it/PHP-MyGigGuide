<?php

namespace Tests\Unit;

use App\Services\Bandsintown\BandsintownApiClient;
use App\Services\Bandsintown\BandsintownImportService;
use App\Services\EventDuplicateService;
use Tests\TestCase;

class BandsintownImportServiceTest extends TestCase
{
    public function test_map_event_extracts_core_fields(): void
    {
        $service = new BandsintownImportService(
            $this->createMock(BandsintownApiClient::class),
            $this->createMock(EventDuplicateService::class),
        );

        $mapped = $service->mapEvent([
            'id' => '123',
            'title' => '',
            'datetime' => now('Africa/Johannesburg')->addDays(5)->format('Y-m-d\TH:i:s'),
            'description' => '<b>Show</b>',
            'url' => 'https://www.bandsintown.com/e/123',
            'lineup' => ['Sample Artist'],
            'offers' => [
                ['type' => 'Tickets', 'url' => 'https://tickets.example/abc'],
            ],
            'venue' => [
                'name' => 'Theatre of Living Arts',
                'city' => 'Johannesburg',
                'region' => 'GP',
                'country' => 'ZA',
                'latitude' => '-26.2',
                'longitude' => '28.0',
            ],
        ], 'Sample Artist');

        $this->assertNotNull($mapped);
        $this->assertSame('Sample Artist', $mapped['name']);
        $this->assertSame('Show', $mapped['description']);
        $this->assertSame('https://tickets.example/abc', $mapped['ticket_url']);
        $this->assertSame('ZA', $mapped['country']);
        $this->assertSame('Johannesburg', $mapped['city']);
    }
}
