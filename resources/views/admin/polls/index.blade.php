@extends('layouts.admin')

@section('title', 'Station Polls - Admin Panel')
@section('page-title', 'Station Polls')

@section('content')
<div class="p-6">
    <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-100">Station Polls</h1>
            <p class="text-slate-400">Manage polls and read live results for on-air</p>
        </div>
        <a href="{{ route('admin.polls.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Poll
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-6">{{ session('success') }}</div>
    @endif

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
            $contextColors = [
                'vowfm'   => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                'risefm'  => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                'hot1027' => 'bg-orange-500/20 text-orange-300 border-orange-500/30',
                'fm919'   => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                'mix938'  => 'bg-green-500/20 text-green-300 border-green-500/30',
            ];
        @endphp

        @foreach($polls as $context => $contextPolls)
            @php
                $livePoll = $contextPolls->first(fn ($p) => $p->isOpen());
            @endphp

            <div class="mb-8">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-400 mb-3">
                    {{ $contextLabels[$context] ?? $context }}
                </h2>

                @if($livePoll)
                    <div class="mb-4 rounded-lg border border-green-500/40 bg-slate-900/90 p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-4">
                            <div>
                                <span class="inline-block px-2 py-0.5 text-xs font-bold uppercase tracking-wide rounded-full bg-green-500/20 text-green-300 border border-green-500/30 mb-2">
                                    Live now
                                </span>
                                <h3 class="text-lg font-semibold text-white leading-snug">{{ $livePoll->question }}</h3>
                                <p class="mt-1 text-sm text-slate-400">
                                    {{ number_format($livePoll->votes_count) }} {{ Str::plural('vote', $livePoll->votes_count) }}
                                    @if($livePoll->closes_at)
                                        · closes {{ $livePoll->closes_at->format('d M Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2 shrink-0">
                                <a href="{{ route('admin.polls.show', ['poll' => $livePoll, 'live' => 1]) }}" class="btn btn-primary btn-sm">
                                    Live results
                                </a>
                                <a href="{{ route('admin.polls.show', $livePoll) }}" class="btn btn-secondary btn-sm">Results</a>
                            </div>
                        </div>
                        @include('admin.polls._results', [
                            'poll' => $livePoll,
                            'totalVotes' => $livePoll->votes_count,
                        ])
                    </div>
                @endif

                <div class="rounded-lg border border-white/10 bg-slate-900/70 overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-slate-800/90">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Question</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-400 uppercase">Votes</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase">Closes</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach($contextPolls as $poll)
                                <tr class="hover:bg-white/5">
                                    <td class="px-4 py-3 text-sm text-slate-200 max-w-md">
                                        <span title="{{ $poll->question }}">{{ Str::limit($poll->question, 70) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-300 text-center font-medium tabular-nums">
                                        {{ $poll->votes_count }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($poll->isOpen())
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-300 border border-green-500/30">Active</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-700 text-slate-400 border border-white/10">Closed</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-400 whitespace-nowrap">
                                        {{ $poll->closes_at ? $poll->closes_at->format('d M Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.polls.show', $poll) }}"
                                               class="text-green-300 hover:text-green-200 font-medium text-sm">Results</a>
                                            <a href="{{ route('admin.polls.edit', $poll) }}"
                                               class="text-indigo-300 hover:text-indigo-200 font-medium text-sm">Edit</a>

                                            @if($poll->isOpen())
                                                <form method="POST" action="{{ route('admin.polls.close', $poll) }}"
                                                      onsubmit="return confirm('Close this poll now?')">
                                                    @csrf
                                                    @method('PATCH')
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
