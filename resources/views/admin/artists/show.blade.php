@extends('layouts.admin')

@section('title', 'Artist Details - Admin Panel')
@section('description', 'View artist details and manage artist information.')

@section('content')
<div class="p-6">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $artist->stage_name }}</h1>
                    <p class="text-gray-600">Artist Details & Management</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.artists.edit', $artist) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Artist</span>
                    </a>
                    <a href="{{ route('admin.artists.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Back to Artists</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Artist Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Artist Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Stage Name</label>
                            <p class="text-gray-900">{{ $artist->stage_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Real Name</label>
                            <p class="text-gray-900">{{ $artist->real_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Genre</label>
                            <p class="text-gray-900">{{ $artist->genre }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Phone Number</label>
                            <p class="text-gray-900">{{ $artist->phone_number ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Instagram</label>
                            <p class="text-gray-900">
                                @if($artist->instagram)
                                    <a href="{{ $artist->instagram }}" target="_blank" class="text-purple-600 hover:text-purple-700">{{ $artist->instagram }}</a>
                                @else
                                    Not provided
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Facebook</label>
                            <p class="text-gray-900">
                                @if($artist->facebook)
                                    <a href="{{ $artist->facebook }}" target="_blank" class="text-purple-600 hover:text-purple-700">{{ $artist->facebook }}</a>
                                @else
                                    Not provided
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Twitter</label>
                            <p class="text-gray-900">
                                @if($artist->twitter)
                                    <a href="{{ $artist->twitter }}" target="_blank" class="text-purple-600 hover:text-purple-700">{{ $artist->twitter }}</a>
                                @else
                                    Not provided
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-gray-500">Bio</label>
                        <p class="text-gray-900 mt-1">{{ $artist->bio }}</p>
                    </div>
                </div>

                <!-- Profile Picture -->
                @if($artist->profile_picture)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Profile Picture</h2>
                        <div class="flex justify-center">
                            <img src="{{ Storage::url($artist->profile_picture) }}" alt="Profile Picture" class="h-64 w-64 object-cover rounded-lg">
                        </div>
                    </div>
                @endif

                <!-- Upcoming Events -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Events</h2>
                    @if($artist->events && $artist->events->count() > 0)
                        <div class="space-y-3">
                            @foreach($artist->events->where('date', '>=', now())->take(5) as $event)
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
                        <p class="text-gray-500">No upcoming events for this artist</p>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- User Account -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">User Account</h2>
                    @if($artist->user)
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">{{ substr($artist->user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="text-gray-900 font-medium">{{ $artist->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $artist->user->email }}</p>
                                <p class="text-sm text-gray-500">Username: {{ $artist->user->username }}</p>
                            </div>
                        </div>
                        @if(auth()->check() && auth()->user()->hasRole('superuser'))
                            <form method="POST" action="{{ route('admin.users.update-email', $artist->user) }}" class="mt-4 space-y-3">
                                @csrf
                                @method('PATCH')
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Update Email Address</label>
                                    <div class="mt-1 flex flex-col sm:flex-row sm:items-center sm:space-x-3 space-y-3 sm:space-y-0">
                                        <div class="flex-1">
                                            <input
                                                id="email"
                                                name="email"
                                                type="email"
                                                required
                                                value="{{ old('email', $artist->user->email) }}"
                                                class="input"
                                            >
                                            @error('email')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Email</button>
                                    </div>
                                </div>
                                <label class="inline-flex items-center space-x-2 text-sm text-gray-600">
                                    <input type="checkbox" name="sync_related" value="1" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500" {{ old('sync_related') ? 'checked' : '' }}>
                                    <span>Also update linked artist/organiser contact emails</span>
                                </label>
                                <p class="text-xs text-gray-500">Saving will reset email verification for this user until they confirm the new address.</p>
                            </form>
                        @endif
                    @else
                        <p class="text-gray-500">No user account linked</p>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="{{ route('artists.show', $artist) }}" target="_blank" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                            View Public Profile
                        </a>
                        <form action="{{ route('features.checkout') }}" method="GET" class="flex items-center space-x-2">
                            <input type="hidden" name="featureable_type" value="artist" />
                            <input type="hidden" name="featureable_id" value="{{ $artist->id }}" />
                            <select name="feature_id" class="input">
                                @foreach(\App\Models\PaidFeature::where('applies_to','artist')->where('is_active', true)->get() as $feature)
                                    <option value="{{ $feature->id }}">Boost: {{ $feature->name }} ({{ $feature->currency }} {{ number_format($feature->price_cents/100, 2) }})</option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary">Boost Artist</button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.artists.destroy', $artist) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this artist?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                                Delete Artist
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Artist Statistics -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Artist Statistics</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Total Events</span>
                            <span class="text-sm text-gray-900">{{ $artist->events ? $artist->events->count() : 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Upcoming Events</span>
                            <span class="text-sm text-gray-900">{{ $artist->events ? $artist->events->where('date', '>=', now())->count() : 0 }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Created</span>
                            <span class="text-sm text-gray-900">{{ $artist->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Last Updated</span>
                            <span class="text-sm text-gray-900">{{ $artist->updated_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">ID</span>
                            <span class="text-sm text-gray-900">#{{ $artist->id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
