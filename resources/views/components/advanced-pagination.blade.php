@props(['paginator', 'showPageSize' => true, 'showJumpTo' => true])

@if ($paginator->hasPages())
    <div class="bg-white border-t border-gray-200 px-4 py-3 sm:px-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            {{-- Results Info --}}
            <div class="mb-4 sm:mb-0">
                <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium">{{ $paginator->firstItem() }}</span>
                    to
                    <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    of
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    results
                </p>
            </div>

            {{-- Controls --}}
            <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                {{-- Page Size Selector --}}
                @if($showPageSize)
                    <div class="flex items-center space-x-2">
                        <label for="per-page" class="text-sm font-medium text-gray-700">Show:</label>
                        <select id="per-page" name="per_page" onchange="changePageSize(this.value)" class="rounded-md border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500">
                            <option value="12" {{ request('per_page', 20) == 12 ? 'selected' : '' }}>12</option>
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                            <option value="24" {{ request('per_page') == 24 ? 'selected' : '' }}>24</option>
                            <option value="48" {{ request('per_page') == 48 ? 'selected' : '' }}>48</option>
                            <option value="96" {{ request('per_page') == 96 ? 'selected' : '' }}>96</option>
                        </select>
                    </div>
                @endif

                {{-- Jump to Page --}}
                @if($showJumpTo && $paginator->lastPage() > 1)
                    <div class="flex items-center space-x-2">
                        <label for="jump-to" class="text-sm font-medium text-gray-700">Go to:</label>
                        <input 
                            type="number" 
                            id="jump-to" 
                            min="1" 
                            max="{{ $paginator->lastPage() }}" 
                            value="{{ $paginator->currentPage() }}"
                            onchange="jumpToPage(this.value)"
                            class="w-16 rounded-md border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500"
                        >
                        <span class="text-sm text-gray-500">of {{ $paginator->lastPage() }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Pagination Navigation --}}
        <div class="mt-4 flex items-center justify-between">
            {{-- Previous Button --}}
            <div class="flex-1 flex justify-start">
                @if ($paginator->onFirstPage())
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-white cursor-not-allowed">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:z-10 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors duration-200">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Previous
                    </a>
                @endif
            </div>

            {{-- Page Numbers --}}
            <div class="flex items-center space-x-1">
                {{-- First Page --}}
                @if ($paginator->currentPage() > 3)
                    <a href="{{ $paginator->url(1) }}" class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:z-10 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors duration-200">
                        1
                    </a>
                    @if ($paginator->currentPage() > 4)
                        <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500">
                            ...
                        </span>
                    @endif
                @endif

                {{-- Page Numbers --}}
                @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="relative z-10 inline-flex items-center px-3 py-2 border border-purple-500 text-sm font-medium rounded-md text-purple-600 bg-purple-50">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:z-10 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors duration-200">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach

                {{-- Last Page --}}
                @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                    @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                        <span class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500">
                            ...
                        </span>
                    @endif
                    <a href="{{ $paginator->url($paginator->lastPage()) }}" class="relative inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:z-10 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors duration-200">
                        {{ $paginator->lastPage() }}
                    </a>
                @endif
            </div>

            {{-- Next Button --}}
            <div class="flex-1 flex justify-end">
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:z-10 focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500 transition-colors duration-200">
                        Next
                        <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-white cursor-not-allowed">
                        Next
                        <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- JavaScript for page size and jump functionality --}}
    <script>
        function changePageSize(size) {
            const url = new URL(window.location);
            url.searchParams.set('per_page', size);
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }

        function jumpToPage(page) {
            if (page >= 1 && page <= {{ $paginator->lastPage() }}) {
                const url = new URL(window.location);
                url.searchParams.set('page', page);
                window.location.href = url.toString();
            }
        }
    </script>
@endif
