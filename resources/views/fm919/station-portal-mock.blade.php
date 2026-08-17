@extends('layouts.app')

@section('title', '919 FM station portal — Preview')
@section('description', 'Preview dashboard for 919 FM staff — ads, listener tools, polls.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-8 pb-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-center text-sm text-amber-200">
            <strong>Station portal</strong> — signed in as {{ $stationUser->name ?? $stationUser->email }}.
            Live ad approvals and listener inbox sync in later phases.
            <a href="{{ route('rogues.pitch') }}" class="underline ml-1">Back to preview hub</a>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div>
                <p class="text-yellow-400 text-sm font-semibold uppercase tracking-wide mb-1">919 FM</p>
                <h1 class="{{ $siteBrand->pageTitleClass() }} mb-0">Station portal</h1>
                <p class="{{ $siteBrand->pageSubtitleClass() }}">Curate the app, ads, and listener engagement</p>
            </div>
            <div class="flex gap-2 text-sm items-center">
                <span class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 border border-slate-700">Role: Station staff</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-700 text-slate-100 hover:bg-slate-600 transition-colors">Sign out</button>
                </form>
            </div>
        </div>

        <p class="text-sm text-slate-400 mb-8 rounded-lg border border-slate-700 bg-slate-900/50 px-4 py-3">
            <strong class="text-slate-200">Future roles:</strong> Sales staff may only see <em>Advertising</em>; studio team may see <em>Listener inbox</em> and <em>Polls</em> — same login, different permissions.
        </p>

        <div class="flex flex-wrap gap-2 mb-8 border-b border-slate-800 pb-4">
            @foreach(['Overview', 'Advertising', 'Listener inbox', 'Polls', 'Settings'] as $i => $tab)
                <span class="px-4 py-2 rounded-lg text-sm font-medium {{ $i === 0 ? 'bg-yellow-500 text-slate-950' : 'text-slate-400 bg-slate-800/50' }}">{{ $tab }}</span>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            @foreach([
                ['label' => 'Pending ad approvals', 'value' => '0', 'accent' => 'text-amber-300'],
                ['label' => 'Active campaigns', 'value' => '2', 'accent' => 'text-green-300'],
                ['label' => 'Listener messages (today)', 'value' => '18', 'accent' => 'text-yellow-300'],
            ] as $stat)
            <div class="{{ $siteBrand->listingCardShellClass() }}">
                <p class="text-xs text-slate-500 uppercase">{{ $stat['label'] }}</p>
                <p class="text-3xl font-bold {{ $stat['accent'] }} mt-1">{{ $stat['value'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="{{ $siteBrand->filterPanelClass() }} mb-0">
                <h2 class="text-lg font-bold text-white mb-4">Approve advertising</h2>
                <div class="rounded-lg border border-amber-500/30 bg-slate-950/80 p-4 mb-4">
                    <div class="flex justify-between items-start gap-2 mb-3">
                        <div>
                            <p class="font-semibold text-white">919 Breakfast sponsor (example)</p>
                            <p class="text-xs text-slate-500">919 Spotlight · Rise &amp; Shine · 30 days</p>
                        </div>
                        <span class="text-xs font-bold uppercase text-green-300 bg-green-500/10 px-2 py-1 rounded">Approved · Live</span>
                    </div>
                    <div class="h-20 rounded bg-gradient-to-r from-yellow-700/40 to-amber-900/40 border border-yellow-600/30 mb-3 flex items-center justify-center text-xs text-yellow-200">
                        Sponsored banner preview
                    </div>
                    <div class="flex gap-2">
                        <button type="button" disabled class="flex-1 py-2 rounded-lg bg-slate-700 text-slate-400 text-sm cursor-not-allowed">Pause</button>
                        <button type="button" disabled class="flex-1 py-2 rounded-lg bg-slate-700 text-slate-300 text-sm cursor-not-allowed">View stats</button>
                    </div>
                </div>
                <div class="rounded-lg border border-amber-500/30 bg-slate-950/80 p-4 mb-4">
                    <div class="flex justify-between items-start gap-2 mb-3">
                        <div>
                            <p class="font-semibold text-white">Next sponsor (example)</p>
                            <p class="text-xs text-slate-500">919 Spotlight · Drive Train · 30 days</p>
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
            </div>

            <div class="{{ $siteBrand->filterPanelClass() }} mb-0">
                <h2 class="text-lg font-bold text-white mb-4">Listener inbox (preview)</h2>
                <p class="text-xs text-slate-500 mb-4">WhatsApp requests from the app and website — triage in one place (Phase 2+)</p>
                <ul class="space-y-3">
                    @foreach([
                        ['src' => 'WhatsApp', 'text' => 'Please play Zahara for my mom in Pretoria', 'time' => '8 min ago'],
                        ['src' => 'App request', 'text' => 'Shout-out to the Monday crew on Rise & Shine', 'time' => '42 min ago'],
                        ['src' => 'WhatsApp', 'text' => 'Can you replay that Kwaito track?', 'time' => '1 hr ago'],
                    ] as $msg)
                    <li class="rounded-lg border border-slate-700 bg-slate-950/60 px-3 py-2">
                        <div class="flex justify-between text-xs text-slate-500 mb-1">
                            <span class="text-yellow-400 font-medium">{{ $msg['src'] }}</span>
                            <span>{{ $msg['time'] }}</span>
                        </div>
                        <p class="text-sm text-slate-200">{{ $msg['text'] }}</p>
                    </li>
                    @endforeach
                </ul>
                <p class="mt-4 text-sm">
                    <a href="{{ route('fm919.request') }}" class="text-yellow-400 hover:text-yellow-300">Open public request page →</a>
                </p>
            </div>
        </div>

        <div class="{{ $siteBrand->filterPanelClass() }} mt-8">
            <h2 class="text-lg font-bold text-white mb-2">Listener poll results (preview)</h2>
            <p class="text-sm text-slate-400 mb-6">Same poll as the website and app — demo data until votes sync to the server</p>
            <p class="text-white font-medium mb-4">What's your favourite 919 show slot?</p>
            @foreach([
                ['label' => 'Rise & Shine (6–9am)', 'pct' => 38],
                ['label' => 'The Feel Good Fix (9am–12pm)', 'pct' => 24],
                ['label' => 'The Drive Train (3–6pm)', 'pct' => 28],
                ['label' => 'Weekend shows', 'pct' => 10],
            ] as $row)
            <div class="mb-3">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-slate-300">{{ $row['label'] }}</span>
                    <span class="text-yellow-400">{{ $row['pct'] }}%</span>
                </div>
                <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-yellow-600 to-amber-400" style="width: {{ $row['pct'] }}%"></div>
                </div>
            </div>
            @endforeach
            <p class="text-xs text-slate-500 mt-4">214 demo responses · Export CSV (coming soon)</p>
            <p class="mt-4 text-sm">
                <a href="{{ route('fm919.poll') }}" class="text-yellow-400 hover:text-yellow-300">Open public poll page →</a>
            </p>
        </div>

        <p class="text-center text-sm text-slate-500 mt-10">
            Platform by <strong class="text-slate-400">Kee Consulting Pty Ltd</strong>
        </p>
    </div>
</div>
@endsection
