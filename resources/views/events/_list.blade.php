<!-- Search Results Info -->
@if(request()->hasAny(['search', 'category', 'date_from']))
<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-sm font-medium text-blue-900">Search Results</h3>
            <p class="text-sm text-blue-700">
                Found {{ $events->total() }} event{{ $events->total() !== 1 ? 's' : '' }}
                @if(request('search'))
                    for "{{ request('search') }}"
                @endif
                @if(request('category'))
                    in {{ ucfirst(request('category')) }} category
                @endif
                @if(request('date_from'))
                    from {{ \Carbon\Carbon::parse(request('date_from'))->format('M j, Y') }}
                @endif
            </p>
        </div>
    </div>
</div>
@endif

<!-- Events Display -->
@if($events->count() > 0)
    @if(request('view', 'cards') === 'table')
        <x-events-table :events="$events" />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $event)
                <x-event-card :event="$event" />
            @endforeach
        </div>
    @endif

    <!-- Pagination -->
    @if($events->hasPages())
        <div class="mt-8">
            <x-advanced-pagination :paginator="$events" />
        </div>
    @endif
@else
    <!-- Empty State -->
    <div class="text-center py-12">
        <svg class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            @if(request()->hasAny(['search', 'category', 'date_from']))
                No events found matching your criteria
            @else
                No events found
            @endif
        </h3>
        <p class="text-gray-500 mb-6">
            @if(request()->hasAny(['search', 'category', 'date_from']))
                Try adjusting your search criteria or create a new event.
            @else
                Try creating a new event.
            @endif
        </p>
        @auth
            <a href="{{ route('events.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                List an Event
            </a>
        @endauth
    </div>
@endif





