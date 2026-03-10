@extends('layouts.app')

@section('title', 'User Dashboard - My Gig Guide')
@section('description', 'Discover events and manage your favorites.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Welcome back, {{ Auth::user()->name }}!
                    </h1>
                    <p class="text-gray-600 mt-2">Discover amazing events and connect with the community</p>
                </div>
            </div>
        </div>

        @if($statsEnabled)
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Favorite Events</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['favorite_events'] }}</p>
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
                            <p class="text-sm font-medium text-gray-600">Upcoming Events</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['upcoming_events'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Reviews Given</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['ratings_given'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-orange-100">
                            <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Venues Owned</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['venues_owned'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Favorite Events -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Your Favorite Events</h2>
                    <a href="{{ route('events.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                        Browse All
                    </a>
                </div>
                
                @if($favoriteEvents->count() > 0)
                    <div class="space-y-4">
                        @foreach($favoriteEvents->take(3) as $event)
                            <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900">{{ $event->name }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $event->venue->name }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $event->date->format('M j, Y') }} at {{ $event->time ? $event->time->format('g:i A') : 'TBA' }}
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                        Favorited
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <p class="text-gray-500">No favorite events yet</p>
                        <a href="{{ route('events.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium mt-2 inline-block">
                            Discover events
                        </a>
                    </div>
                @endif
            </div>

            <!-- Upcoming Events -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Upcoming Events</h2>
                    <a href="{{ route('events.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                        View All
                    </a>
                </div>
                
                @if($upcomingEvents->count() > 0)
                    <div class="space-y-4">
                        @foreach($upcomingEvents->take(3) as $event)
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
                @else
                    <div class="text-center py-8">
                        <svg class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500">No upcoming events</p>
                        <a href="{{ route('events.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium mt-2 inline-block">
                            Browse events
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Reviews -->
        @if($userRatings->count() > 0)
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Your Recent Reviews</h2>
                    @if($statsEnabled)
                        <span class="text-sm text-gray-500">{{ $stats['ratings_given'] }} total</span>
                    @endif
                </div>
                
                <div class="space-y-4">
                    @foreach($userRatings as $rating)
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center">
                                    <div class="flex text-yellow-400">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="h-4 w-4 {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="ml-2 text-sm font-medium text-gray-900">
                                        {{ class_basename($rating->rateable_type) }}: {{ $rating->rateable->name ?? 'Unknown' }}
                                    </span>
                                </div>
                                <span class="text-xs text-gray-500">{{ $rating->created_at->diffForHumans() }}</span>
                            </div>
                            @if($rating->review)
                                <p class="text-sm text-gray-600">{{ Str::limit($rating->review, 100) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Favorite Artists Section -->
        @if($favoriteArtists->count() > 0)
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Your Favorite Artists</h2>
                    <a href="{{ route('artists.index') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                        Discover More
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($favoriteArtists->take(6) as $artist)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    @if($artist->user && $artist->user->profile_photo)
                                        <img src="{{ Storage::url($artist->user->profile_photo) }}" 
                                             alt="{{ $artist->stage_name }}" 
                                             class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                                            <span class="text-lg font-bold text-purple-600">
                                                {{ substr($artist->stage_name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-gray-900 truncate">
                                        {{ $artist->stage_name }}
                                    </h3>
                                    @if($artist->genre)
                                        <p class="text-sm text-gray-600">{{ $artist->genre }}</p>
                                    @endif
                                    @if($artist->ratings()->count() > 0)
                                        <div class="flex items-center mt-1">
                                            <div class="flex text-yellow-400">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="h-3 w-3 {{ $i <= round($artist->ratings()->avg('rating')) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.888c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="ml-1 text-xs text-gray-500">{{ number_format($artist->ratings()->avg('rating'), 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <a href="{{ route('artists.show', $artist->id) }}" 
                                   class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                    View Profile →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Your Venues Section -->
        @if($userVenues->count() > 0)
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Your Venues</h2>
                    <a href="{{ route('venues.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium transition-colors">
                        Add New Venue
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($userVenues as $venue)
                        <div class="border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow">
                            <!-- Venue Image -->
                            <div class="h-48 bg-gradient-to-br from-purple-100 to-blue-100 relative">
                                @if($venue->main_picture)
                                    <img src="{{ Storage::url($venue->main_picture) }}" 
                                         alt="{{ $venue->name }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-16 h-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                @endif
                                <!-- Event Count Badge -->
                                <div class="absolute top-3 right-3 bg-white rounded-full px-3 py-1 shadow-md">
                                    <span class="text-xs font-semibold text-purple-600">{{ $venue->events_count }} Events</span>
                                </div>
                            </div>

                            <!-- Venue Info -->
                            <div class="p-4">
                                <h3 class="font-bold text-gray-900 text-lg mb-2">{{ $venue->name }}</h3>
                                
                                @if($venue->address)
                                    <div class="flex items-start text-sm text-gray-600 mb-3">
                                        <svg class="w-4 h-4 mr-1 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="line-clamp-2">{{ $venue->address }}</span>
                                    </div>
                                @endif

                                @if($venue->capacity)
                                    <div class="flex items-center text-sm text-gray-600 mb-3">
                                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span>Capacity: {{ number_format($venue->capacity) }}</span>
                                    </div>
                                @endif

                                <!-- Upcoming Events -->
                                @if($venue->events->count() > 0)
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-xs font-semibold text-gray-700 mb-2">Upcoming Events:</p>
                                        <div class="space-y-1">
                                            @foreach($venue->events->take(2) as $event)
                                                <div class="text-xs text-gray-600">
                                                    <span class="font-medium">{{ $event->name }}</span>
                                                    <span class="text-gray-400">• {{ $event->date->format('M j') }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="mt-4 flex gap-2">
                                    <a href="{{ route('venues.show', $venue->id) }}" 
                                       class="flex-1 text-center bg-purple-600 text-white px-3 py-2 rounded-lg hover:bg-purple-700 text-sm font-medium transition-colors">
                                        View Details
                                    </a>
                                    <a href="{{ route('venues.edit', $venue->id) }}" 
                                       class="flex-1 text-center bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium transition-colors">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($userVenues->hasPages())
                    <div class="mt-6 flex justify-center">
                        {{ $userVenues->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Empty State for No Venues -->
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Your Venues</h2>
                </div>
                
                <div class="text-center py-12">
                    <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Venues Yet</h3>
                    <p class="text-gray-500 mb-6">Start by adding your first venue to host amazing events!</p>
                    <a href="{{ route('venues.create') }}" class="inline-flex items-center bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 font-medium transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Your First Venue
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

