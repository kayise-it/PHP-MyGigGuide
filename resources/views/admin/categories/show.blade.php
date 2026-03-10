@extends('layouts.admin')

@section('title', 'Category Details - Admin Panel')
@section('description', 'View category details.')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <div class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-purple-600">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.categories.index') }}" class="hover:text-purple-600">Categories</a>
            <span>/</span>
            <span class="text-gray-900">Details</span>
        </div>
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Category: {{ $category->name }}</h1>
            <div class="flex space-x-2">
                <a href="{{ route('admin.categories.edit', $category) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    Edit Category
                </a>
                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors duration-200">
                        Delete Category
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Category Details Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Category Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Name</label>
                <div class="flex items-center space-x-2">
                    @if($category->icon)
                        <span class="text-2xl">{{ $category->icon }}</span>
                    @endif
                    <p class="text-gray-900">{{ $category->name }}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Slug</label>
                <p class="text-gray-900">{{ $category->slug }}</p>
            </div>

            @if($category->color)
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Color</label>
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded border border-gray-300" style="background-color: {{ $category->color }}"></div>
                    <span class="text-gray-900">{{ $category->color }}</span>
                </div>
            </div>
            @endif

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-500 mb-1">Description</label>
                <p class="text-gray-900">{{ $category->description ?: 'No description provided' }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Total Events</label>
                <p class="text-gray-900">{{ $category->events_count }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Created</label>
                <p class="text-gray-900">{{ $category->created_at->format('F d, Y \a\t h:i A') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Last Updated</label>
                <p class="text-gray-900">{{ $category->updated_at->format('F d, Y \a\t h:i A') }}</p>
            </div>
        </div>
    </div>

    <!-- Recent Events Card -->
    @if($recentEvents->count() > 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Events in this Category</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentEvents as $event)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $event->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600">{{ $event->date->format('M d, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-600">{{ $event->venue->name ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $event->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('admin.events.show', $event) }}" class="text-purple-600 hover:text-purple-900">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No events yet</h3>
        <p class="mt-1 text-sm text-gray-500">No events have been assigned to this category yet.</p>
    </div>
    @endif
</div>
@endsection


