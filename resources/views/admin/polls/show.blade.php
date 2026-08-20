@extends('layouts.admin')

@section('title', 'Poll Results - Admin Panel')
@section('page-title', 'Poll Results')

@if($autoRefresh && $poll->isOpen())
    @push('head')
        <meta http-equiv="refresh" content="30">
    @endpush
@endif

@section('content')
<div class="p-6 max-w-3xl">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">
                {{ $contextLabels[$poll->context] ?? $poll->context }}
            </p>
            <h1 class="text-2xl font-bold text-slate-100 leading-snug">{{ $poll->question }}</h1>
            <p class="mt-2 text-sm text-slate-400">
                <span class="font-medium text-slate-300">{{ number_format($totalVotes) }} {{ Str::plural('vote', $totalVotes) }}</span>
                @if($poll->isOpen())
                    · <span class="text-green-400">Live</span>
                @else
                    · <span class="text-slate-500">Closed</span>
                @endif
                @if($poll->closes_at)
                    · Closes {{ $poll->closes_at->format('d M Y, H:i') }}
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            @if($poll->isOpen())
                @if($autoRefresh)
                    <a href="{{ route('admin.polls.show', $poll) }}" class="btn btn-primary btn-sm">
                        Auto-refresh on (30s)
                    </a>
                @else
                    <a href="{{ route('admin.polls.show', ['poll' => $poll, 'live' => 1]) }}" class="btn btn-primary btn-sm">
                        Turn on auto-refresh
                    </a>
                @endif
            @endif
            <a href="{{ route('admin.polls.show', $poll) }}" class="btn btn-secondary btn-sm">Refresh now</a>
            <a href="{{ route('admin.polls.edit', $poll) }}" class="btn btn-secondary btn-sm">Edit poll</a>
        </div>
    </div>

    <div class="rounded-lg border border-white/10 bg-slate-900/70 p-6 mb-6">
        <h2 class="text-sm font-semibold text-slate-300 uppercase tracking-wide mb-5">Results</h2>
        @include('admin.polls._results', [
            'poll' => $poll,
            'totalVotes' => $totalVotes,
            'large' => true,
        ])
    </div>

    @if($poll->isOpen() && ! $autoRefresh)
        <p class="text-xs text-slate-500">
            Tip: turn on <strong class="text-slate-400">Auto-refresh</strong> during a live show — this page updates every 30 seconds.
        </p>
    @endif

    <div class="mt-6">
        <a href="{{ route('admin.polls.index') }}" class="text-indigo-300 hover:text-indigo-200 text-sm font-medium">← Back to all polls</a>
    </div>
</div>
@endsection
