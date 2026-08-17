@extends('layouts.app')

@section('title', '919 FM platform preview — Kee Consulting')
@section('description', 'Preview mock-ups for 919 FM app, listener tools, and station portal.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xs font-semibold uppercase tracking-widest text-amber-400/90 mb-3">Preview — not live billing yet</p>
        <h1 class="{{ $siteBrand->pageTitleClass() }} mb-4">919 FM platform preview</h1>
        <p class="{{ $siteBrand->pageSubtitleClass() }} mb-10">
            Listener tools and station portal mock-ups — same pattern as Rogues on Radio.
        </p>

        <div class="grid gap-4 text-left">
            <a href="{{ route('rogues.listen') }}"
               class="{{ $siteBrand->detailPanelClass() }} block hover:border-yellow-500/50 transition-colors">
                <h2 class="text-xl font-bold text-white mb-2">Listen live</h2>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-0">
                    Stream 919 FM in the browser — same stream as the mobile app.
                </p>
            </a>
            <a href="{{ route('fm919.poll') }}"
               class="{{ $siteBrand->detailPanelClass() }} block hover:border-yellow-500/50 transition-colors">
                <h2 class="text-xl font-bold text-white mb-2">Listener poll</h2>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-0">
                    Vote for your favourite show slot — demo saves on this device until backend results go live.
                </p>
            </a>
            <a href="{{ route('fm919.request') }}"
               class="{{ $siteBrand->detailPanelClass() }} block hover:border-yellow-500/50 transition-colors">
                <h2 class="text-xl font-bold text-white mb-2">Send a request</h2>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-0">
                    Song requests and shout-outs via WhatsApp to the studio line.
                </p>
            </a>
            <a href="{{ route('rogues.station.mock') }}"
               class="{{ $siteBrand->detailPanelClass() }} block hover:border-yellow-500/50 transition-colors">
                <h2 class="text-xl font-bold text-white mb-2">Station portal</h2>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-0">
                    Station staff login — manage ads, polls, and listener tools (My Gig Guide account required).
                </p>
            </a>
        </div>

        <p class="mt-10 text-sm {{ $siteBrand->detailMutedTextClass() }}">
            Mobile app: open the <strong>919 FM</strong> build → <strong>Radio</strong> tab for stream, poll, and request chips.
        </p>
    </div>
</div>
@endsection
