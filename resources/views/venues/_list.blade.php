@if(request()->hasAny(['search', 'capacity_filter', 'sort']))
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-blue-900">Search Results</h3>
                <p class="text-sm text-blue-700">
                    Found {{ $venues->total() }} venue{{ $venues->total() !== 1 ? 's' : '' }}
                    @if(request('search'))
                        for "{{ request('search') }}"
                    @endif
                    @if(request('capacity_filter'))
                        with {{ ucfirst(request('capacity_filter')) }} capacity
                    @endif
                    @if(request('sort') && request('sort') != 'newest')
                        sorted by {{ ucfirst(request('sort')) }}
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Venues Grid -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if($venues->count() > 0)
        @php
            $ownedIds = $ownedVenueIds ?? [];
        @endphp

        @if(!empty($ownedIds))
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Your Venues</h2>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($venues as $venue)
                <x-venue-card :venue="$venue" :isOwned="in_array($venue->id, $ownedIds)" />
            @endforeach
        </div>

        <!-- Pagination -->
        @if($venues->hasPages())
        <div class="mt-8">
            <x-advanced-pagination :paginator="$venues" />
        </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
                @if(request()->hasAny(['search', 'capacity_filter', 'sort']))
                    No venues found matching your criteria
                @else
                    No venues found
                @endif
            </h3>
            <p class="text-gray-500 mb-6">
                @if(request()->hasAny(['search', 'capacity_filter', 'sort']))
                    Try adjusting your search criteria or add a new venue.
                @else
                    Be the first to add a venue to our platform!
                @endif
            </p>
            @auth
            <a href="{{ route('venues.create') }}" class="btn-primary">
                <div class="btn-content">
                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Venue
                </div>
            </a>
            @endauth
        </div>
    @endif
</div>





