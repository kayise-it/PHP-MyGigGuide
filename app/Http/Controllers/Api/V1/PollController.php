<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function show(string $context): JsonResponse
    {
        $poll = Poll::query()
            ->where('context', $context)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('closes_at')
                    ->orWhere('closes_at', '>', now());
            })
            ->latest('id')
            ->first();

        if ($poll === null) {
            return response()->json(['poll' => null]);
        }

        return response()->json([
            'poll' => $this->formatPoll($poll),
        ]);
    }

    public function vote(Request $request, Poll $poll): JsonResponse
    {
        if (! $poll->isOpen()) {
            return response()->json(['message' => 'This poll is closed.'], 422);
        }

        $validated = $request->validate([
            'device_id' => 'required|string|max:128',
            'option_index' => 'required|integer|min:0',
        ]);

        $optionCount = count($poll->options ?? []);
        if ($validated['option_index'] >= $optionCount) {
            return response()->json(['message' => 'Invalid option.'], 422);
        }

        $existing = PollVote::query()
            ->where('poll_id', $poll->id)
            ->where('device_id', $validated['device_id'])
            ->first();

        if ($existing !== null) {
            return response()->json([
                'message' => 'You have already voted on this poll.',
                'poll' => $this->formatPoll($poll->fresh(['votes'])),
            ], 409);
        }

        PollVote::create([
            'poll_id' => $poll->id,
            'device_id' => $validated['device_id'],
            'option_index' => $validated['option_index'],
        ]);

        return response()->json([
            'poll' => $this->formatPoll($poll->fresh(['votes'])),
        ]);
    }

    /** @return array<string, mixed> */
    private function formatPoll(Poll $poll): array
    {
        $poll->loadMissing('votes');

        return [
            'id' => $poll->id,
            'context' => $poll->context,
            'question' => $poll->question,
            'options' => $poll->options,
            'is_open' => $poll->isOpen(),
            'closes_at' => $poll->closes_at?->toIso8601String(),
            'vote_counts' => $poll->voteCounts(),
            'total_votes' => $poll->votes->count(),
        ];
    }
}
