@extends('layouts.app')

@section('title', 'Events Map - My Gig Guide')
@section('description', 'Discover events near you with our interactive map')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <!-- Header -->
    <div class="text-center mb-12">
      <div class="flex justify-center mb-6">
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-3 rounded-xl shadow-sm">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
      </div>
      <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
        Events 
        <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
          Near You
        </span>
      </h1>
      <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
        Discover what's happening around your location. Click on any marker to see event details.
      </p>
    </div>

    <!-- Map -->
    <div class="mb-8">
      <x-google-map 
        :events="$events" 
        :categories-list="$categories"
        height="600px" 
        :show-legend="true" 
        :compact="false"
        :radius-km="100"
        id="main-map"
      />
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white border border-purple-100 rounded-xl p-6 text-center shadow-sm">
        <div class="text-2xl font-bold text-purple-600 mb-2">{{ $events->count() }}</div>
        <div class="text-gray-600">Upcoming Events</div>
      </div>
      <div class="bg-white border border-purple-100 rounded-xl p-6 text-center shadow-sm">
        <div class="text-2xl font-bold text-blue-600 mb-2">{{ $venues->count() }}</div>
        <div class="text-gray-600">Active Venues</div>
      </div>
      <div class="bg-white border border-purple-100 rounded-xl p-6 text-center shadow-sm">
        <div class="text-2xl font-bold text-pink-600 mb-2">{{ $events->count() > 0 ? 'Live' : 'None' }}</div>
        <div class="text-gray-600">Map Status</div>
      </div>
    </div>

    <!-- Events List -->
    @if($events->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">All Events</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($events as $event)
        <div class="bg-white border border-purple-100 rounded-xl p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:scale-105">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-bold text-gray-900">{{ $event->name }}</h3>
            <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-medium">
              @if($event->price)
                R{{ number_format($event->price, 2) }}
              @else
                Free
              @endif
            </span>
          </div>
          
          <div class="space-y-2 text-gray-600 text-sm">
            @if($event->venue)
            <div class="flex items-center">
              <svg class="h-4 w-4 text-purple-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>{{ $event->venue->name }}</span>
            </div>
            @endif
            <div class="flex items-center">
              <svg class="h-4 w-4 text-purple-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ $event->date->format('M d, Y') }}</span>
            </div>
            <div class="flex items-center">
              <svg class="h-4 w-4 text-purple-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ $event->time->format('H:i A') }}</span>
            </div>
          </div>

          <a
            href="{{ route('events.show', $event) }}"
            class="mt-4 w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white py-2 px-4 rounded-lg font-medium transition-all duration-300 flex items-center justify-center gap-2 hover:scale-105"
          >
            <span>View Details</span>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        </div>
        @endforeach
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
