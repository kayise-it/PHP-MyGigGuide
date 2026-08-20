<?php

namespace Tests\Feature\Admin;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollAdminResultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_poll_results_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $poll = Poll::create([
            'context' => 'mix938',
            'question' => 'Favourite song?',
            'options' => ['Track A', 'Track B'],
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.polls.show', $poll));

        $response->assertOk()
            ->assertSee('Favourite song?')
            ->assertSee('Track A')
            ->assertSee('Results');
    }
}
