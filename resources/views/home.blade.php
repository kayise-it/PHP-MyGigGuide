@extends('layouts.app')

@push('head')
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url('/') }}">
<meta property="og:title" content="Home - {{ $siteBrand->name }}">
<meta property="og:description" content="{{ $siteBrand->tagline }}">
<meta property="og:image" content="{{ $siteBrand->socialShareImageUrl() }}">
<meta property="og:image:width" content="512">
<meta property="og:image:height" content="512">
<meta property="og:site_name" content="{{ config('app.name', 'My Gig Guide') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Home - {{ $siteBrand->name }}">
<meta name="twitter:description" content="{{ $siteBrand->tagline }}">
<meta name="twitter:image" content="{{ $siteBrand->socialShareImageUrl() }}">
@endpush

@section('title', 'Home - '.$siteBrand->name)
@section('description', $siteBrand->tagline)

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }}">

  <section class="py-6 lg:py-10">
    <div
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
      x-data="{ homePanel: 'gigs' }"
    >
      <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-5">
        <div class="text-left">
          <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">
            {{ $siteBrand->name }}
          </h1>
          <p class="text-slate-400 text-sm md:text-base mt-1 max-w-xl">
            {{ $siteBrand->tagline }}
          </p>
        </div>

        <div class="{{ $siteBrand->homeSegmentGroupClass() }}">
          <button
            type="button"
            class="{{ $siteBrand->homeSegmentButtonClass() }}"
            :class="homePanel === 'gigs' ? '{{ $siteBrand->homeSegmentActiveClass() }}' : '{{ $siteBrand->homeSegmentInactiveClass() }}'"
            @click="homePanel = 'gigs'"
          >
            Gigs
          </button>
          <button
            type="button"
            class="{{ $siteBrand->homeSegmentButtonClass() }}"
            :class="homePanel === 'map' ? '{{ $siteBrand->homeSegmentActiveClass() }}' : '{{ $siteBrand->homeSegmentInactiveClass() }}'"
            @click="homePanel = 'map'"
          >
            Map
          </button>
        </div>
      </div>

      <div x-show="homePanel === 'gigs'" x-cloak>
        <x-home-event-coverflow
          :hero-event-catalog="$heroEventCatalog"
          :hero-days="$heroDays"
          :category-slug="$categorySlug"
          :categories="$categories"
        />
      </div>

      <div x-show="homePanel === 'map'" x-cloak>
        <x-google-map
          :events="$mapEvents"
          :categories-list="$categories"
          height="520px"
          :show-legend="true"
          :compact="false"
          :center="['lat' => -26.2041, 'lng' => 28.0473]"
          id="home-map"
        />
        <div class="mt-4 flex flex-wrap gap-3 justify-center sm:justify-start">
          <a href="{{ route('map') }}" class="btn-outline inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-medium">
            Full-screen map
          </a>
          <a href="{{ route('events.index') }}" class="btn-primary inline-flex items-center px-5 py-2.5 rounded-xl text-sm font-medium">
            Event diary
          </a>
        </div>
      </div>
    </div>
  </section>

  <x-home-browse-section
    :artists-by-events="$artistsByEvents"
    :artists-by-rating="$artistsByRating"
    :venues-by-events="$venuesByEvents"
    :venues-by-rating="$venuesByRating"
    :browse-sort="$browseSort"
  />

  @if($siteBrand->isFm919)
  <section class="py-10 border-t border-slate-800">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
      <p class="text-xs font-semibold uppercase tracking-widest text-yellow-400/90 mb-2">919 FM</p>
      <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">Listen, vote, and request</h2>
      <p class="text-base text-slate-400 mb-6">
        Stream live, take the listener poll, or send a song request to the studio.
      </p>
      <div class="flex flex-col sm:flex-row flex-wrap gap-3 justify-center">
        <a href="{{ route('rogues.listen') }}" class="btn-primary px-6 py-3 rounded-2xl font-semibold">Listen live</a>
        <a href="{{ route('fm919.poll') }}" class="btn-outline px-6 py-3 rounded-2xl font-semibold">Listener poll</a>
        <a href="{{ route('fm919.request') }}" class="btn-outline px-6 py-3 rounded-2xl font-semibold">Send a request</a>
      </div>
    </div>
  </section>
  @endif

  <section class="{{ $siteBrand->ctaSectionClass() }}">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
      <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">
        Post a gig or claim your page
      </h2>
      <p class="text-base text-slate-400 mb-6">
        Use the mobile app to add events, or sign in on the web to manage your artist or venue page.
      </p>
      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('events.index') }}" class="btn-primary px-8 py-3 rounded-2xl font-semibold">
          Browse all events
        </a>
        @if($playStoreUrl = $siteBrand->androidPlayStoreUrl())
        <a href="{{ $playStoreUrl }}" class="btn-outline px-8 py-3 rounded-2xl font-semibold inline-flex items-center justify-center gap-2" target="_blank" rel="noopener noreferrer">
          Get the Android app
        </a>
        @endif
        @guest
        <a href="{{ route('login') }}" class="btn-outline px-8 py-3 rounded-2xl font-semibold">
          Sign in
        </a>
        @endguest
      </div>
    </div>
  </section>
</div>
@endsection
