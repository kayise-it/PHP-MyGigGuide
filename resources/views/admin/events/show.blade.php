@extends('layouts.admin')

@section('title', 'Event Details - Admin Panel')
@section('description', 'View event details and manage event information.')

@section('content')
<div class="p-6">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $event->name }}</h1>
                    <p class="text-gray-600">Event Details & Management</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.events.edit', $event) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Event</span>
                    </a>
                    <a href="{{ route('admin.events.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Back to Events</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Event Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Event Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Event Name</label>
                            <p class="text-gray-900">{{ $event->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($event->status === 'upcoming') bg-blue-100 text-blue-800
                                @elseif($event->status === 'ongoing') bg-green-100 text-green-800
                                @elseif($event->status === 'completed') bg-gray-100 text-gray-800
                                @elseif($event->status === 'cancelled') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($event->status) }}
                            </span>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Date</label>
                            <p class="text-gray-900">{{ $event->date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Time</label>
                            <p class="text-gray-900">{{ $event->time ? $event->time->format('H:i') : 'Not set' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Price</label>
                            <p class="text-gray-900">R {{ number_format($event->price, 2) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Capacity</label>
                            <p class="text-gray-900">{{ $event->capacity ?? 'Unlimited' }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Description</label>
                        <p class="text-gray-900 mt-1">{{ $event->description }}</p>
                    </div>
                </div>

                <!-- Venue Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Venue Information</h2>
                    @if($event->venue)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Venue Name</label>
                                <p class="text-gray-900">{{ $event->venue->name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Address</label>
                                <p class="text-gray-900">{{ $event->venue->address }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">City</label>
                                <p class="text-gray-900">{{ $event->venue->city }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Capacity</label>
                                <p class="text-gray-900">{{ $event->venue->capacity ?? 'Not specified' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">No venue information available</p>
                    @endif
                </div>

                <!-- Event Images -->
                @if($event->poster || $event->gallery)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Event Images</h2>
                        
                        @if($event->poster)
                            <div class="mb-4">
                                <label class="text-sm font-medium text-gray-500">Event Poster</label>
                                <div class="mt-2">
                                    <img src="{{ Storage::url($event->poster) }}" alt="Event Poster" class="h-48 w-full object-cover rounded-lg">
                                </div>
                            </div>
                        @endif

                        @if($event->gallery)
                            <div>
                                <label class="text-sm font-medium text-gray-500">Gallery Images</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-2">
                                    @foreach($event->gallery as $image)
                                        <img src="{{ Storage::url($image) }}" alt="Gallery Image" class="h-32 w-full object-cover rounded-lg">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Event Owner -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Event Owner</h2>
                    @if($event->owner)
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">{{ substr($event->owner->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium">{{ $event->owner->name }}</p>
                                <p class="text-sm text-gray-500">{{ ucfirst($event->owner_type) }}</p>
                                <p class="text-sm text-gray-500">{{ $event->owner->email }}</p>
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
                        <form method="POST" action="{{ route('admin.events.toggle-status', $event) }}" class="inline">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                                @if($event->status === 'active')
                                    Cancel Event
                                @else
                                    Activate Event
                                @endif
                            </button>
                        </form>
                        
                        <a href="{{ route('events.show', $event) }}" target="_blank" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            View Public Page
                        </a>
                        
                        <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                Delete Event
                            </button>
                        </form>
                    </div>
                </div>

              
            </div>
        </div>
    </div>
</div>
@endsection

