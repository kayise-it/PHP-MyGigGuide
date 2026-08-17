@php
    $picolinosBanner = asset('demo-ads/picolinos/home-banner.jpg');
    $picolinosStrip = asset('demo-ads/picolinos/events-strip.jpg');
@endphp

@extends('layouts.app')

@section('title', 'Advertise on Rogues on Radio — Preview')
@section('description', 'Sponsored placement on the Rogues app and website — preview for advertisers.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} py-8 pb-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Demo banner --}}
        <div class="mb-8 rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-center text-sm text-amber-200">
            <strong>Preview mock-up</strong> — for discussion with Rogues. Payments and self-serve signup are not live yet.
            <a href="{{ route('rogues.pitch') }}" class="underline ml-1">Back to preview hub</a>
        </div>

        {{-- Hero --}}
        <div class="text-center mb-12">
            <h1 class="{{ $siteBrand->heroTitleClass() }}">
                Reach <span class="{{ $siteBrand->accentGradientClass() }}">Rogues listeners</span>
            </h1>
            <p class="{{ $siteBrand->subtitleClass() }}">
                Sponsored spots on the Rogues on Radio app and website — local gigs audience, station brand, clear “Sponsored” labelling.
            </p>
        </div>

        {{-- Package card --}}
        <div class="grid lg:grid-cols-2 gap-8 mb-12">
            <div class="{{ $siteBrand->detailPanelClass() }}">
                <p class="text-sky-400 text-sm font-semibold uppercase tracking-wide mb-2">Launch package</p>
                <h2 class="text-2xl font-bold text-white mb-2">Rogues Spotlight</h2>
                <p class="text-4xl font-bold text-white mb-1">R 3 000 <span class="text-lg font-normal text-slate-400">/ 30 days</span></p>
                <p class="text-sm text-slate-400 mb-6">Price set by Rogues — example only</p>
                <ul class="space-y-3 text-slate-300 text-sm mb-8">
                    <li class="flex gap-2"><span class="text-sky-400">✓</span> Home banner (app + web)</li>
                    <li class="flex gap-2"><span class="text-sky-400">✓</span> Sponsored row in Events list</li>
                    <li class="flex gap-2"><span class="text-sky-400">✓</span> Rogues approves your creative</li>
                    <li class="flex gap-2"><span class="text-sky-400">✓</span> Clearly labelled <strong>Sponsored</strong></li>
                </ul>
                <button type="button" disabled
                    class="w-full py-3 rounded-lg font-semibold bg-sky-600/50 text-sky-100 cursor-not-allowed">
                    Request package (coming soon)
                </button>
                <p class="text-xs text-slate-500 mt-3 text-center">Phase 1: contact Rogues on Radio to book</p>
            </div>

            {{-- Live sample — Picolinos morning show sponsor --}}
            <div class="{{ $siteBrand->detailPanelClass() }}">
                <p class="text-sm font-semibold text-slate-400 uppercase mb-2">Sample creative — real sponsor</p>
                <p class="text-slate-300 text-sm mb-4">
                    <strong class="text-white">Picolinos</strong> — morning show partner.
                    Logo from <a href="https://picolinos.co.za/" class="text-sky-400 hover:text-sky-300" target="_blank" rel="noopener">picolinos.co.za</a>.
                </p>
                <img src="{{ $picolinosBanner }}" alt="Picolinos sponsored banner" class="w-full rounded-lg border border-slate-700 mb-3">
                <img src="{{ $picolinosStrip }}" alt="Picolinos events strip" class="w-full rounded-lg border border-slate-700">
            </div>
        </div>

        {{-- Mock advertiser dashboard --}}
        <div class="{{ $siteBrand->filterPanelClass() }}">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-white">Your dashboard (preview)</h2>
                    <p class="text-sm text-slate-400">What an advertiser would see after booking</p>
                </div>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-300 border border-green-500/30 w-fit">Campaign active</span>
            </div>

            <div class="grid sm:grid-cols-3 gap-4 mb-8">
                @foreach([
                    ['label' => 'Impressions (demo)', 'value' => '12 480'],
                    ['label' => 'Clicks (demo)', 'value' => '186'],
                    ['label' => 'Days remaining', 'value' => '18'],
                ] as $stat)
                <div class="rounded-lg bg-slate-950/80 border border-slate-700 p-4">
                    <p class="text-xs text-slate-500 uppercase">{{ $stat['label'] }}</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $stat['value'] }}</p>
                </div>
                @endforeach
            </div>

            <div class="rounded-lg border border-slate-700 overflow-hidden">
                <div class="px-4 py-3 bg-slate-800/80 border-b border-slate-700">
                    <h3 class="font-semibold text-white text-sm">Active creative</h3>
                </div>
                <div class="p-4 flex flex-col sm:flex-row gap-4 items-start">
                    <img src="{{ $picolinosBanner }}" alt="Picolinos banner" class="w-full sm:w-64 rounded-lg border border-slate-600">
                    <div class="text-sm text-slate-300 space-y-1">
                        <p><span class="text-slate-500">Business:</span> Picolinos Addictive Pizza</p>
                        <p><span class="text-slate-500">Link:</span> <a href="https://picolinos.co.za/" class="text-sky-400 hover:text-sky-300" target="_blank" rel="noopener">picolinos.co.za</a></p>
                        <p><span class="text-slate-500">Show:</span> Morning show sponsor</p>
                        <p><span class="text-slate-500">Runs:</span> 30 days (demo)</p>
                        <p><span class="text-slate-500">Placements:</span> Home + Events</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center text-sm text-slate-500 mt-10">
            Platform by <strong class="text-slate-400">Kee Consulting Pty Ltd</strong> · My Gig Guide white-label
        </p>
    </div>
</div>
@endsection
