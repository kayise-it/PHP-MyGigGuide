@extends('layouts.admin')

@section('title', 'Venues Management - Admin Panel')
@section('description', 'Manage all venues in the system.')

@section('styles')
<style>
    .skeleton-loader {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton-loading 1.5s infinite;
    }

    @keyframes skeleton-loading {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }

    .lazy-image {
        transition: opacity 0.3s ease-in-out;
    }

    .lazy-image-container {
        position: relative;
    }
    
    [x-cloak] {
        display: none !important;
    }
</style>
@endsection

@section('content')
<div class="p-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Venues Management</h1>
            <p class="text-gray-600">Manage all venues in the system</p>
        </div>
        
        <div class="flex space-x-3">
            <!-- Export Button -->
            <a href="{{ route('admin.venues.export', request()->query()) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export CSV</span>
            </a>

            <!-- Import CSV Button -->
            <button type="button"
                    onclick="window.AdminModal && AdminModal.openFromElement('#venue-import-modal-content')"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <span>Import CSV</span>
            </button>

            <!-- Import from Excel Button (existing) -->
            <form method="POST" action="{{ route('admin.venues.import') }}" onsubmit="return confirm('Import venues from Excel spreadsheet? This will add new venues that don\'t exist yet.');">
                @csrf
                <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-lg hover:bg-yellow-700 transition-colors duration-200 flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span>Import Excel</span>
                </button>
            </form>

            <!-- Create Venue Button -->
            <a href="{{ route('admin.venues.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Create Venue</span>
            </a>
        </div>
    </div>

    <!-- Import CSV Modal body content (rendered inside global admin modal) -->
    <div id="venue-import-modal-content" class="hidden">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Import Venues from CSV</h3>
        <form action="{{ route('admin.venues.import-csv') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4 space-y-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Upload a CSV file with venue data. The file should match the export format.
                </p>
                <a href="{{ route('admin.venues.import-template') }}" class="inline-flex items-center text-xs text-purple-600 hover:text-purple-800 hover:underline">
                    Download CSV template
                </a>
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="window.AdminModal && AdminModal.close()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Import
                </button>
            </div>
        </form>
    </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" id="ajax-search-form" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search venues..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div>
                <select name="venue_type" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="concert_hall" {{ request('venue_type') == 'concert_hall' ? 'selected' : '' }}>Concert Hall</option>
                    <option value="club" {{ request('venue_type') == 'club' ? 'selected' : '' }}>Club</option>
                    <option value="bar" {{ request('venue_type') == 'bar' ? 'selected' : '' }}>Bar</option>
                    <option value="outdoor" {{ request('venue_type') == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                    <option value="theater" {{ request('venue_type') == 'theater' ? 'selected' : '' }}>Theater</option>
                    <option value="stadium" {{ request('venue_type') == 'stadium' ? 'selected' : '' }}>Stadium</option>
                    <option value="other" {{ request('venue_type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>
            @if(request()->hasAny(['search', 'venue_type']))
            <a href="#" data-clear-filters class="text-purple-600 hover:text-purple-800 px-4 py-2 flex items-center font-medium transition-colors duration-200">
                Clear Filters
            </a>
            @endif
        </form>
    </div>

    <!-- Venues Table -->
    <div id="ajax-results">
    <form id="bulk-action-form" method="POST" action="{{ route('admin.venues.bulk-action') }}">
        @csrf
        <!-- DEBUG: Route URL = {{ route('admin.venues.bulk-action') }} -->
        <!-- DEBUG: CSRF Token = {{ csrf_token() }} -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Bulk Actions Top -->
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <select name="bulk_action" id="bulk-action-select" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Bulk Actions</option>
                        <option value="delete">Delete</option>
                        <option value="export">Export</option>
                    </select>
                    <button type="submit" class="px-4 py-1.5 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200" onclick="return confirmBulkAction(event)">
                        Apply
                    </button>
                    <span id="selected-count" class="text-sm text-gray-600">0 selected</span>
                </div>
            </div>

            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer" onclick="toggleAllCheckboxes(this)">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($venues as $venue)
                    <tr class="hover:bg-gray-50 cursor-pointer venue-row" data-venue-url="{{ route('admin.venues.show', $venue) }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="venue_ids[]" value="{{ $venue->id }}" class="venue-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500 cursor-pointer" onchange="updateSelectedCount()" onclick="event.stopPropagation()">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-12 w-12">
                                    @if($venue->main_picture)
                                        <div class="lazy-image-container h-12 w-12 rounded-lg overflow-hidden bg-gray-200 relative">
                                            <!-- Skeleton loader -->
                                            <div class="skeleton-loader absolute inset-0 bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200 animate-pulse"></div>
                                            <!-- Actual image -->
                                            <img 
                                                class="lazy-image h-12 w-12 rounded-lg object-cover opacity-0 transition-opacity duration-300" 
                                                data-src="{{ Storage::url($venue->main_picture) }}" 
                                                alt="{{ $venue->name }}"
                                                onload="this.style.opacity='1'; this.previousElementSibling.style.display='none';"
                                                onerror="this.style.opacity='0'; this.previousElementSibling.style.display='flex'; this.previousElementSibling.innerHTML='<svg class=\'h-6 w-6 text-gray-400\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\' /></svg>';"
                                            >
                                        </div>
                                    @else
                                        <div class="h-12 w-12 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $venue->name }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($venue->description, 30) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                {{ ucfirst(str_replace('_', ' ', $venue->venue_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $venue->address }}</div>
                            <div class="text-sm text-gray-500">{{ $venue->city }}, {{ $venue->country }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($venue->capacity) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $venue->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2" onclick="event.stopPropagation()">
                                <a href="{{ route('admin.venues.show', $venue) }}" class="text-purple-600 hover:text-purple-900">View</a>
                                <a href="{{ route('admin.venues.edit', $venue) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                
                                <!-- Actions Dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click.stop="open = !open" 
                                            class="text-gray-600 hover:text-gray-900 focus:outline-none p-1">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div x-show="open" 
                                         x-cloak
                                         @click.outside="open = false"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95"
                                         class="absolute right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 py-1.5 z-50 min-w-[180px]"
                                         style="display: none;">
                                        @if(!$venue->user_id || !$venue->owner_id)
                                            <button type="button"
                                                    @click.stop="open = false; openLinkModal('venue', {{ $venue->id }}, '{{ addslashes($venue->name) }}')"
                                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 whitespace-nowrap">
                                                <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
                                                Link to User
                                            </button>
                                            <div class="border-t border-gray-100 my-1.5"></div>
                                        @endif
                                        <form method="POST" action="{{ route('admin.venues.destroy.post', $venue) }}" 
                                              onsubmit="return confirm('Are you sure you want to delete this venue?')"
                                              class="inline">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 whitespace-nowrap">
                                                <svg class="w-4 h-4 flex-shrink-0 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No venues found</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new venue.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bulk Actions Bottom -->
        @if($venues->count() > 0)
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center space-x-3">
                <select class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" onchange="syncBulkAction(this.value)">
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete</option>
                    <option value="export">Export</option>
                </select>
                <button type="submit" class="px-4 py-1.5 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200" onclick="return confirmBulkAction(event)">
                    Apply
                </button>
            </div>
        </div>
        @endif
        
        <!-- Pagination and Items Per Page -->
        <div class="px-6 py-4 border-t border-gray-200 bg-white">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <!-- Results Info and Per Page Selector -->
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-700">
                        @if($venues->total() > 0)
                            Showing 
                            <span class="font-medium">{{ $venues->firstItem() }}</span>
                            to 
                            <span class="font-medium">{{ $venues->lastItem() }}</span>
                            of 
                            <span class="font-medium">{{ $venues->total() }}</span>
                            results
                        @else
                            No results found
                        @endif
                    </div>
                    
                    @if($venues->total() > 0)
                    <div class="flex items-center gap-2">
                        <label for="per-page" class="text-sm text-gray-600">Show:</label>
                        <select id="per-page" onchange="changePerPage(this.value)" class="px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    @endif
                </div>

                <!-- Pagination Links -->
                @if($venues->hasPages())
                <nav class="inline-flex rounded-md shadow-sm" role="navigation">
                    <div class="flex items-center space-x-1">
                        {{-- First Page --}}
                        @if($venues->onFirstPage())
                            <span class="px-3 py-2 text-sm text-gray-400 bg-white border border-gray-300 rounded-l-md cursor-not-allowed">
                                « First
                            </span>
                        @else
                            <a href="{{ $venues->url(1) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 transition-colors">
                                « First
                            </a>
                        @endif

                        {{-- Previous Page --}}
                        @if($venues->onFirstPage())
                            <span class="px-3 py-2 text-sm text-gray-400 bg-white border-t border-b border-gray-300 cursor-not-allowed">
                                ‹
                            </span>
                        @else
                            <a href="{{ $venues->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                ‹
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            $start = max($venues->currentPage() - 2, 1);
                            $end = min($start + 4, $venues->lastPage());
                            $start = max($end - 4, 1);
                        @endphp

                        @if($start > 1)
                            <a href="{{ $venues->url(1) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                1
                            </a>
                            @if($start > 2)
                                <span class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300">
                                    ...
                                </span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            @if($page == $venues->currentPage())
                                <span class="px-3 py-2 text-sm font-semibold text-white bg-purple-600 border border-purple-600">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $venues->url($page) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                    {{ $page }}
                                </a>
                            @endif
                        @endfor

                        @if($end < $venues->lastPage())
                            @if($end < $venues->lastPage() - 1)
                                <span class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300">
                                    ...
                                </span>
                            @endif
                            <a href="{{ $venues->url($venues->lastPage()) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                {{ $venues->lastPage() }}
                            </a>
                        @endif

                        {{-- Next Page --}}
                        @if($venues->hasMorePages())
                            <a href="{{ $venues->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 bg-white border-t border-b border-gray-300 hover:bg-gray-50 transition-colors">
                                ›
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm text-gray-400 bg-white border-t border-b border-gray-300 cursor-not-allowed">
                                ›
                            </span>
                        @endif

                        {{-- Last Page --}}
                        @if($venues->hasMorePages())
                            <a href="{{ $venues->url($venues->lastPage()) }}" class="px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 transition-colors">
                                Last »
                            </a>
                        @else
                            <span class="px-3 py-2 text-sm text-gray-400 bg-white border border-gray-300 rounded-r-md cursor-not-allowed">
                                Last »
                            </span>
                        @endif
                    </div>
                </nav>
                @endif
            </div>
        </div>
        </div>
    </form>
    </div>
</div>

<!-- Bulk Actions JavaScript -->
<script>
    // Toggle all checkboxes
    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('.venue-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
        updateSelectedCount();
    }

    // Update selected count
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.venue-checkbox:checked');
        const count = checkboxes.length;
        const countElement = document.getElementById('selected-count');
        
        if (count > 0) {
            countElement.textContent = count + ' selected';
            countElement.classList.add('font-semibold', 'text-purple-600');
        } else {
            countElement.textContent = '0 selected';
            countElement.classList.remove('font-semibold', 'text-purple-600');
        }

        // Update "select all" checkbox state
        const allCheckboxes = document.querySelectorAll('.venue-checkbox');
        const selectAllCheckbox = document.getElementById('select-all');
        
        if (count === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (count === allCheckboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    // Confirm bulk action
    function confirmBulkAction(event) {
        const checkboxes = document.querySelectorAll('.venue-checkbox:checked');
        const action = document.getElementById('bulk-action-select').value;
        
        if (checkboxes.length === 0) {
            event.preventDefault();
            alert('Please select at least one venue.');
            return false;
        }
        
        if (!action) {
            event.preventDefault();
            alert('Please select an action.');
            return false;
        }
        
        if (action === 'delete') {
            const confirmed = confirm(`Are you sure you want to delete ${checkboxes.length} venue(s)? This action cannot be undone.`);
            if (!confirmed) {
                event.preventDefault();
                return false;
            }
        }
        
        return true;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateSelectedCount();
    });

    // Change items per page
    function changePerPage(perPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', 1); // Reset to first page
        window.location.href = url.toString();
    }

    // Sync bulk action between top and bottom dropdowns
    function syncBulkAction(value) {
        document.getElementById('bulk-action-select').value = value;
    }

    // Add error handling for form submission
    document.getElementById('bulk-action-form').addEventListener('submit', function(e) {
        console.log('=== FORM SUBMISSION DEBUG ===');
        console.log('Form action:', this.action);
        console.log('Form method:', this.method);
        console.log('CSRF token:', this.querySelector('input[name="_token"]').value);
        console.log('Selected venues:', Array.from(document.querySelectorAll('.venue-checkbox:checked')).map(cb => cb.value));
        console.log('Bulk action:', document.getElementById('bulk-action-select').value);
        console.log('Form submit event triggered');
        
        // Check if we have a valid CSRF token
        const csrfToken = this.querySelector('input[name="_token"]');
        if (!csrfToken || !csrfToken.value) {
            console.error('ERROR: No CSRF token found!');
            alert('Error: No CSRF token found. Please refresh the page and try again.');
            e.preventDefault();
            return false;
        }
        
        // Check if we have selected venues
        const selectedVenues = document.querySelectorAll('.venue-checkbox:checked');
        if (selectedVenues.length === 0) {
            console.error('ERROR: No venues selected!');
            alert('Please select at least one venue.');
            e.preventDefault();
            return false;
        }
        
        // Check if we have a bulk action selected
        const bulkAction = document.getElementById('bulk-action-select').value;
        if (!bulkAction) {
            console.error('ERROR: No bulk action selected!');
            alert('Please select a bulk action.');
            e.preventDefault();
            return false;
        }
        
        console.log('Form validation passed, submitting...');
    });

    // Add global error handler
    window.addEventListener('error', function(e) {
        console.error('JavaScript Error:', e.error);
    });

    // Modern Lazy Loading with Intersection Observer
    class ModernLazyLoader {
        constructor() {
            this.imageObserver = null;
            this.init();
        }

        init() {
            // Check if Intersection Observer is supported
            if ('IntersectionObserver' in window) {
                this.setupIntersectionObserver();
            } else {
                // Fallback for older browsers
                this.loadAllImages();
            }
        }

        setupIntersectionObserver() {
            const options = {
                root: null,
                rootMargin: '50px', // Start loading 50px before image comes into view
                threshold: 0.1
            };

            this.imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        this.imageObserver.unobserve(entry.target);
                    }
                });
            }, options);

            // Observe all lazy images
            document.querySelectorAll('.lazy-image').forEach(img => {
                this.imageObserver.observe(img);
            });
        }

        loadImage(img) {
            const src = img.getAttribute('data-src');
            if (src) {
                img.src = src;
                img.removeAttribute('data-src');
            }
        }

        loadAllImages() {
            // Fallback: load all images immediately
            document.querySelectorAll('.lazy-image').forEach(img => {
                this.loadImage(img);
            });
        }
    }

    // Initialize modern lazy loading when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        new ModernLazyLoader();
        // Initialize venue row handlers immediately and also after a short delay to ensure DOM is ready
        initVenueRowClickHandlers();
        setTimeout(initVenueRowClickHandlers, 100);
    });

    // Initialize venue row click handlers
    function initVenueRowClickHandlers() {
        // Attach click handlers directly to each venue row
        const rows = document.querySelectorAll('tr.venue-row');
        rows.forEach(function(row) {
            // Skip if already has handler
            if (row.hasAttribute('data-click-handler-attached')) {
                return;
            }
            
            row.addEventListener('click', function(e) {
                const target = e.target;
                
                // Don't navigate if clicking on:
                // - Checkbox input (specifically) 
                // - Links
                // - Buttons  
                // - Forms
                // - Elements inside the actions div (which has stopPropagation)
                if (target.type === 'checkbox' || 
                    target.closest('input[type="checkbox"]') ||
                    target.tagName === 'A' || 
                    target.closest('a') ||
                    target.tagName === 'BUTTON' || 
                    target.closest('button') ||
                    target.tagName === 'FORM' || 
                    target.closest('form') ||
                    target.closest('div[onclick*="stopPropagation"]')) {
                    return;
                }
                
                const url = row.getAttribute('data-venue-url');
                if (url) {
                    window.location.href = url;
                }
            });
            
            row.setAttribute('data-click-handler-attached', 'true');
        });
    }
</script>

<script>
    // Lightweight inline AJAX search (fallback if external asset isn't available)
    (function(){
        class InlineAjaxSearch {
            constructor() {
                this.form = document.getElementById('ajax-search-form');
                this.results = document.getElementById('ajax-results');
                this.debounceTimer = null;
                if (!this.form || !this.results) return;
                this.bind();
            }
            bind(){
                // Inputs
                this.form.querySelectorAll('input[type="text"],input[type="search"],input[type="date"]').forEach(inp=>{
                    inp.addEventListener('input', ()=>{
                        clearTimeout(this.debounceTimer);
                        this.debounceTimer = setTimeout(()=>this.search(), 400);
                    });
                });
                // Selects
                this.form.querySelectorAll('select').forEach(sel=>{
                    sel.addEventListener('change', ()=>this.search());
                });
                // Submit
                this.form.addEventListener('submit', (e)=>{ e.preventDefault(); this.search(); });
                // Clear filters triggers
                document.querySelectorAll('[data-clear-filters]').forEach(el=>{
                    el.addEventListener('click', (e)=>{ e.preventDefault(); this.clearFilters(); });
                });
            }
            urlWithParams(extra={}){
                const fd = new FormData(this.form);
                Object.entries(extra).forEach(([k,v])=>fd.set(k,v));
                const params = new URLSearchParams(fd);
                return `${window.location.pathname}?${params.toString()}`;
            }
            search(){
                const url = this.urlWithParams();
                this.loading(true);
                fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'text/html' }})
                    .then(r=>r.text())
                    .then(html=>{
                        const doc = new DOMParser().parseFromString(html,'text/html');
                        const repl = doc.getElementById('ajax-results');
                        if (repl) {
                            this.results.innerHTML = repl.innerHTML;
                            window.history.pushState({}, '', url);
                            this.attachPerPage();
                            // Re-initialize venue row click handlers after AJAX update
                            if (typeof initVenueRowClickHandlers === 'function') {
                                initVenueRowClickHandlers();
                            }
                        }
                    })
                    .finally(()=>this.loading(false));
            }
            attachPerPage(){
                const per = this.results.querySelector('#per-page');
                if (per) {
                    per.addEventListener('change', (e)=>{
                        const url = this.urlWithParams({ per_page: e.target.value, page: 1 });
                        this.load(url);
                    });
                }
                this.results.querySelectorAll('a[href*="page="]').forEach(a=>{
                    a.addEventListener('click', (e)=>{ e.preventDefault(); this.load(a.href); });
                });
            }
            load(url){
                this.loading(true);
                fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest','Accept':'text/html' }})
                    .then(r=>r.text())
                    .then(html=>{
                        const doc = new DOMParser().parseFromString(html,'text/html');
                        const repl = doc.getElementById('ajax-results');
                        if (repl) {
                            this.results.innerHTML = repl.innerHTML;
                            window.history.pushState({}, '', url);
                            this.attachPerPage();
                            // Re-initialize venue row click handlers after AJAX update
                            if (typeof initVenueRowClickHandlers === 'function') {
                                initVenueRowClickHandlers();
                            }
                        }
                    })
                    .finally(()=>this.loading(false));
            }
            clearFilters(){
                this.form.querySelectorAll('input[type="text"],input[type="search"],input[type="date"]').forEach(i=>i.value='');
                this.form.querySelectorAll('select').forEach(s=>{ s.selectedIndex = 0; s.dispatchEvent(new Event('change', { bubbles:true })); });
                this.search();
            }
            loading(on){
                this.results.style.opacity = on ? '0.5' : '1';
                this.results.style.pointerEvents = on ? 'none' : 'auto';
            }
        }
        document.addEventListener('DOMContentLoaded', function(){
            window.ajaxSearchInstance = new InlineAjaxSearch();
        });
    })();
</script>

<!-- Link to User Modal -->
<div id="link-modal" 
     x-data="linkModalData()"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center"
     @keydown.escape.window="closeModal()"
     @user-selected.window="onUserSelected($event.detail)"
     style="display: none;">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 overflow-hidden" @click.outside="closeModal()">
        <!-- Main Link Form -->
        <template x-if="!showConflict">
            <div>
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Link to User</h3>
                    <p class="text-sm text-gray-500 mt-1">Link <span x-text="itemName" class="font-medium text-gray-900"></span> to a registered user</p>
                </div>
                <form @submit.prevent="submitLink()" class="p-6">
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <x-user-selector 
                            name="user_id" 
                            placeholder="Search and select a user..."
                            :required="true"
                        />
                        <!-- Checking indicator -->
                        <div x-show="checkingConflict" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Checking for conflicts...
                        </div>
                    </div>
                    <div x-show="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="errorMessage"></div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="loading || checkingConflict" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50">
                            <span x-show="!loading">Link User</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </template>

        <!-- Conflict Confirmation -->
        <template x-if="showConflict">
            <div>
                <div class="px-6 py-4 border-b border-gray-200 bg-amber-50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-full">
                            <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">User Already Has Profile</h3>
                            <p class="text-sm text-amber-700">This action will replace an existing profile</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600 mb-3">
                            <span class="font-medium text-gray-900" x-text="conflictData.user_name"></span> already has an existing 
                            <span class="font-medium" x-text="conflictData.type"></span> profile:
                        </p>
                        <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-gray-200">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900" x-text="conflictData.existing_name"></p>
                                <p class="text-xs text-gray-500">ID: <span x-text="conflictData.existing_id"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-6">
                        <p class="text-sm text-amber-800">
                            <strong>Warning:</strong> Proceeding will unlink the existing profile and link <span class="font-medium" x-text="itemName"></span> instead. The old profile will become unclaimed.
                        </p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="cancelConflict()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="button" @click="forceLink()" :disabled="loading" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <span x-show="!loading">Replace & Link</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    // Link modal Alpine.js data
    function linkModalData() {
        return {
            show: false,
            showConflict: false,
            loading: false,
            checkingConflict: false,
            errorMessage: '',
            itemName: '',
            itemType: '',
            itemId: null,
            conflictData: {},
            selectedUserId: null,
            selectedUserName: '',
            
            openModal(type, id, name) {
                this.itemType = type;
                this.itemId = id;
                this.itemName = name;
                this.showConflict = false;
                this.errorMessage = '';
                this.conflictData = {};
                this.selectedUserId = null;
                this.selectedUserName = '';
                this.show = true;
            },
            
            closeModal() {
                this.show = false;
                this.showConflict = false;
                this.loading = false;
                this.checkingConflict = false;
                this.errorMessage = '';
            },
            
            // Called when user-selector dispatches user-selected event
            async onUserSelected(detail) {
                if (!this.show || !detail || !detail.user) return;
                
                const user = detail.user;
                this.selectedUserId = user.id;
                this.selectedUserName = user.name;
                
                // Venues don't have one-to-one relationships, so no conflict check needed
                // But we can still check if the user already owns this venue
                this.checkingConflict = false;
            },
            
            async submitLink() {
                const userId = document.querySelector('#link-modal input[name="user_id"]').value;
                if (!userId) {
                    this.errorMessage = 'Please select a user first';
                    return;
                }
                
                this.selectedUserId = userId;
                this.loading = true;
                this.errorMessage = '';
                
                try {
                    const response = await fetch(`/admin/unclaimed/${this.itemType}/${this.itemId}/link-user`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                    
                    const data = await response.json();
                    
                    if (response.status === 409 && data.conflict) {
                        // Show conflict confirmation
                        this.conflictData = data.data;
                        this.showConflict = true;
                    } else if (response.ok) {
                        // Success - reload page
                        window.location.reload();
                    } else {
                        this.errorMessage = data.message || 'An error occurred';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.errorMessage = 'Failed to process request';
                } finally {
                    this.loading = false;
                }
            },
            
            cancelConflict() {
                this.showConflict = false;
                this.conflictData = {};
            },
            
            async forceLink() {
                this.loading = true;
                
                try {
                    const response = await fetch(`/admin/unclaimed/${this.itemType}/${this.itemId}/link-user`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ 
                            user_id: this.selectedUserId,
                            force_replace: true
                        })
                    });
                    
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        const data = await response.json();
                        this.errorMessage = data.message || 'Failed to link user';
                        this.showConflict = false;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.errorMessage = 'Failed to process request';
                    this.showConflict = false;
                } finally {
                    this.loading = false;
                }
            }
        };
    }

    // Global function to open modal (called from table rows)
    function openLinkModal(type, id, name) {
        const modal = document.getElementById('link-modal');
        if (modal && modal.__x && modal.__x.$data) {
            modal.__x.$data.openModal(type, id, name);
        } else {
            // Wait a bit for Alpine to initialize
            setTimeout(() => {
                const modal = document.getElementById('link-modal');
                if (modal && modal.__x && modal.__x.$data) {
                    modal.__x.$data.openModal(type, id, name);
                } else {
                    console.error('Modal not initialized or Alpine not ready');
                }
            }, 100);
        }
    }
</script>
@endsection

