@props([
    'artistsByEvents',
    'artistsByRating',
    'venuesByEvents',
    'venuesByRating',
    'browseSort' => 'events',
])

@php
    $artistCount = max($artistsByEvents->count(), $artistsByRating->count());
    $venueCount = max($venuesByEvents->count(), $venuesByRating->count());
    $hasBrowse = $artistCount > 0 || $venueCount > 0;
    $defaultTab = $artistCount > 0 ? 'artists' : 'venues';
@endphp

@if($hasBrowse)
<section id="home-browse" class="{{ $siteBrand->sectionSurfaceClass() }}">
    <div
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
        x-data="{
            browseTab: @js($defaultTab),
            browseSort: @js($browseSort),
            setBrowseSort(sort) {
                if (this.browseSort === sort) return;
                const scrollY = window.scrollY;
                this.browseSort = sort;
                this.$nextTick(() => window.scrollTo(0, scrollY));
            },
        }"
    >
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between mb-6">
            <div>
                <h2 class="{{ $siteBrand->headingClass() }} mb-1">Browse</h2>
                <p class="{{ $siteBrand->bodyTextClass() }} max-w-none text-base">
                    Top artists and venues by gigs and ratings.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <div class="{{ $siteBrand->homeSegmentGroupClass() }}">
                    <button
                        type="button"
                        class="{{ $siteBrand->homeSegmentButtonClass() }}"
                        :class="browseSort === 'events' ? '{{ $siteBrand->homeSegmentActiveClass() }}' : '{{ $siteBrand->homeSegmentInactiveClass() }}'"
                        @click.prevent="setBrowseSort('events')"
                    >
                        Most gigs
                    </button>
                    <button
                        type="button"
                        class="{{ $siteBrand->homeSegmentButtonClass() }}"
                        :class="browseSort === 'rating' ? '{{ $siteBrand->homeSegmentActiveClass() }}' : '{{ $siteBrand->homeSegmentInactiveClass() }}'"
                        @click.prevent="setBrowseSort('rating')"
                    >
                        Top rated
                    </button>
                </div>

                @if($artistCount > 0 && $venueCount > 0)
                <div class="{{ $siteBrand->homeSegmentGroupClass() }}">
                    <button
                        type="button"
                        class="{{ $siteBrand->homeSegmentButtonClass() }}"
                        :class="browseTab === 'artists' ? '{{ $siteBrand->homeSegmentActiveClass() }}' : '{{ $siteBrand->homeSegmentInactiveClass() }}'"
                        @click="browseTab = 'artists'"
                    >
                        Artists
                    </button>
                    <button
                        type="button"
                        class="{{ $siteBrand->homeSegmentButtonClass() }}"
                        :class="browseTab === 'venues' ? '{{ $siteBrand->homeSegmentActiveClass() }}' : '{{ $siteBrand->homeSegmentInactiveClass() }}'"
                        @click="browseTab = 'venues'"
                    >
                        Venues
                    </button>
                </div>
                @endif
            </div>
        </div>

        @if($artistCount > 0)
        <div x-show="browseTab === 'artists'" x-cloak>
            <div x-show="browseTab === 'artists' && browseSort === 'events'">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($artistsByEvents as $artist)
                        <x-artist-card :artist="$artist" />
                    @endforeach
                </div>
            </div>
            <div x-show="browseTab === 'artists' && browseSort === 'rating'" x-cloak>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($artistsByRating as $artist)
                        <x-artist-card :artist="$artist" />
                    @endforeach
                </div>
            </div>
            <div class="mt-8 text-center">
                <a
                    href="{{ route('artists.index', ['sort' => 'events']) }}"
                    class="btn-primary inline-flex items-center px-6 py-3 font-medium rounded-lg"
                    x-bind:href="browseSort === 'rating' ? '{{ route('artists.index', ['sort' => 'rating']) }}' : '{{ route('artists.index', ['sort' => 'events']) }}'"
                >
                    View all artists
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @endif

        @if($venueCount > 0)
        <div x-show="browseTab === 'venues'" x-cloak @if($artistCount === 0) x-init="browseTab = 'venues'" @endif>
            <div x-show="browseTab === 'venues' && browseSort === 'events'">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($venuesByEvents as $venue)
                        <x-venue-card :venue="$venue" />
                    @endforeach
                </div>
            </div>
            <div x-show="browseTab === 'venues' && browseSort === 'rating'" x-cloak>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($venuesByRating as $venue)
                        <x-venue-card :venue="$venue" />
                    @endforeach
                </div>
            </div>
            <div class="mt-8 text-center">
                <a
                    href="{{ route('venues.index', ['sort' => 'events']) }}"
                    class="btn-primary inline-flex items-center px-6 py-3 font-medium rounded-lg"
                    x-bind:href="browseSort === 'rating' ? '{{ route('venues.index', ['sort' => 'rating']) }}' : '{{ route('venues.index', ['sort' => 'events']) }}'"
                >
                    View all venues
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @endif
    </div>
</section>
@endif
