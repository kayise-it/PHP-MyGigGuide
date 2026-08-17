<?php

namespace Tests\Unit;

use App\Services\Quicket\QuicketPosterService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuicketPosterServiceTest extends TestCase
{
    public function test_prefers_listing_asset_over_event_page_banner(): void
    {
        Http::fake([
            'https://images.quicket.co.za/0848847_0.jpeg' => Http::response('', 200),
            'https://images.quicket.co.za/0848848_0.jpeg' => Http::response('', 200),
            'https://www.quicket.co.za/events/*' => Http::response(
                '<meta property="og:image" content="//images.quicket.co.za/0848847_300_300.jpeg" />'
                .'<div class="banner-container" style="background-image: url(\'//images.quicket.co.za/0848848_0.jpeg\');">',
                200,
                ['Content-Type' => 'text/html']
            ),
        ]);

        $service = new QuicketPosterService;
        $resolved = $service->resolveBestImageUrl(
            '//images.quicket.co.za/0848847_360_360.jpeg',
            'https://www.quicket.co.za/events/310307-mojo-jazzy-tuesdays/'
        );

        $this->assertSame('https://images.quicket.co.za/0848847_0.jpeg', $resolved);
    }

    public function test_upgrades_api_square_thumb_to_full_listing_asset(): void
    {
        Http::fake([
            'https://images.quicket.co.za/0848847_0.jpeg' => Http::response('', 200),
        ]);

        $service = new QuicketPosterService;
        $resolved = $service->resolveBestImageUrl('//images.quicket.co.za/0848847_360_360.jpeg');

        $this->assertSame('https://images.quicket.co.za/0848847_0.jpeg', $resolved);
    }
}
