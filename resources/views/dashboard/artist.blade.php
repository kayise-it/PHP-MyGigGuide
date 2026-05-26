@extends('layouts.app')

@section('title', 'Artist Dashboard - My Gig Guide')
@section('description', 'Manage your artist profile, upcoming events, and performance history.')

@section('content')
<div class="{{ $siteBrand->dashboardShellClass() }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Welcome back, {{ $artist->stage_name ?? $artist->real_name }}!
                    </h1>
                    <p class="text-gray-600 mt-2">Manage your music career and upcoming performances</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if(auth()->user() && auth()->user()->can('edit-artist-profile'))
                    <a href="{{ route('artists.edit', $artist->id) }}" class="btn-secondary whitespace-nowrap">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Profile
                    </a>
                    @endif
                    <button type="button" onclick="openClaimVenueModal()" class="btn-secondary whitespace-nowrap">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Claim Venue
                    </button>
                    <a href="{{ route('events.create') }}" class="btn-primary whitespace-nowrap">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Event
                    </a>
                    <a href="{{ route('venues.create') }}" class="btn-primary whitespace-nowrap">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Add Venue
                    </a>
                </div>
            </div>
            
            <!-- Boost Profile Section - Moved below main buttons -->
            @auth
            @if(\App\Models\SiteSetting::get('boost_profile_enabled', true) && \App\Models\PaidFeature::where('applies_to','artist')->where('is_active', true)->count() > 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <form action="{{ route('features.checkout') }}" method="GET" class="flex flex-wrap items-center gap-3">
                    <input type="hidden" name="featureable_type" value="artist">
                    <input type="hidden" name="featureable_id" value="{{ $artist->id }}">
                    <label class="text-sm font-medium text-gray-700">Boost Your Profile:</label>
                    <select name="feature_id" class="input flex-1 min-w-[200px] max-w-[300px]">
                        @foreach(\App\Models\PaidFeature::where('applies_to','artist')->where('is_active', true)->get() as $feature)
                            <option value="{{ $feature->id }}">Boost: {{ $feature->name }} ({{ $feature->currency }} {{ number_format($feature->price_cents/100, 2) }})</option>
                        @endforeach
                    </select>
                    <button class="btn-primary whitespace-nowrap" type="submit">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Boost Profile
                    </button>
                </form>
            </div>
            @endif
            @endauth
        </div>

        @if($statsEnabled)
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        <div class="p-3 rounded-xl bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                        <div class="p-3 rounded-xl bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Venues Owned</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['venues_owned'] }}</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Upcoming Events -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">My Events</h2>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('events.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                            View All
                        </a>
                        <a href="{{ route('events.create') }}" class="btn-primary text-sm py-2 px-3">
                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add Event
                        </a>
                    </div>
                </div>
                
                @if($upcomingEvents->count() > 0 || $pastEvents->count() > 0)
                    <div class="space-y-4">
                        @if($upcomingEvents->count() > 0)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Upcoming</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($upcomingEvents->take(6) as $event)
                                        <div class="relative group">
                                            <x-event-card :event="$event" />
                                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2 z-20" onclick="event.stopPropagation();">
                                                <a href="{{ route('events.edit', $event) }}" onclick="event.stopPropagation();" class="p-2 bg-white/90 hover:bg-white rounded-lg shadow-md" title="Edit Event">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?');" onclick="event.stopPropagation();">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 bg-white/90 hover:bg-white rounded-lg shadow-md" title="Delete Event" onclick="event.stopPropagation();">
                                                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                        @if($pastEvents->count() > 0)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">Past Events</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($pastEvents->take(3) as $event)
                                        <div class="relative group opacity-75">
                                            <x-event-card :event="$event" />
                                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2 z-20" onclick="event.stopPropagation();">
                                                <a href="{{ route('events.edit', $event) }}" onclick="event.stopPropagation();" class="p-2 bg-white/90 hover:bg-white rounded-lg shadow-md" title="Edit Event">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>
                                                <form action="{{ route('events.destroy', $event) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?');" onclick="event.stopPropagation();">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 bg-white/90 hover:bg-white rounded-lg shadow-md" title="Delete Event" onclick="event.stopPropagation();">
                                                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500">No events scheduled</p>
                        <a href="{{ route('events.create') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium mt-2 inline-block">
                            Create your first event
                        </a>
                    </div>
                @endif
            </div>

            <!-- Recent Reviews -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Recent Reviews</h2>
                    @if($statsEnabled)
                        <span class="text-sm text-gray-500">{{ $stats['total_ratings'] }} total</span>
                    @endif
                </div>
                
                @if($ratings->count() > 0)
                    <div class="space-y-4">
                        @foreach($ratings as $rating)
                            <div class="border border-gray-200 rounded-xl p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center">
                                        <div class="flex text-yellow-400">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="h-4 w-4 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="ml-2 text-sm font-medium text-gray-900">{{ $rating->user->name }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $rating->created_at->diffForHumans() }}</span>
                                </div>
                                @if($rating->review)
                                    <p class="text-sm text-gray-600">{{ Str::limit($rating->review, 100) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <p class="text-gray-500">No reviews yet</p>
                        <p class="text-sm text-gray-400 mt-1">Reviews will appear here once you start performing</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Venues Section -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">My Venues</h2>
                <a href="{{ route('venues.create') }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Venue
                </a>
            </div>
            
            @if($venues->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($venues as $venue)
                        <div class="relative group">
                            <x-venue-card :venue="$venue" />
                            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2 z-20" onclick="event.stopPropagation();">
                                <a href="{{ route('venues.edit', $venue) }}" onclick="event.stopPropagation();" class="p-2 bg-white/90 hover:bg-white rounded-lg shadow-md" title="Edit Venue">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('venues.destroy', $venue) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this venue? This action cannot be undone.');" onclick="event.stopPropagation();">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-white/90 hover:bg-white rounded-lg shadow-md" title="Delete Venue" onclick="event.stopPropagation();">
                                        <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p class="text-gray-500 text-lg mb-2">No venues added yet</p>
                    <p class="text-gray-400 text-sm mb-4">Add your first venue to start managing your performance spaces</p>
                    <a href="{{ route('venues.create') }}" class="btn-primary inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Your First Venue
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@include('dashboard.partials.claim-venue-modal')
@endsection
