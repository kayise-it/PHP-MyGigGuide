@extends('layouts.app')

@section('title', 'About - My Gig Guide')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }}">
  
  <section class="relative py-20 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <div class="flex justify-center mb-8">
          <div class="{{ $siteBrand->isRogues ? 'bg-gradient-to-r from-sky-500 to-cyan-500' : 'bg-gradient-to-r from-indigo-600 to-violet-600' }} p-4 rounded-2xl shadow-sm">
            <svg class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>

        <h1 class="{{ $siteBrand->heroTitleClass() }}">
          About 
          <span class="{{ $siteBrand->accentGradientClass() }}">
            {{ $siteBrand->footerBrandTitle() }}
          </span>
        </h1>
        
        <p class="{{ $siteBrand->subtitleClass() }}">
          Your backstage pass to South Africa's live rock scene
        </p>
      </div>
    </div>
  </section>

  <section class="{{ $siteBrand->sectionSurfaceClass() }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="{{ $siteBrand->listingCardShellClass() }}">
        <div class="flex items-start">
          <div class="{{ $siteBrand->isRogues ? 'bg-gradient-to-r from-sky-500 to-cyan-500' : 'bg-gradient-to-r from-indigo-600 to-violet-600' }} p-3 rounded-xl shadow-sm mr-6 flex-shrink-0">
            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
            </svg>
          </div>
          <div class="flex-1">
            <h2 class="{{ $siteBrand->headingClass() }}">
              Our <span class="{{ $siteBrand->accentGradientClass() }}">Mission</span>
            </h2>
            <p class="{{ $siteBrand->bodyTextClass() }} leading-relaxed text-left max-w-none">
              Welcome to <strong class="{{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }}">{{ $siteBrand->footerBrandTitle() }}</strong> — a venue, user and artist event-sharing site — your ultimate ticket to the pulse-pounding, amp-cranking, sweat-soaked world of South Africa's live rock scene! We're a fired-up startup in South Africa, fuelled by an obsession with the raw, electric chaos of gigs, festivals, and the local legends who showcase legendary stages. Our mission? To make sure you never miss an event in a Jozi dive bar or an epic outdoor fest under the stars in the Mother City.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="{{ $siteBrand->sectionAltClass() }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="{{ $siteBrand->headingClass() }}">
          What We're <span class="{{ $siteBrand->accentGradientClass() }}">About</span>
        </h2>
        <p class="{{ $siteBrand->bodyTextClass() }}">
          Building a community of music lovers, one gig at a time
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach([
          ['title' => 'For The Community', 'text' => 'Building a tribe of music junkies who thrive on the live vibe and support local artists.', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
          ['title' => 'All Genres', 'text' => 'From classic rock to punk, metal to indie—we\'ve got your next stage-dive-worthy night sorted.', 'icon' => 'M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z'],
          ['title' => 'Never Miss Out', 'text' => 'Real-time gig alerts and curated playlists so you\'re always in the know.', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
        ] as $feature)
        <div class="{{ $siteBrand->listingCardShellClass() }} hover:shadow-md transition-all duration-300 hover:scale-105">
          <div class="{{ $siteBrand->isRogues ? 'bg-gradient-to-r from-sky-500 to-cyan-500' : 'bg-gradient-to-r from-indigo-600 to-violet-600' }} p-3 rounded-xl shadow-sm mb-6 inline-block">
            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-white mb-3">{{ $feature['title'] }}</h3>
          <p class="{{ $siteBrand->detailMutedTextClass() }} leading-relaxed">{{ $feature['text'] }}</p>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  <section class="{{ $siteBrand->sectionSurfaceClass() }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="{{ $siteBrand->listingCardShellClass() }}">
        <h2 class="{{ $siteBrand->headingClass() }}">
          <span class="text-3xl mr-3">🎸</span>
          The <span class="{{ $siteBrand->accentGradientClass() }}">Vibe</span>
        </h2>
        <p class="{{ $siteBrand->bodyTextClass() }} text-left max-w-none mb-8">
          Our crew lives for the <span class="{{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }} font-semibold">mosh pit</span> and the <span class="{{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }} font-semibold">encore</span> that leaves your ears ringing. We're all about championing local rock gods, unearthing the best gigs, and building a tribe of music junkies who thrive on the live vibe.
        </p>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          @foreach([
            ['value' => '100%', 'label' => 'Live Music'],
            ['value' => 'SA', 'label' => 'Born & Bred'],
            ['value' => '24/7', 'label' => 'Gig Updates'],
            ['value' => '🤘', 'label' => 'Rock On'],
          ] as $stat)
          <div class="{{ $siteBrand->isRogues ? 'bg-slate-800/80 border border-slate-700' : 'bg-black/40 border border-white/10' }} rounded-xl p-6 text-center">
            <div class="text-2xl font-bold {{ $siteBrand->isRogues ? 'text-sky-400' : 'text-indigo-400' }} mb-1">{{ $stat['value'] }}</div>
            <div class="text-sm {{ $siteBrand->detailMutedTextClass() }}">{{ $stat['label'] }}</div>
          </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>

  <section class="{{ $siteBrand->sectionAltClass() }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="{{ $siteBrand->headingClass() }}">
          Get <span class="{{ $siteBrand->accentGradientClass() }}">Involved</span>
        </h2>
        <p class="{{ $siteBrand->bodyTextClass() }}">
          Got a tip on a killer gig or a band we need to shout out? Send it our way! Follow us on Facebook for real-time gig alerts and playlists to get you pumped.
        </p>
      </div>
      
      <div class="max-w-md mx-auto">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-8 text-white text-center shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
          <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </div>
          <h3 class="text-xl font-bold mb-2">Follow Us on Facebook</h3>
          <p class="text-blue-100 mb-6">Get real-time gig alerts, backstage stories, and playlists to get you pumped!</p>
          <a href="https://www.facebook.com/profile.php?id=61565846829473" target="_blank" rel="noopener noreferrer" 
             class="inline-block bg-white text-blue-600 font-semibold px-6 py-3 rounded-xl hover:bg-gray-100 transition-colors duration-200 shadow-sm">
            Join The Tribe
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="{{ $siteBrand->ctaSectionClass() }}">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-white mb-4">Catch you in the front row! 🤘</h2>
      <p class="{{ $siteBrand->bodyTextClass() }} mb-8">— The {{ $siteBrand->footerBrandTitle() }} Crew</p>
      <a href="{{ route('events.index') }}" class="btn-primary inline-flex items-center px-8 py-4 rounded-2xl font-semibold text-lg">
        <span>Start Exploring</span>
        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>
    </div>
  </section>

</div>
@endsection
