<?php

namespace Tests\Feature;

use Tests\TestCase;

class Fm919EngagementPagesTest extends TestCase
{
    public function test_poll_page_renders_on_fm919_host(): void
    {
        $response = $this->get('https://919fm.mygigguide.co.za/poll');

        $response->assertOk()
            ->assertSee('Listener poll', false)
            ->assertSee('Rise &amp; Shine', false);
    }

    public function test_request_page_renders_on_fm919_host(): void
    {
        $response = $this->get('https://919fm.mygigguide.co.za/request');

        $response->assertOk()
            ->assertSee('Send a request', false)
            ->assertSee('WhatsApp', false);
    }

    public function test_poll_page_not_found_on_main_site(): void
    {
        $response = $this->get('https://www.mygigguide.co.za/poll');

        $response->assertNotFound();
    }

    public function test_listen_page_renders_on_fm919_host(): void
    {
        $response = $this->get('https://919fm.mygigguide.co.za/listen');

        $response->assertOk()
            ->assertSee('Listen live', false)
            ->assertSee('edge.iono.fm', false);
    }

    public function test_pitch_page_renders_on_fm919_host(): void
    {
        $response = $this->get('https://919fm.mygigguide.co.za/pitch');

        $response->assertOk()
            ->assertSee('919 FM platform preview', false)
            ->assertSee('Station portal', false)
            ->assertDontSee('Rogues platform preview', false);
    }

    public function test_station_portal_redirects_guests_to_login_on_fm919_host(): void
    {
        $response = $this->get('https://919fm.mygigguide.co.za/station');

        $response->assertRedirect(route('login'));
    }

    public function test_nav_includes_fm919_listener_links(): void
    {
        $response = $this->get('https://919fm.mygigguide.co.za/poll');

        $response->assertOk()
            ->assertSee('Listen live', false)
            ->assertSee(route('fm919.request'), false);
    }
}
