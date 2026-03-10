@extends('layouts.app')

@section('title', 'Event Organisers - My Gig Guide')
@section('description', 'Discover professional event organisers and their upcoming events.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Event Organisers</h1>
            <p class="text-gray-600">Discover professional event organisers and their amazing events</p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 mb-8">
            <form method="GET" action="{{ route('organisers.index') }}" id="ajax-search-form" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Organisers</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="Search by name or organization..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" id="location" value="{{ request('location') }}" 
                               placeholder="City or area..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    <div class="flex items-end">
                        @if(request()->hasAny(['search', 'location']))
                        <a href="#" onclick="event.preventDefault(); ajaxSearchInstance.clearFilters();" class="w-full text-center text-purple-600 hover:text-purple-800 px-6 py-2 font-medium transition-colors duration-200">
                            Clear Filters
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Organisers Grid -->
        <div id="ajax-results">
        @if($organisers->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($organisers as $organiser)
                    <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                @if($organiser->user && $organiser->user->profile_photo)
                                    <img src="{{ Storage::url($organiser->user->profile_photo) }}" 
                                         alt="{{ $organiser->organisation_name }}" 
                                         class="w-16 h-16 rounded-full object-cover">
                                @else
                                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                                        <span class="text-2xl font-bold text-purple-600">
                                            {{ substr($organiser->organisation_name, 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">
                                    {{ $organiser->organisation_name }}
                                </h3>
                                @if($organiser->user)
                                    <p class="text-sm text-gray-600">{{ $organiser->user->name }}</p>
                                @endif
                                @if($organiser->description)
                                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">
                                        {{ Str::limit($organiser->description, 100) }}
                                    </p>
                                @endif
                                <div class="flex items-center mt-3 space-x-4">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $organiser->events_count ?? 0 }} events
                                    </div>
                                    @if($organiser->average_rating)
                                        <div class="flex items-center text-sm text-yellow-600">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.888c-.783.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            {{ number_format($organiser->average_rating, 1) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('organisers.show', $organiser->id) }}" 
                               class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                View Profile →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $organisers->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No organisers found</h3>
                <p class="text-gray-500">Try adjusting your search criteria</p>
            </div>
        @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/ajax-search.js') }}"></script>
<script>
    let ajaxSearchInstance;
    document.addEventListener('DOMContentLoaded', function() {
        ajaxSearchInstance = AjaxSearch.init();
    });
</script>
@endpush
@endsection

