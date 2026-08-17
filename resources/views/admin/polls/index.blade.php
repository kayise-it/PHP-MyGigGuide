@extends('layouts.admin')

@section('title', 'Station Polls - Admin Panel')
@section('page-title', 'Station Polls')

@section('content')
<div class="p-6">
    <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Station Polls</h1>
            <p class="text-slate-400">Manage polls for each radio station context</p>
        </div>
        <a href="{{ route('admin.polls.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Poll
        </a>
    </div>

    @if($polls->isEmpty())
        <div class="rounded-lg border border-white/10 bg-slate-900/70 p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="text-slate-400 text-lg">No polls yet.</p>
            <a href="{{ route('admin.polls.create') }}" class="btn btn-primary mt-4 inline-flex">Create your first poll</a>
        </div>
    @else
        @php
            $contextLabels = [
                'vowfm'   => 'VOW 88.1',
                'risefm'  => 'RISE fm',
                'hot1027' => 'HOT 102.7',
                'fm919'   => '91.9 FM',
                'mix938'  => 'Mix 93.8',
            ];
            $contextColors = [
                'vowfm'   => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                'risefm'  => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                'hot1027' => 'bg-orange-500/20 text-orange-300 border-orange-500/30',
                'fm919'   => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                'mix938'  => 'bg-green-500/20 text-green-300 border-green-500/30',
            ];
        @endphp

        @foreach($polls as $context => $contextPolls)
            <div class="mb-8">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-3">
                    {{ $contextLabels[$context] ?? $context }}
                </h2>

                <div class="rounded-lg border border-white/10 bg-slate-900/70 overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-slate-800/90">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Context</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Question</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-400 uppercase">Options</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-400 uppercase">Votes</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Closes</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Created</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach($contextPolls as $poll)
                                <tr class="hover:bg-white/5">
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full border {{ $contextColors[$poll->context] ?? 'bg-slate-700 text-slate-300 border-white/10' }}">
                                            {{ $contextLabels[$poll->context] ?? $poll->context }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-200 max-w-xs">
                                        <span title="{{ $poll->question }}">{{ Str::limit($poll->question, 60) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-300 text-center">
                                        {{ count($poll->options ?? []) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-300 text-center font-medium">
                                        {{ $poll->votes_count }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($poll->active && (!$poll->closes_at || $poll->closes_at->isFuture()))
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-300 border border-green-500/30">Active</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-700 text-slate-400 border border-white/10">Closed</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-400 whitespace-nowrap">
                                        {{ $poll->closes_at ? $poll->closes_at->format('d M Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-400 whitespace-nowrap">
                                        {{ $poll->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.polls.edit', $poll) }}"
                                               class="text-indigo-300 hover:text-indigo-200 font-medium text-sm">Edit</a>

                                            @if($poll->active && (!$poll->closes_at || $poll->closes_at->isFuture()))
                                                <form method="POST" action="{{ route('admin.polls.close', $poll) }}"
                                                      onsubmit="return confirm('Close this poll now?')">
                                                    @csrf
                                                    <button type="submit" class="text-amber-400 hover:text-amber-300 font-medium text-sm">Close</button>
                                                </form>
                                            @endif

                                            <form method="POST" action="{{ route('admin.polls.destroy', $poll) }}"
                                                  onsubmit="return confirm('Delete this poll and all its votes? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 font-medium text-sm">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
