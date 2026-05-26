@extends('layouts.app')

@section('title', 'Venue Owner Dashboard - My Gig Guide')
@section('description', 'Manage your venues and hosted events.')

@section('content')
<div class="{{ $siteBrand->dashboardShellClass() }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Welcome back, {{ Auth::user()->name }}!
                    </h1>
                    <p class="text-gray-600 mt-2">Manage your venues and hosted events</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('venues.my-requests') }}" class="btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        My Venue Requests
                    </a>
                    <a href="{{ route('venues.create') }}" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Venue
                    </a>
                </div>
            </div>
        </div>

        @if($statsEnabled)
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Venues</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_venues'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Events</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_events'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Upcoming</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['upcoming_events'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-yellow-100">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Avg Rating</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_rating'], 1) }}/5</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Venues Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">Your Venues</h2>
                <a href="{{ route('venues.create') }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Venue
                </a>
            </div>
            
            @if($venues->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($venues as $venue)
                        <x-venue-card :venue="$venue" />
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p class="text-gray-500">No venues added yet</p>
                    <a href="{{ route('venues.create') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium mt-2 inline-block">
                        Add your first venue
                    </a>
                </div>
            @endif
        </div>

        <!-- Upcoming Events at Your Venues -->
        @if($upcomingEvents->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Upcoming Events at Your Venues</h2>
                    <a href="{{ route('events.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                        View All
                    </a>
                </div>
                
                <div class="space-y-4">
                    @foreach($upcomingEvents->take(5) as $event)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-900">{{ $event->name }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $event->venue->name }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $event->date->format('M j, Y') }} at {{ $event->time ? $event->time->format('g:i A') : 'TBA' }}
                                    </p>
                                    @if($event->artists->count() > 0)
                                        <p class="text-xs text-purple-600 mt-1">
                                            Artists: {{ $event->artists->pluck('stage_name')->implode(', ') }}
                                        </p>
                                    @endif
                                </div>
                                <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                    {{ $event->status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
