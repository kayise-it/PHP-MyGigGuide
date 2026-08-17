<?php

namespace Tests\Feature\Api\V1;

use App\Models\Poll;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_active_poll_for_context(): void
    {
        Poll::create([
            'context' => 'mix938',
            'question' => 'Favourite show?',
            'options' => ['Breakfast', 'Drive'],
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/polls/mix938');

        $response->assertOk()
            ->assertJsonPath('poll.context', 'mix938')
            ->assertJsonPath('poll.question', 'Favourite show?');
    }

    public function test_vote_records_device_choice(): void
    {
        $poll = Poll::create([
            'context' => 'mix938',
            'question' => 'Pick one',
            'options' => ['A', 'B'],
            'is_active' => true,
        ]);

        $response = $this->postJson("/api/v1/polls/{$poll->id}/vote", [
            'device_id' => 'test-device-123',
            'option_index' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('poll.vote_counts.1', 1);
    }
}
