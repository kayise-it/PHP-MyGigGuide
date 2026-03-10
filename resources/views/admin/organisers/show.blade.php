@extends('layouts.admin')

@section('title', 'Organiser Details - Admin Panel')
@section('description', 'View organiser details and manage organiser information.')

@section('content')
<div class="p-6">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $organiser->name }}</h1>
                    <p class="text-gray-600">Organiser Details & Management</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.organisers.edit', $organiser) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Organiser</span>
                    </a>
                    <a href="{{ route('admin.organisers.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Back to Organisers</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Organiser Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Organiser Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Organiser Name</label>
                            <p class="text-gray-900">{{ $organiser->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Company</label>
                            <p class="text-gray-900">{{ $organiser->company }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Location</label>
                            <p class="text-gray-900">{{ $organiser->location }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Website</label>
                            <p class="text-gray-900">
                                @if($organiser->website)
                                    <a href="{{ $organiser->website }}" target="_blank" class="text-purple-600 hover:text-purple-700">{{ $organiser->website }}</a>
                                @else
                                    Not provided
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Social Media</label>
                            <p class="text-gray-900">{{ $organiser->social_media ?? 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Bio</label>
                        <p class="text-gray-900 mt-1">{{ $organiser->bio }}</p>
                    </div>
                </div>

                <!-- Profile Picture -->
                @if($organiser->profile_picture)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Picture</h2>
                        <div class="flex justify-center">
                            <img src="{{ Storage::url($organiser->profile_picture) }}" alt="Profile Picture" class="h-64 w-64 object-cover rounded-lg">
                        </div>
                    </div>
                @endif

                <!-- Upcoming Events -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Events</h2>
                    @if($organiser->events && $organiser->events->count() > 0)
                        <div class="space-y-3">
                            @foreach($organiser->events->where('date', '>=', now())->take(5) as $event)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <h3 class="font-medium text-gray-900">{{ $event->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $event->date->format('M d, Y') }} at {{ $event->time }}</p>
                                        <p class="text-sm text-gray-500">{{ $event->venue->name ?? 'No venue' }}</p>
                                    </div>
                                    <a href="{{ route('admin.events.show', $event) }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                        View Event
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No upcoming events for this organiser</p>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- User Account -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">User Account</h2>
                    @if($organiser->user)
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">{{ substr($organiser->user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium">{{ $organiser->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $organiser->user->email }}</p>
                                <p class="text-sm text-gray-500">Username: {{ $organiser->user->username }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">No user account linked</p>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('organisers.show', $organiser) }}" target="_blank" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            View Public Profile
                        </a>
                        
                        <form method="POST" action="{{ route('admin.organisers.destroy', $organiser) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this organiser?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                Delete Organiser
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Organiser Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Organiser Statistics</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Total Events</span>
                            <span class="text-sm text-gray-900">{{ $organiser->events ? $organiser->events->count() : 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Upcoming Events</span>
                            <span class="text-sm text-gray-900">{{ $organiser->events ? $organiser->events->where('date', '>=', now())->count() : 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Created</span>
                            <span class="text-sm text-gray-900">{{ $organiser->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Last Updated</span>
                            <span class="text-sm text-gray-900">{{ $organiser->updated_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">ID</span>
                            <span class="text-sm text-gray-900">#{{ $organiser->id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

