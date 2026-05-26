@extends('layouts.app')

@section('title', 'Events - My Gig Guide')
@section('description', 'Discover amazing events happening around you.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="{{ $siteBrand->pageTitleClass() }}">Events</h1>
                    <p class="{{ $siteBrand->pageSubtitleClass() }}">Discover amazing events happening around you</p>
                </div>
                <div class="flex items-center space-x-4">
                    <x-view-switcher :current-view="request('view', 'cards')" />
                    @auth
                        <a href="{{ route('events.create') }}" class="btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            List an Event
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="{{ $siteBrand->filterPanelClass() }}">
            <form method="GET" action="{{ route('events.index') }}" id="ajax-search-form">
                @if(request('view'))
                    <input type="hidden" name="view" value="{{ request('view') }}">
                @endif
                <div class="flex flex-wrap gap-4 items-end">
                    <!-- Search Input -->
                    <div class="flex-1 min-w-64">
                        <label for="search" class="{{ $siteBrand->formLabelClass() }}">Search Events</label>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search events..."
                            class="{{ $siteBrand->formInputClass() }}"
                        />
                    </div>
                    
                    <!-- Category Filter -->
                    <div class="min-w-48">
                        <label for="category" class="{{ $siteBrand->formLabelClass() }}">Category</label>
                        <x-category-select 
                            name="category" 
                            id="category" 
                            value="{{ request('category') }}"
                            placeholder="All Categories"
                            :multiple="false"
                            :useIds="false"
                        />
                    </div>
                    
                    <!-- Date Filter -->
                    <div class="min-w-40">
                        <label for="date_from" class="{{ $siteBrand->formLabelClass() }}">From Date</label>
                        <input
                            type="date"
                            id="date_from"
                            name="date_from"
                            value="{{ request('date_from') }}"
                            class="{{ $siteBrand->formInputClass() }}"
                        />
                    </div>
                    
                    <!-- Search Button -->
                    <div>
                        <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors duration-200 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                            </svg>
                            Search Events
                        </button>
                    </div>
                    
                    <!-- Clear Filters -->
                    @if(request()->hasAny(['search', 'category', 'date_from']))
                    <div>
                        <a 
                            href="#" 
                            onclick="event.preventDefault(); if(window.ajaxSearchInstance) { window.ajaxSearchInstance.clearFilters(); } else { window.location.href = '{{ route('events.index') }}'; }"
                            class="{{ $siteBrand->accentLinkClass() }}"
                        >
                            Clear Filters
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>

        <!-- Events Display -->
        <div id="ajax-results">
            @include('events._list', ['events' => $events])
        </div>
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
                }
            } else {
                this.showError('Search failed. Please try again.');
            }
        } catch (error) {
            console.error('Search error:', error);
            this.showError('Search failed. Please try again.');
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