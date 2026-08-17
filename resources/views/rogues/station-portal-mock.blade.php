@php
    $picolinosBanner = asset('demo-ads/picolinos/home-banner.jpg');
@endphp

@extends('layouts.app')

@section('title', 'Rogues station portal — Preview')
@section('description', 'Preview dashboard for Rogues staff — ads, listener tools, polls.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-8 pb-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-center text-sm text-amber-200">
            <strong>Preview mock-up</strong> — station login and live data coming in later phases.
            <a href="{{ route('rogues.pitch') }}" class="underline ml-1">Back to preview hub</a>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <p class="text-sky-400 text-sm font-semibold uppercase tracking-wide mb-1">Rogues on Radio</p>
                <h1 class="{{ $siteBrand->pageTitleClass() }} mb-0">Station portal</h1>
                <p class="{{ $siteBrand->pageSubtitleClass() }}">Curate the app, ads, and listener engagement — preview layout</p>
            </div>
            <div class="flex gap-2 text-sm">
                <span class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700">Role: Station manager</span>
                <button type="button" disabled class="px-3 py-1.5 rounded-lg bg-sky-600/40 text-sky-200 cursor-not-allowed">Sign out</button>
            </div>
        </div>

        <p class="text-sm text-slate-400 mb-8 rounded-lg border border-slate-700 bg-slate-900/50 px-4 py-3">
            <strong class="text-slate-200">Future roles:</strong> Sales staff may only see <em>Advertising</em>; studio team may see <em>Listener inbox</em> and <em>Polls</em> — same login, different permissions.
        </p>

        {{-- Nav tabs (visual only) --}}
        <div class="flex flex-wrap gap-2 mb-8 border-b border-slate-800 pb-4">
            @foreach(['Overview', 'Advertising', 'Listener inbox', 'Polls', 'Settings'] as $i => $tab)
                <span class="px-4 py-2 rounded-lg text-sm font-medium {{ $i === 0 ? 'bg-sky-600 text-white' : 'text-slate-400 bg-slate-800/50' }}">{{ $tab }}</span>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            @foreach([
                ['label' => 'Pending ad approvals', 'value' => '1', 'accent' => 'text-amber-300'],
                ['label' => 'Active campaigns', 'value' => '1', 'accent' => 'text-green-300'],
                ['label' => 'Listener messages (today)', 'value' => '14', 'accent' => 'text-sky-300'],
            ] as $stat)
            <div class="{{ $siteBrand->listingCardShellClass() }}">
                <p class="text-xs text-slate-500 uppercase">{{ $stat['label'] }}</p>
                <p class="text-3xl font-bold {{ $stat['accent'] }} mt-1">{{ $stat['value'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            {{-- Ad approval queue --}}
            <div class="{{ $siteBrand->filterPanelClass() }} mb-0">
                <h2 class="text-lg font-bold text-white mb-4">Approve advertising</h2>
                <div class="rounded-lg border border-amber-500/30 bg-slate-950/80 p-4 mb-4">
                    <div class="flex justify-between items-start gap-2 mb-3">
                        <div>
                            <p class="font-semibold text-white">Picolinos Addictive Pizza</p>
                            <p class="text-xs text-slate-500">Rogues Spotlight · Morning show · 30 days</p>
                        </div>
                        <span class="text-xs font-bold uppercase text-green-300 bg-green-500/10 px-2 py-1 rounded">Approved · Live</span>
                    </div>
                    <img src="{{ $picolinosBanner }}" alt="Picolinos approved banner" class="w-full rounded-lg border border-slate-600 mb-3">
                    <div class="flex gap-2">
                        <button type="button" disabled class="flex-1 py-2 rounded-lg bg-slate-700 text-slate-400 text-sm cursor-not-allowed">Pause</button>
                        <button type="button" disabled class="flex-1 py-2 rounded-lg bg-slate-700 text-slate-300 text-sm cursor-not-allowed">View stats</button>
                    </div>
                </div>
                <div class="rounded-lg border border-amber-500/30 bg-slate-950/80 p-4 mb-4">
                    <div class="flex justify-between items-start gap-2 mb-3">
                        <div>
                            <p class="font-semibold text-white">Next sponsor (example)</p>
                            <p class="text-xs text-slate-500">Rogues Spotlight · 30 days</p>
                        </div>
                        <span class="text-xs font-bold uppercase text-amber-300 bg-amber-500/10 px-2 py-1 rounded">Awaiting approval</span>
                    </div>
                    <div class="h-20 rounded bg-gradient-to-r from-slate-700 to-slate-800 border border-slate-600 mb-3 flex items-center justify-center text-xs text-slate-400">
                        Uploaded banner
                    </div>
                    <div class="flex gap-2">
                        <button type="button" disabled class="flex-1 py-2 rounded-lg bg-green-600/50 text-green-100 text-sm font-semibold cursor-not-allowed">Approve</button>
                        <button type="button" disabled class="flex-1 py-2 rounded-lg bg-slate-700 text-slate-300 text-sm cursor-not-allowed">Reject</button>
                    </div>
                </div>
                <a href="{{ route('rogues.advertise.mock') }}" class="text-sky-400 hover:text-sky-300 text-sm">View Picolinos on advertiser preview →</a>
            </div>

            {{-- Listener inbox mock --}}
            <div class="{{ $siteBrand->filterPanelClass() }} mb-0">
                <h2 class="text-lg font-bold text-white mb-4">Listener inbox (preview)</h2>
                <p class="text-xs text-slate-500 mb-4">WhatsApp, requests, feedback — triage in one place (Phase 2+)</p>
                <ul class="space-y-3">
                    @foreach([
                        ['src' => 'WhatsApp', 'text' => 'Please play Springsteen for my dad\'s birthday', 'time' => '12 min ago'],
                        ['src' => 'App feedback', 'text' => 'Wrong time on Friday jazz listing', 'time' => '1 hr ago'],
                        ['src' => 'WhatsApp', 'text' => 'Shout-out to the Monday crew', 'time' => '2 hr ago'],
                    ] as $msg)
                    <li class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2">
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span class="text-sky-400 font-medium">{{ $msg['src'] }}</span>
                            <span>{{ $msg['time'] }}</span>
                        </div>
                        <p class="text-sm text-slate-200">{{ $msg['text'] }}</p>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Poll results mock --}}
        <div class="{{ $siteBrand->filterPanelClass() }} mt-8">
            <h2 class="text-lg font-bold text-white mb-2">Listener poll results (preview)</h2>
            <p class="text-sm text-slate-400 mb-6">Same poll as the app <strong>Listener poll</strong> tile — demo data</p>
            <p class="text-white font-medium mb-4">What’s your favourite Rogues show slot?</p>
            @foreach([
                ['label' => 'Morning Heist', 'pct' => 42],
                ['label' => 'Drive time', 'pct' => 35],
                ['label' => 'Weekend shows', 'pct' => 23],
            ] as $row)
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-slate-300">{{ $row['label'] }}</span>
                    <span class="text-sky-400">{{ $row['pct'] }}%</span>
                </div>
                <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-sky-600 to-cyan-400" style="width: {{ $row['pct'] }}%"></div>
                </div>
            </div>
            @endforeach
            <p class="text-xs text-slate-500 mt-4">127 demo responses · Export CSV (coming soon)</p>
        </div>

        <p class="text-center text-sm text-slate-500 mt-10">
            Platform by <strong class="text-slate-400">Kee Consulting Pty Ltd</strong>
        </p>
    </div>
</div>
@endsection
