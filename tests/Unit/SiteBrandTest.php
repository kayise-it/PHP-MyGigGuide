<?php

namespace Tests\Unit;

use App\Support\SiteBrand;
use Illuminate\Http\Request;
use Tests\TestCase;

class SiteBrandTest extends TestCase
{
    public function test_rogues_hostname_resolves_rogues_brand(): void
    {
        $request = Request::create('https://rogues.mygigguide.co.za/', 'GET');
        $brand = SiteBrand::current($request);

        $this->assertTrue($brand->isRogues);
        $this->assertSame('rogues', $brand->key);
        $this->assertSame('Rogues on Radio', $brand->name);
        $this->assertStringContainsString('RouguesonRadioLogo.png', $brand->logoUrl());
        $this->assertSame('brand-rogues', $brand->bodyClass());
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
}
