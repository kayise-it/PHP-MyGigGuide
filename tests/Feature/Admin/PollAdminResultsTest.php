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
        $this->seed(\Database\Seeders\LaratrustSeeder::class);

        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);

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

    public function test_admin_can_close_poll_via_patch(): void
    {
        $this->seed(\Database\Seeders\LaratrustSeeder::class);

        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);

        $poll = Poll::create([
            'context' => 'vowfm',
            'question' => 'Close me?',
            'options' => ['Yes', 'No'],
            'active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.polls.close', $poll))
            ->assertRedirect(route('admin.polls.index'))
            ->assertSessionHas('success');

        $poll->refresh();
        $this->assertFalse($poll->active);
        $this->assertNotNull($poll->closes_at);
    }
}
