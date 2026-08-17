<?php

namespace Tests\Unit;

use App\Http\Controllers\RoguesListenController;
use App\Support\SiteBrand;
use Illuminate\Http\Request;
use Tests\TestCase;

class RoguesListenControllerTest extends TestCase
{
    public function test_show_renders_embed_listen_page_for_rogues_host(): void
    {
        $request = Request::create('/listen?embed=1', 'GET', [], [], [], [
            'HTTP_HOST' => 'rogues.mygigguide.co.za',
        ]);

        $this->app->instance('request', $request);

        $response = (new RoguesListenController)->show();

        $html = $response->render();
        $this->assertStringContainsString('Listen live', $html);
        $this->assertStringContainsString('Play live stream', $html);
        $this->assertStringContainsString('edge.iono.fm', $html);
        $this->assertStringContainsString(SiteBrand::rogues()->name, $html);
    }

    public function test_show_aborts_on_main_site_host(): void
    {
        $request = Request::create('/listen', 'GET', [], [], [], [
            'HTTP_HOST' => 'www.mygigguide.co.za',
        ]);

        $this->app->instance('request', $request);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);

        (new RoguesListenController)->show();
    }

    public function test_show_renders_listen_page_for_fm919_host(): void
    {
        $request = Request::create('/listen', 'GET', [], [], [], [
            'HTTP_HOST' => '919fm.mygigguide.co.za',
        ]);

        $this->app->instance('request', $request);

        $response = (new RoguesListenController)->show();

        $html = $response->render();
        $this->assertStringContainsString('Listen live', $html);
        $this->assertStringContainsString('919 FM', $html);
        $this->assertStringContainsString('xice/112_medium', $html);
    }
}
