@extends('layouts.admin')

@section('title', 'Venue Details - Admin Panel')
@section('description', 'View venue details and manage venue information.')

@section('content')
<div class="p-6">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $venue->name }}</h1>
                    <p class="text-gray-600">Venue Details & Management</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.venues.edit', $venue) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Venue</span>
                    </a>
                    <a href="{{ route('admin.venues.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Back to Venues</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Venue Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Venue Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Venue Name</label>
                            <p class="text-gray-900">{{ $venue->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">City</label>
                            <p class="text-gray-900">{{ $venue->city }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Address</label>
                            <p class="text-gray-900">{{ $venue->address }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Capacity</label>
                            <p class="text-gray-900">{{ $venue->capacity ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Latitude</label>
                            <p class="text-gray-900">{{ $venue->latitude ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Longitude</label>
                            <p class="text-gray-900">{{ $venue->longitude ?? 'Not specified' }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="text-gray-900 mt-1">{{ $venue->description }}</p>
                    </div>
                </div>

                <!-- Venue Images -->
                @if($venue->main_picture || $venue->venue_gallery)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Venue Images</h2>
                        
                        @if($venue->main_picture)
                            <div class="mb-4">
                                <label class="text-sm font-medium text-gray-500">Main Picture</label>
                                <div class="mt-2">
                                    <img src="{{ Storage::url($venue->main_picture) }}" alt="Main Picture" class="h-48 w-full object-cover rounded-lg">
                                </div>
                            </div>
                        @endif

                        @if($venue->venue_gallery)
                            <div>
                                <label class="text-sm font-medium text-gray-500">Gallery Images</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-2">
                                    @php
                                        $galleryImages = is_string($venue->venue_gallery)
                                            ? json_decode($venue->venue_gallery, true)
                                            : ($venue->venue_gallery ?? []);
                                        if (!is_array($galleryImages)) { $galleryImages = []; }
                                    @endphp
                                    @foreach($galleryImages as $image)
                                        <img src="{{ Storage::url($image) }}" alt="Gallery Image" class="h-32 w-full object-cover rounded-lg">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Upcoming Events -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Events</h2>
                    @if($venue->events && $venue->events->count() > 0)
                        <div class="space-y-3">
                            @foreach($venue->events->where('date', '>=', now())->take(5) as $event)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $event->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $event->date->format('M d, Y') }} at {{ $event->time }}</p>
                                    </div>
                                    <a href="{{ route('admin.events.show', $event) }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                        View Event
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No upcoming events at this venue</p>
                    @endif
                </div>

                <!-- Venue Management (Superuser Only) -->
                <x-venue-owner-management :venue="$venue" />
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Venue Owner -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Venue Owner</h2>
                    @if($venue->user)
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">{{ substr($venue->user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium">{{ $venue->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $venue->user->email }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">No owner information available</p>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('venues.show', $venue) }}" target="_blank" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            View Public Page
                        </a>
                        
                        <form method="POST" action="{{ route('admin.venues.destroy.post', $venue) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this venue?')">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                Delete Venue
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Venue Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Venue Statistics</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Total Events</span>
                            <span class="text-sm text-gray-900">{{ $venue->events ? $venue->events->count() : 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Upcoming Events</span>
                            <span class="text-sm text-gray-900">{{ $venue->events ? $venue->events->where('date', '>=', now())->count() : 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Created</span>
                            <span class="text-sm text-gray-900">{{ $venue->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Last Updated</span>
                            <span class="text-sm text-gray-900">{{ $venue->updated_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">ID</span>
                            <span class="text-sm text-gray-900">#{{ $venue->id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

