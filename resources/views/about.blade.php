@extends('layouts.app')

@section('title', 'About - My Gig Guide')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
  
  <!-- Hero Section -->
  <section class="relative py-20 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <!-- Icon -->
        <div class="flex justify-center mb-8">
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-4 rounded-2xl shadow-sm">
            <svg class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>

        <!-- Title with Gradient -->
        <h1 class="text-4xl md:text-6xl font-bold text-gray-900 mb-6">
          About 
          <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
            My Gig Guide
          </span>
        </h1>
        
        <p class="text-xl md:text-2xl text-gray-600 mb-12 max-w-3xl mx-auto">
          Your backstage pass to South Africa's live rock scene
        </p>
      </div>
    </div>
  </section>

  <!-- Mission Section -->
  <section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white border border-purple-100 rounded-2xl p-8 md:p-12 shadow-sm">
        <div class="flex items-start">
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-3 rounded-xl shadow-sm mr-6 flex-shrink-0">
            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
            </svg>
          </div>
          <div class="flex-1">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
              Our 
              <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                Mission
              </span>
            </h2>
            <p class="text-lg text-gray-600 leading-relaxed">
              Welcome to <strong class="text-purple-600">My Gig Guide</strong> - a venue, user and artist event-sharing site - your ultimate ticket to the pulse-pounding, amp-cranking, sweat-soaked world of South Africa's live rock scene! We're a fired-up startup in South Africa, fuelled by an obsession with the raw, electric chaos of gigs, festivals, and the local legends who showcase legendary stages. Our mission? To make sure you never miss an event in a Jozi dive bar or an epic outdoor fest under the stars in the Mother City.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="py-16 bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          What We're 
          <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
            About
          </span>
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Building a community of music lovers, one gig at a time
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="bg-white border border-purple-100 rounded-2xl p-8 shadow-sm hover:shadow-md transition-all duration-300 hover:scale-105">
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-3 rounded-xl shadow-sm mb-6 inline-block">
            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">For The Community</h3>
          <p class="text-gray-600 leading-relaxed">
            Building a tribe of music junkies who thrive on the live vibe and support local artists.
          </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white border border-purple-100 rounded-2xl p-8 shadow-sm hover:shadow-md transition-all duration-300 hover:scale-105">
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-3 rounded-xl shadow-sm mb-6 inline-block">
            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">All Genres</h3>
          <p class="text-gray-600 leading-relaxed">
            From classic rock to punk, metal to indie—we've got your next stage-dive-worthy night sorted.
          </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-white border border-purple-100 rounded-2xl p-8 shadow-sm hover:shadow-md transition-all duration-300 hover:scale-105">
          <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-3 rounded-xl shadow-sm mb-6 inline-block">
            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Never Miss Out</h3>
          <p class="text-gray-600 leading-relaxed">
            Real-time gig alerts and curated playlists so you're always in the know.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- The Vibe Section -->
  <section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white border border-purple-100 rounded-2xl p-8 md:p-12 shadow-sm">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">
          <span class="text-3xl mr-3">🎸</span>
          The 
          <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
            Vibe
          </span>
        </h2>
        <p class="text-lg text-gray-600 leading-relaxed mb-8">
          Our crew lives for the <span class="text-purple-600 font-semibold">mosh pit</span> and the <span class="text-purple-600 font-semibold">encore</span> that leaves your ears ringing. We're all about championing local rock gods, unearthing the best gigs, and building a tribe of music junkies who thrive on the live vibe. From classic rock to punk, metal to indie, we've got your next stage-dive-worthy night sorted.
        </p>
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-gradient-to-br from-purple-50 to-blue-50 border border-purple-100 rounded-xl p-6 text-center">
            <div class="text-2xl font-bold text-purple-600 mb-1">100%</div>
            <div class="text-sm text-gray-600">Live Music</div>
          </div>
          <div class="bg-gradient-to-br from-purple-50 to-blue-50 border border-purple-100 rounded-xl p-6 text-center">
            <div class="text-2xl font-bold text-blue-600 mb-1">SA</div>
            <div class="text-sm text-gray-600">Born & Bred</div>
          </div>
          <div class="bg-gradient-to-br from-purple-50 to-blue-50 border border-purple-100 rounded-xl p-6 text-center">
            <div class="text-2xl font-bold text-purple-600 mb-1">24/7</div>
            <div class="text-sm text-gray-600">Gig Updates</div>
          </div>
          <div class="bg-gradient-to-br from-purple-50 to-blue-50 border border-purple-100 rounded-xl p-6 text-center">
            <div class="text-2xl font-bold mb-1">🤘</div>
            <div class="text-sm text-gray-600">Rock On</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Get Involved Section -->
  <section class="py-16 bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Get 
          <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
            Involved
          </span>
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Got a tip on a killer gig or a band we need to shout out? Send it our way! Join us in boosting SA's rock scene and let's keep the music alive. Follow us on Facebook for real-time gig alerts and playlists to get you pumped.
        </p>
      </div>
      
      <!-- Social Media Card -->
      <div class="max-w-md mx-auto">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-8 text-white text-center shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
          <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
          </div>
          <h3 class="text-xl font-bold mb-2">Follow Us on Facebook</h3>
          <p class="text-blue-100 mb-6">
            Get real-time gig alerts, backstage stories, and playlists to get you pumped!
          </p>
          <a href="https://www.facebook.com/profile.php?id=61565846829473" target="_blank" rel="noopener noreferrer" 
             class="inline-block bg-white text-blue-600 font-semibold px-6 py-3 rounded-xl hover:bg-gray-100 transition-colors duration-200 shadow-sm">
            Join The Tribe
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Sign Off / CTA Section -->
  <section class="py-16 bg-white">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-bold text-gray-900 mb-4">
        Catch you in the front row! 🤘
      </h2>
      <p class="text-lg text-gray-600 mb-8">
        — The My Gig Guide Crew
      </p>
      <a href="{{ route('events.index') }}"
         class="inline-flex items-center bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-8 py-4 rounded-2xl font-semibold text-lg transition-all duration-300 shadow-sm hover:shadow-md hover:scale-105">
        <span>Start Exploring</span>
        <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </a>
    </div>
  </section>

</div>
@endsection




