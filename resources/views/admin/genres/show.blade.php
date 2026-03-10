@extends('layouts.admin')

@section('title', 'Genre Details - Admin Panel')
@section('description', 'View genre details.')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-purple-600">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.genres.index') }}" class="hover:text-purple-600">Genres</a>
            <span>/</span>
            <span class="text-gray-900">Details</span>
        </div>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Genre: {{ $genre->name }}</h1>
            <div class="flex space-x-2">
                <a href="{{ route('admin.genres.edit', $genre) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    Edit Genre
                </a>
                <form method="POST" action="{{ route('admin.genres.destroy', $genre) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this genre?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Delete Genre
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Genre Details Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Genre Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Name</label>
                <p class="text-gray-900">{{ $genre->name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Slug</label>
                <p class="text-gray-900">{{ $genre->slug }}</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                <p class="text-gray-900">{{ $genre->description ?: 'No description provided' }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $genre->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $genre->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                <p class="text-gray-900">{{ $genre->created_at->format('F d, Y \a\t h:i A') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                <p class="text-gray-900">{{ $genre->updated_at->format('F d, Y \a\t h:i A') }}</p>
            </div>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Events</p>
                        <p class="text-2xl font-bold text-purple-600">{{ $genre->events_count }}</p>
                    </div>
                    <svg class="w-12 h-12 text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Artists</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $genre->artists_count }}</p>
                    </div>
                    <svg class="w-12 h-12 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection










