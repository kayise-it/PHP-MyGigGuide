<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\StationPortalAccess;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class StationPortalAccessTest extends TestCase
{
    public function test_admin_can_access_fm919_station_portal(): void
    {
        /** @var User&MockObject $user */
        $user = $this->createMock(User::class);
        $user->method('hasRole')->with(['admin', 'superuser'])->willReturn(true);
        $user->email = 'admin@example.com';

        $this->assertTrue(StationPortalAccess::userCanAccess($user, 'fm919'));
    }

    public function test_configured_email_can_access_fm919_station_portal(): void
    {
        config(['fm919.station_emails' => 'studio@919.co.za, Manager@919.CO.ZA']);

        $user = User::factory()->make(['email' => 'studio@919.co.za']);
        $user = \Mockery::mock($user)->makePartial();
        $user->shouldReceive('hasRole')->with(['admin', 'superuser'])->andReturn(false);

        $this->assertTrue(StationPortalAccess::userCanAccess($user, 'fm919'));
    }

    public function test_plain_user_without_allowlist_cannot_access(): void
    {
        config(['fm919.station_emails' => 'studio@919.co.za']);

        $user = User::factory()->make(['email' => 'listener@example.com']);
        $user = \Mockery::mock($user)->makePartial();
        $user->shouldReceive('hasRole')->with(['admin', 'superuser'])->andReturn(false);

        $this->assertFalse(StationPortalAccess::userCanAccess($user, 'fm919'));
    }
}
