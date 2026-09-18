<?php

namespace Tests\Unit;

use App\Support\ClientAccess;
use Illuminate\Http\Request;
use Tests\TestCase;

class ClientAccessTest extends TestCase
{
    public function test_parses_branded_device_name(): void
    {
        $parsed = ClientAccess::parseDeviceName('fm919-android-149');

        $this->assertSame('fm919', $parsed['client']);
        $this->assertSame('android', $parsed['platform']);
    }

    public function test_ignores_generic_device_name(): void
    {
        $parsed = ClientAccess::parseDeviceName('mobile-app');

        $this->assertNull($parsed['client']);
        $this->assertNull($parsed['platform']);
    }

    public function test_reads_headers_before_body(): void
    {
        $request = Request::create('/api/v1/me', 'GET', [
            'brand' => 'mygigguide',
            'platform' => 'android',
        ]);
        $request->headers->set('X-Client-Brand', 'fm919');
        $request->headers->set('X-Client-Platform', 'ios');

        $access = ClientAccess::fromRequest($request);

        $this->assertSame('fm919', $access['client']);
        $this->assertSame('ios', $access['platform']);
    }

    public function test_unknown_brand_is_ignored(): void
    {
        $this->assertNull(ClientAccess::normalizeClient('tracker-sdk'));
        $this->assertSame('ios', ClientAccess::normalizePlatform('iphone'));
    }

    public function test_web_client_follows_919_hostname(): void
    {
        $request = Request::create('https://919fm.mygigguide.co.za/login', 'POST');

        $access = ClientAccess::forWeb($request);

        $this->assertSame('fm919', $access['client']);
        $this->assertSame('web', $access['platform']);
    }
}
