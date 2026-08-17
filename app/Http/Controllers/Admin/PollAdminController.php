<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PollAdminController extends Controller
{
    /** Station / app contexts shown in admin dropdown. */
    public const CONTEXTS = [
        'mygigguide' => 'My Gig Guide (vanilla)',
        'rogues' => 'Rogues on Radio',
        'fm919' => '919 FM',
        'hot1027' => 'HOT 1027',
        'vowfm' => 'VOW FM',
        'risefm' => 'Rise FM',
        'mix938' => 'Mix 93.8',
        'burghradio' => 'Burgh Radio',
    ];

    public function index(Request $request): View
    {
        $query = Poll::query()->latest('id');

        if ($request->filled('context')) {
            $query->where('context', $request->string('context'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('question', 'like', "%{$search}%");
        }

        $polls = $query->paginate(20)->withQueryString();

        return view('admin.polls.index', [
            'polls' => $polls,
            'contexts' => self::CONTEXTS,
        ]);
    }

    public function create(): View
    {
        return view('admin.polls.create', [
            'contexts' => self::CONTEXTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePoll($request);

        Poll::create($validated);

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll created.');
    }

    public function edit(Poll $poll): View
    {
        return view('admin.polls.edit', [
            'poll' => $poll,
            'contexts' => self::CONTEXTS,
        ]);
    }

    public function update(Request $request, Poll $poll): RedirectResponse
    {
        $poll->update($this->validatePoll($request));

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll updated.');
    }

    public function destroy(Poll $poll): RedirectResponse
    {
        $poll->delete();

        return redirect()
            ->route('admin.polls.index')
            ->with('success', 'Poll deleted.');
    }

    public function close(Poll $poll): RedirectResponse
    {
        $poll->update([
            'is_active' => false,
            'closes_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Poll closed.');
    }

    /** @return array<string, mixed> */
    private function validatePoll(Request $request): array
    {
        $validated = $request->validate([
            'context' => 'required|string|max:64',
            'question' => 'required|string|max:500',
            'options' => 'required|array|min:2|max:10',
            'options.*' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
            'closes_at' => 'nullable|date',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['options'] = array_values(array_filter(
            array_map('trim', $validated['options']),
            fn (string $option) => $option !== ''
        ));

        if (count($validated['options']) < 2) {
            abort(422, 'At least two options are required.');
        }

        return $validated;
    }
}
