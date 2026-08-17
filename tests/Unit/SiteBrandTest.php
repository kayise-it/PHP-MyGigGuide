<?php

namespace Tests\Unit;

use App\Support\SiteBrand;
use Illuminate\Http\Request;
use Tests\TestCase;

class SiteBrandTest extends TestCase
{
    public function test_decommissioned_rogues_hostname_falls_back_to_my_gig_guide(): void
    {
        $request = Request::create('https://rogues.mygigguide.co.za/', 'GET');
        $brand = SiteBrand::current($request);

        $this->assertFalse($brand->isRogues);
        $this->assertSame('mygigguide', $brand->key);
        $this->assertSame('My Gig Guide', $brand->name);
    }

    public function test_main_hostname_resolves_my_gig_guide_brand(): void
    {
        $request = Request::create('https://www.mygigguide.co.za/', 'GET');
        $brand = SiteBrand::current($request);

        $this->assertFalse($brand->isRogues);
        $this->assertSame('mygigguide', $brand->key);
        $this->assertSame('My Gig Guide', $brand->name);
        $this->assertSame('brand-mygigguide', $brand->bodyClass());
        $this->assertStringContainsString('bg-black', $brand->pageShellClass());
        $this->assertStringContainsString('indigo', $brand->accentGradientClass());
    }

    public function test_rogues_brand_uses_dark_shell_and_cyan_accent(): void
    {
        $brand = SiteBrand::rogues();

        $this->assertStringContainsString('slate-950', $brand->pageShellClass());
        $this->assertStringContainsString('sky', $brand->accentGradientClass());
        $this->assertNotEmpty($brand->googleMapStyles());
        $this->assertStringContainsString('#0f172a', json_encode($brand->googleMapStyles()));
    }

    public function test_fm919_hostname_resolves_fm919_brand(): void
    {
        $request = Request::create('https://919fm.mygigguide.co.za/', 'GET');
        $brand = SiteBrand::current($request);

        $this->assertTrue($brand->isFm919);
        $this->assertFalse($brand->isRogues);
        $this->assertSame('fm919', $brand->key);
        $this->assertSame('919 FM', $brand->name);
        $this->assertStringContainsString('fm919_brand_logo.png', $brand->logoUrl());
        $this->assertSame('brand-fm919', $brand->bodyClass());
        $this->assertStringContainsString('yellow', $brand->accentGradientClass());
        $this->assertSame('919 FM · My Gig Guide', $brand->footerCopyrightLine());
    }

    public function test_fm919_brand_uses_dark_shell_and_yellow_accent(): void
    {
        $brand = SiteBrand::fm919();

        $this->assertTrue($brand->isDarkTenant());
        $this->assertTrue($brand->isPartnerTenant());
        $this->assertStringContainsString('slate-950', $brand->pageShellClass());
        $this->assertSame('#f2c200', $brand->mapMarkerHex());
        $this->assertStringContainsString('yellow', $brand->navLinkClass(true));
        $this->assertCount(3, $brand->partnerEngagementNavLinks());
        $this->assertSame('Listen live', $brand->partnerEngagementNavLinks()[0]['label']);
    }
}
