<?php

namespace Tests\Feature\Api\V1;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // GET /api/v1/polls/{context}
    // -------------------------------------------------------------------------

    public function test_show_returns_404_when_no_active_poll(): void
    {
        $response = $this->getJson('/api/v1/polls/vowfm');

        $response->assertStatus(404)
                 ->assertJson(['message' => 'No active poll']);
    }

    public function test_show_returns_poll_data_when_active(): void
    {
        $poll = Poll::create([
            'context'   => 'vowfm',
            'question'  => 'Best song this week?',
            'options'   => ['Song A', 'Song B', 'Song C'],
            'active'    => true,
            'closes_at' => now()->addDays(7),
        ]);

        $response = $this->getJson('/api/v1/polls/vowfm');

        $response->assertStatus(200)
                 ->assertJsonPath('poll.id', $poll->id)
                 ->assertJsonPath('poll.context', 'vowfm')
                 ->assertJsonPath('poll.question', 'Best song this week?')
                 ->assertJsonPath('poll.total_votes', 0)
                 ->assertJsonPath('poll.user_vote_index', null)
                 ->assertJsonStructure([
                     'poll' => [
                         'id', 'context', 'question', 'closes_at',
                         'options' => [['index', 'label', 'votes', 'percent']],
                         'total_votes', 'user_vote_index',
                     ],
                 ]);
    }

    public function test_show_returns_404_for_inactive_poll(): void
    {
        Poll::create([
            'context'  => 'vowfm',
            'question' => 'Old question',
            'options'  => ['A', 'B'],
            'active'   => false,
        ]);

        $this->getJson('/api/v1/polls/vowfm')->assertStatus(404);
    }

    public function test_show_returns_404_for_expired_poll(): void
    {
        Poll::create([
            'context'   => 'vowfm',
            'question'  => 'Expired poll',
            'options'   => ['A', 'B'],
            'active'    => true,
            'closes_at' => now()->subHour(),
        ]);

        $this->getJson('/api/v1/polls/vowfm')->assertStatus(404);
    }

    public function test_show_returns_user_vote_index_when_device_id_provided(): void
    {
        $poll = Poll::create([
            'context'  => 'vowfm',
            'question' => 'Vote check',
            'options'  => ['A', 'B'],
            'active'   => true,
        ]);

        PollVote::create([
            'poll_id'      => $poll->id,
            'device_id'    => 'device-abc',
            'option_index' => 1,
        ]);

        $response = $this->getJson('/api/v1/polls/vowfm?device_id=device-abc');

        $response->assertStatus(200)
                 ->assertJsonPath('poll.user_vote_index', 1);
    }

    // -------------------------------------------------------------------------
    // POST /api/v1/polls/{poll}/vote
    // -------------------------------------------------------------------------

    public function test_vote_records_and_returns_updated_results(): void
    {
        $poll = Poll::create([
            'context'   => 'vowfm',
            'question'  => 'Pick one',
            'options'   => ['Option A', 'Option B'],
            'active'    => true,
            'closes_at' => now()->addDays(7),
        ]);

        $response = $this->postJson("/api/v1/polls/{$poll->id}/vote", [
            'device_id'    => 'device-xyz',
            'option_index' => 0,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('poll.total_votes', 1)
                 ->assertJsonPath('poll.user_vote_index', 0)
                 ->assertJsonPath('poll.options.0.votes', 1)
                 ->assertJsonPath('poll.options.1.votes', 0);

        $this->assertDatabaseHas('poll_votes', [
            'poll_id'      => $poll->id,
            'device_id'    => 'device-xyz',
            'option_index' => 0,
        ]);
    }

    public function test_vote_returns_409_on_duplicate(): void
    {
        $poll = Poll::create([
            'context'  => 'vowfm',
            'question' => 'Already voted',
            'options'  => ['A', 'B'],
            'active'   => true,
        ]);

        PollVote::create([
            'poll_id'      => $poll->id,
            'device_id'    => 'dup-device',
            'option_index' => 0,
        ]);

        $response = $this->postJson("/api/v1/polls/{$poll->id}/vote", [
            'device_id'    => 'dup-device',
            'option_index' => 1,
        ]);

        $response->assertStatus(409)
                 ->assertJson(['message' => 'Already voted']);
    }

    public function test_vote_returns_422_when_poll_is_closed(): void
    {
        $poll = Poll::create([
            'context'   => 'vowfm',
            'question'  => 'Closed poll',
            'options'   => ['A', 'B'],
            'active'    => true,
            'closes_at' => now()->subHour(),
        ]);

        $response = $this->postJson("/api/v1/polls/{$poll->id}/vote", [
            'device_id'    => 'late-device',
            'option_index' => 0,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Poll is closed']);
    }

    public function test_vote_returns_422_for_invalid_option(): void
    {
        $poll = Poll::create([
            'context'  => 'vowfm',
            'question' => 'Two options only',
            'options'  => ['A', 'B'],
            'active'   => true,
        ]);

        $response = $this->postJson("/api/v1/polls/{$poll->id}/vote", [
            'device_id'    => 'device-q',
            'option_index' => 99,
        ]);

        $response->assertStatus(422);
    }
}
