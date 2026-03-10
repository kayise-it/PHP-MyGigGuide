@extends('layouts.app')

@section('title', 'Artists - My Gig Guide')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-semibold mb-6">Artists</h1>

    <!-- Search and Filter Form -->
    <form method="GET" action="{{ route('artists.index') }}" id="ajax-search-form" class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Search Input -->
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search Artists</label>
                <input 
                    type="text" 
                    id="search" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Search by name..." 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>

            <!-- Genre Filter -->
            <div class="min-w-48">
                <label for="genre" class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                <x-genre-select 
                    id="genre" 
                    name="genre" 
                    :value="request('genre')" 
                    placeholder="All Genres"
                    use-names
                    class="w-full"
                />
            </div>

            <!-- Sort By -->
            <div class="min-w-40">
                <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                <select 
                    id="sort" 
                    name="sort" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="name" {{ request('sort', 'name') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                    <option value="events" {{ request('sort') == 'events' ? 'selected' : '' }}>Most Events</option>
                </select>
            </div>

            <!-- Search Button -->
            <div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                    Search Artists
                </button>
            </div>

            <!-- Clear Filters -->
            @if(request()->hasAny(['search', 'genre', 'sort']))
            <div>
                <a 
                    href="#" 
                    onclick="event.preventDefault(); if(window.ajaxSearchInstance) { window.ajaxSearchInstance.clearFilters(); } else { window.location.href = '{{ route('artists.index') }}'; }"
                    class="text-purple-600 hover:text-purple-800 px-4 py-2 flex items-center font-medium transition-colors duration-200"
                >
                    Clear Filters
                </a>
            </div>
            @endif
        </div>
    </form>

    <div id="ajax-results">
        @include('artists._results', ['artists' => $artists])
    </div>
</div>

@push('scripts')
<script>
// AjaxSearch class implementation
class AjaxSearch {
    constructor(config = {}) {
        this.form = document.getElementById('ajax-search-form');
        this.resultsContainer = document.getElementById('ajax-results');
        this.config = {
            debounceDelay: 300,
            ...config
        };
        
        if (this.form) {
            this.init();
        }
    }
    
    init() {
        // Handle form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });
        
        // Handle input changes with debouncing
        const inputs = this.form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    this.performSearch();
                }, this.config.debounceDelay);
            });
        });
    }
    
    async performSearch() {
        if (!this.form) return;
        
        const formData = new FormData(this.form);
        const params = new URLSearchParams();
        
        for (let [key, value] of formData.entries()) {
            if (value) {
                params.append(key, value);
            }
        }
        
        try {
            const response = await fetch(`${this.form.action}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            
            if (response.ok) {
                const html = await response.text();
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = html;
                    this.attachPagination();
                }
            } else {
                this.showError('Search failed. Please try again.');
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Search failed. Please try again.');
        }
    }

    attachPagination() {
        if (!this.resultsContainer) return;
        // Intercept pagination clicks to load via AJAX
        this.resultsContainer.querySelectorAll('a[href*="page="]').forEach(a => {
            a.addEventListener('click', (e) => {
                e.preventDefault();
                const url = a.getAttribute('href');
                this.load(url);
            });
        });
    }

    async load(url) {
        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
            });
            if (response.ok) {
                const html = await response.text();
                if (this.resultsContainer) {
                    this.resultsContainer.innerHTML = html;
                    this.attachPagination();
                }
                window.history.pushState({}, '', url);
            }
        } catch (e) {
            console.error('Pagination load failed', e);
        }
    }
    
    clearFilters() {
        // Reset all form inputs
        const inputs = this.form.querySelectorAll('input[type="text"], input[type="search"], input[type="date"]');
        inputs.forEach(input => input.value = '');
        
        const selects = this.form.querySelectorAll('select');
        selects.forEach(select => {
            select.selectedIndex = 0;
            // Trigger change event to ensure any custom components are updated
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        // Clear any hidden inputs that might store values
        const hiddenInputs = this.form.querySelectorAll('input[type="hidden"]');
        hiddenInputs.forEach(input => {
            if (input.name === 'genre' || input.name.includes('genre')) {
                input.value = '';
            }
        });
        
        // Perform search to show all results
        this.performSearch();
    }
    
    showError(message) {
        if (this.resultsContainer) {
            this.resultsContainer.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                    <p class="text-red-600">${message}</p>
                </div>
            `;
        }
    }
    
    static init(config = {}) {
        return new AjaxSearch(config);
    }
}

// Initialize when DOM is ready
let ajaxSearchInstance;
document.addEventListener('DOMContentLoaded', function() {
    ajaxSearchInstance = AjaxSearch.init();
    // Make it globally accessible
    window.ajaxSearchInstance = ajaxSearchInstance;
});
</script>
@endpush
@endsection
