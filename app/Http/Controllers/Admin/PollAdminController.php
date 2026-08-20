<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PollAdminController extends Controller
{
    private const CONTEXTS = ['vowfm', 'risefm', 'hot1027', 'fm919', 'mix938'];

    /** @var array<string, string> */
    public const CONTEXT_LABELS = [
        'vowfm'   => 'VOW 88.1',
        'risefm'  => 'RISE fm',
        'hot1027' => 'HOT 102.7',
        'fm919'   => '91.9 FM',
        'mix938'  => 'Mix 93.8',
    ];

    public function index(): View
    {
        $polls = Poll::query()
            ->withCount('votes')
            ->latest()
            ->get()
            ->groupBy('context');

        return view('admin.polls.index', [
            'polls' => $polls,
            'contextLabels' => self::CONTEXT_LABELS,
        ]);
    }

    public function create(): View
    {
        $contexts = self::CONTEXTS;
        $defaultClosesAt = now()->addDays(7)->format('Y-m-d');

        return view('admin.polls.create', compact('contexts', 'defaultClosesAt'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePoll($request);

        Poll::create([
            'context'   => $validated['context'],
            'question'  => $validated['question'],
            'options'   => $this->parseOptions($request->input('options_raw')),
            'active'    => $request->boolean('active'),
            'closes_at' => $validated['closes_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll created successfully.');
    }

    public function edit(Poll $poll): View
    {
        $contexts = self::CONTEXTS;
        $optionsText = implode("\n", $poll->options ?? []);
        $totalVotes = $poll->votes()->count();

        return view('admin.polls.edit', [
            'poll' => $poll,
            'contexts' => $contexts,
            'optionsText' => $optionsText,
            'totalVotes' => $totalVotes,
            'contextLabels' => self::CONTEXT_LABELS,
        ]);
    }

    public function show(Poll $poll): View
    {
        $poll->loadCount('votes');

        return view('admin.polls.show', [
            'poll' => $poll,
            'totalVotes' => $poll->votes_count,
            'contextLabels' => self::CONTEXT_LABELS,
            'autoRefresh' => request()->boolean('live'),
        ]);
    }

    public function update(Request $request, Poll $poll): RedirectResponse
    {
        $validated = $this->validatePoll($request);

        $poll->update([
            'context'   => $validated['context'],
            'question'  => $validated['question'],
            'options'   => $this->parseOptions($request->input('options_raw')),
            'active'    => $request->boolean('active'),
            'closes_at' => $validated['closes_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll updated successfully.');
    }

    public function close(Poll $poll): RedirectResponse
    {
        $poll->update([
            'active'    => false,
            'closes_at' => now(),
        ]);

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll closed.');
    }

    public function destroy(Poll $poll): RedirectResponse
    {
        $poll->votes()->delete();
        $poll->delete();

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll deleted.');
    }

    private function validatePoll(Request $request): array
    {
        $rules = [
            'context'    => ['required', 'in:' . implode(',', self::CONTEXTS)],
            'question'   => ['required', 'string', 'max:500'],
            'options_raw' => ['required', 'string'],
            'closes_at'  => ['nullable', 'date', 'after:today'],
            'active'     => ['boolean'],
        ];

        $validated = $request->validate($rules);

        $options = $this->parseOptions($request->input('options_raw'));
        if (count($options) < 2) {
            back()->withErrors(['options_raw' => 'Please enter at least 2 options (one per line).'])->throwResponse();
        }

        return $validated;
    }

    private function parseOptions(string $raw): array
    {
        return array_values(
            array_filter(
                array_map('trim', explode("\n", $raw))
            )
        );
    }
}
