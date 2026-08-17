<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    /**
     * GET /api/v1/polls/{context}
     *
     * Returns the active poll for the given context string (e.g. 'vowfm').
     * Optionally includes whether the requesting device has already voted.
     */
    public function show(Request $request, string $context): JsonResponse
    {
        $poll = Poll::activeForContext($context)->latest()->first();

        if (! $poll) {
            return response()->json(['message' => 'No active poll'], 404);
        }

        $deviceId = $request->query('device_id');
        $userVoteIndex = null;

        if ($deviceId) {
            $existing = PollVote::where('poll_id', $poll->id)
                ->where('device_id', $deviceId)
                ->first();
            if ($existing) {
                $userVoteIndex = $existing->option_index;
            }
        }

        return response()->json([
            'poll' => $this->formatPoll($poll, $userVoteIndex),
        ]);
    }

    /**
     * POST /api/v1/polls/{poll}/vote
     *
     * Body: { "device_id": "abc123", "option_index": 1 }
     *
     * Records a vote and returns the updated poll results.
     */
    public function vote(Request $request, Poll $poll): JsonResponse
    {
        if (! $poll->active) {
            return response()->json(['message' => 'Poll is closed'], 422);
        }

        if ($poll->closes_at && $poll->closes_at->isPast()) {
            return response()->json(['message' => 'Poll is closed'], 422);
        }

        $data = $request->validate([
            'device_id'    => ['required', 'string', 'max:191'],
            'option_index' => ['required', 'integer', 'min:0'],
        ]);

        $optionCount = count($poll->options ?? []);
        if ($data['option_index'] >= $optionCount) {
            return response()->json(['message' => 'Invalid option'], 422);
        }

        $alreadyVoted = PollVote::where('poll_id', $poll->id)
            ->where('device_id', $data['device_id'])
            ->exists();

        if ($alreadyVoted) {
            return response()->json(['message' => 'Already voted'], 409);
        }

        PollVote::create([
            'poll_id'      => $poll->id,
            'device_id'    => $data['device_id'],
            'option_index' => $data['option_index'],
        ]);

        // Refresh vote counts from DB.
        $poll->unsetRelation('votes');

        return response()->json([
            'poll' => $this->formatPoll($poll, (int) $data['option_index']),
        ]);
    }

    /**
     * Serialise a Poll to the response shape, injecting per-index metadata.
     *
     * @param int|null $userVoteIndex
     */
    private function formatPoll(Poll $poll, ?int $userVoteIndex): array
    {
        $results = $poll->resultsArray();
        $options = [];
        foreach ($results as $index => $row) {
            $options[] = [
                'index'   => $index,
                'label'   => $row['label'],
                'votes'   => $row['votes'],
                'percent' => $row['percent'],
            ];
        }

        return [
            'id'              => $poll->id,
            'context'         => $poll->context,
            'question'        => $poll->question,
            'closes_at'       => $poll->closes_at?->toIso8601String(),
            'options'         => $options,
            'total_votes'     => $poll->totalVotes(),
            'user_vote_index' => $userVoteIndex,
        ];
    }
}
