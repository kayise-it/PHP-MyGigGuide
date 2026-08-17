@extends('layouts.app')

@section('title', $organiser->organisation_name . ' - Event Organiser - '.$siteBrand->name)
@section('description', 'View ' . $organiser->organisation_name . ' profile and gigs they posted.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="{{ $siteBrand->detailPanelClass('p-8') }} mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                <div class="flex-shrink-0">
                    @if($organiser->logo)
                        <img src="{{ Storage::url($organiser->logo) }}"
                             alt="{{ $organiser->organisation_name }}"
                             class="w-24 h-24 rounded-full object-cover">
                    @else
                        <div class="w-24 h-24 rounded-full flex items-center justify-center {{ $siteBrand->isRogues ? 'bg-slate-800' : 'bg-[#12121A] border border-white/10' }}">
                            <span class="text-3xl font-bold {{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }}">
                                {{ substr($organiser->organisation_name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $organiser->organisation_name }}</h1>
                    @if($organiser->user)
                        <p class="text-lg {{ $siteBrand->detailMutedTextClass() }} mb-4">{{ $organiser->user->name }}</p>
                    @endif
                    @if($organiser->description)
                        <p class="text-slate-300 mb-4">{{ $organiser->description }}</p>
                    @endif
                    <x-page-ownership-badge :entity="$organiser" variant="inline" />
                </div>
            </div>
        </div>

        <x-page-posted-events
            :events="$postedEvents"
            heading="Gigs they posted"
            empty="This organiser has not listed any upcoming gigs yet."
        />
    </div>
</div>
@endsection
