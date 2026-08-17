@extends('layouts.app')

@section('title', 'Rogues platform preview — Kee Consulting')
@section('description', 'Preview mock-ups for Rogues app, advertising, and station portal.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="text-xs font-semibold uppercase tracking-widest text-amber-400/90 mb-3">Preview — not live billing yet</p>
        <h1 class="{{ $siteBrand->pageTitleClass() }} mb-4">Rogues platform preview</h1>
        <p class="{{ $siteBrand->subtitleClass() }} mb-10">
            Mock-ups for Thursday discussion — advertising, station tools, and listener poll.
        </p>

        <div class="grid gap-4 text-left">
            <a href="{{ route('rogues.advertise.mock') }}"
               class="{{ $siteBrand->detailPanelClass() }} block hover:border-sky-500/50 transition-colors">
                <h2 class="text-xl font-bold text-white mb-2">Advertise on Rogues</h2>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-0">
                    Packages, sponsored placements — includes <strong class="text-slate-300">Picolinos</strong> morning-show sample banners.
                </p>
            </a>
            <a href="{{ route('rogues.station.mock') }}"
               class="{{ $siteBrand->detailPanelClass() }} block hover:border-sky-500/50 transition-colors">
                <h2 class="text-xl font-bold text-white mb-2">Station portal (preview)</h2>
                <p class="{{ $siteBrand->detailMutedTextClass() }} mb-0">
                    Rogues staff view — approve ads, listener inbox, poll results (different roles later).
                </p>
            </a>
        </div>

        <p class="mt-10 text-sm {{ $siteBrand->detailMutedTextClass() }}">
            Mobile app: open the <strong>Rogues</strong> tab → <strong>Listener poll</strong> for a sample in-app poll.
        </p>
    </div>
</div>
@endsection
