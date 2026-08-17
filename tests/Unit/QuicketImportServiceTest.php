<?php

namespace Tests\Unit;

use App\Services\Quicket\QuicketApiClient;
use App\Services\Quicket\QuicketImportService;
use App\Services\Quicket\QuicketPosterService;
use App\Services\EventDuplicateService;
use App\Services\EventPosterCardService;
use App\Services\GooglePlacesService;
use Tests\TestCase;

class QuicketImportServiceTest extends TestCase
{
    private function makeService(): QuicketImportService
    {
        return new QuicketImportService(
            $this->createMock(QuicketApiClient::class),
            $this->createMock(EventDuplicateService::class),
            $this->createMock(QuicketPosterService::class),
            $this->createMock(EventPosterCardService::class),
            $this->createMock(GooglePlacesService::class),
        );
    }

    public function test_map_event_extracts_core_fields(): void
    {
        $service = $this->makeService();

        $mapped = $service->mapEvent([
            'id' => 170781,
            'name' => 'Test Gig &amp; Friends',
            'description' => '<p>Hello</p>',
            'url' => 'https://www.quicket.co.za/events/170781-test/',
            'imageUrl' => '//images.quicket.co.za/x.png',
            'startDate' => now('Africa/Johannesburg')->addDays(3)->format('Y-m-d\TH:i:s'),
            'venue' => [
                'name' => 'The Venue',
                'addressLine1' => '1 Main Rd',
                'addressLine2' => '',
                'latitude' => -26.1,
                'longitude' => 28.0,
            ],
            'locality' => [
                'levelOne' => 'South Africa',
                'levelTwo' => 'Gauteng',
                'levelThree' => 'Johannesburg',
            ],
            'tickets' => [
                ['price' => 150, 'soldOut' => false, 'donation' => false],
                ['price' => 80, 'soldOut' => false, 'donation' => false],
            ],
        ]);

        $this->assertNotNull($mapped);
        $this->assertSame('Test Gig & Friends', $mapped['name']);
        $this->assertSame('Hello', $mapped['description']);
        $this->assertSame(80, $mapped['price']);
        $this->assertSame('https://images.quicket.co.za/x.png', $mapped['image_url']);
        $this->assertSame('Gauteng', $mapped['province']);
        $this->assertSame('The Venue', $mapped['venue_name']);
    }

    public function test_map_event_skips_past_dates(): void
    {
        $service = $this->makeService();

        $mapped = $service->mapEvent([
            'id' => 1,
            'name' => 'Old',
            'startDate' => '2020-01-01T20:00:00',
            'url' => 'https://www.quicket.co.za/events/1/',
            'venue' => ['name' => 'X'],
            'locality' => ['levelTwo' => 'Gauteng'],
        ]);

        $this->assertNull($mapped);
    }

    public function test_quicket_id_from_ticket_url(): void
    {
        $service = $this->makeService();
        $this->assertSame(
            '369410',
            $service->quicketIdFromTicketUrl('https://www.quicket.co.za/events/369410-buskaid-at-the-linder/')
        );
        $this->assertNull($service->quicketIdFromTicketUrl('https://example.com/x'));
    }

    public function test_configured_category_slugs_default(): void
    {
        config(['quicket.mgg_category_slugs' => ['live-music', 'quicket']]);

        $service = $this->makeService();

        $this->assertSame(['live-music', 'quicket'], $service->configuredCategorySlugs());
    }

    public function test_mgg_slugs_for_quicket_category_map(): void
    {
        config([
            'quicket.category_slug_map' => [
                1  => ['live-music', 'quicket'],
                5  => ['sports', 'quicket'],
                6  => ['travel-outdoor', 'quicket'],
                9  => ['quicket'],                    // Arts & Culture — too broad; refineCategories adds specifics
                30 => ['family-friendly', 'quicket'],
                64 => ['quicket'],                    // Other — too broad; refineCategories adds specifics
            ],
            'quicket.mgg_category_slugs' => ['live-music', 'quicket'],
        ]);

        $service = $this->makeService();

        // Without a title/description, refineCategories is not called — base slugs only.
        $this->assertSame(['live-music', 'quicket'], $service->mggSlugsForQuicketCategory(1));
        $this->assertSame(['sports', 'quicket'], $service->mggSlugsForQuicketCategory(5));
        $this->assertSame(['travel-outdoor', 'quicket'], $service->mggSlugsForQuicketCategory(6));
        $this->assertSame(['family-friendly', 'quicket'], $service->mggSlugsForQuicketCategory(30));
        $this->assertSame(['quicket'], $service->mggSlugsForQuicketCategory(9));
        $this->assertSame(['quicket'], $service->mggSlugsForQuicketCategory(64));
        // Unknown id falls back to mgg_category_slugs
        $this->assertSame(['live-music', 'quicket'], $service->mggSlugsForQuicketCategory(99));

        // With a title, refineCategories runs — broad categories pick up specific slugs.
        $this->assertSame(['quicket', 'theatre'], $service->mggSlugsForQuicketCategory(9, 'Swan Lake Ballet'));
        $this->assertSame(['quicket', 'comedy'], $service->mggSlugsForQuicketCategory(9, 'Stand-up Comedy Night'));
        $this->assertSame(['quicket'], $service->mggSlugsForQuicketCategory(9, 'Round Coffee Table Workshop'));
    }

    public function test_normalize_quicket_category_ids(): void
    {
        $service = $this->makeService();

        $this->assertSame([1], $service->normalizeQuicketCategoryIds(null));
        $this->assertSame([1, 5], $service->normalizeQuicketCategoryIds('1,5'));
        $this->assertSame([5, 6], $service->normalizeQuicketCategoryIds([5, 6, 0, 'x']));
        $this->assertSame([1], $service->normalizeQuicketCategoryIds([]));
    }

    public function test_map_event_extracts_quicket_category_id(): void
    {
        $service = $this->makeService();

        $mapped = $service->mapEvent([
            'id' => 170781,
            'name' => 'Sports Day',
            'categoryId' => 5,
            'startDate' => now('Africa/Johannesburg')->addDays(3)->format('Y-m-d\TH:i:s'),
            'url' => 'https://www.quicket.co.za/events/170781-test/',
            'venue' => ['name' => 'Stadium'],
            'locality' => ['levelTwo' => 'Gauteng', 'levelThree' => 'Johannesburg'],
        ]);

        $this->assertNotNull($mapped);
        $this->assertSame(5, $mapped['quicket_category_id']);

        $nested = $service->mapEvent([
            'id' => 2,
            'name' => 'Hike',
            'category' => ['id' => 6, 'name' => 'Travel'],
            'startDate' => now('Africa/Johannesburg')->addDays(4)->format('Y-m-d\TH:i:s'),
            'url' => 'https://www.quicket.co.za/events/2/',
            'venue' => ['name' => 'Trail'],
            'locality' => ['levelTwo' => 'Western Cape'],
        ]);

        $this->assertNotNull($nested);
        $this->assertSame(6, $nested['quicket_category_id']);
    }

    public function test_extract_quicket_category_id_from_categories_list(): void
    {
        $service = $this->makeService();

        $this->assertSame(1, $service->extractQuicketCategoryId(['categories' => [1, 5]]));
        $this->assertSame(5, $service->extractQuicketCategoryId(['categories' => [['id' => 5]]]));
        $this->assertNull($service->extractQuicketCategoryId(['name' => 'x']));
    }
}
