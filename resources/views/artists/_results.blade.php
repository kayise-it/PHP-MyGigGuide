@if(request()->hasAny(['search', 'genre', 'sort']))
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-medium text-blue-800">Search Results</h3>
                <p class="text-sm text-blue-600">
                    Found {{ $artists->total() }} artist{{ $artists->total() !== 1 ? 's' : '' }}
                    @if(request('search'))
                        matching "{{ request('search') }}"
                    @endif
                    @if(request('genre'))
                        in {{ ucfirst(request('genre')) }} genre
                    @endif
                </p>
            </div>
        </div>
    </div>
@endif

@if($artists->count() === 0)
    <div class="text-center py-12">
        <div class="text-gray-400 mb-4">
            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            @if(request()->hasAny(['search', 'genre', 'sort']))
                No artists found matching your criteria
            @else
                No artists yet
            @endif
        </h3>
        <p class="text-gray-600">
            @if(request()->hasAny(['search', 'genre', 'sort']))
                Try adjusting your search terms or filters.
            @else
                Be the first to add an artist profile!
            @endif
        </p>
    </div>
@else
    <div id="artists-results">
        @include('artists._list', ['artists' => $artists])
    </div>
@endif


