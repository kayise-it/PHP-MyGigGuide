@extends('layouts.admin')

@section('title', 'Artists Management - Admin Panel')
@section('description', 'Manage all artists in the system.')

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

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Import Errors:</strong>
            <ul class="list-disc list-inside mt-2">
                @foreach(array_slice(session('import_errors'), 0, 10) as $error)
                    <li>{{ $error }}</li>
                @endforeach
                @if(count(session('import_errors')) > 10)
                    <li>... and {{ count(session('import_errors')) - 10 }} more errors</li>
                @endif
            </ul>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Artists Management</h1>
            <p class="text-gray-600">Manage all artists in the system</p>
        </div>
        <div class="flex space-x-3">
            <!-- Export Button -->
            <a href="{{ route('admin.artists.export', request()->query()) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Export CSV</span>
            </a>

            <!-- Import Button -->
            <button type="button"
                    onclick="window.AdminModal && AdminModal.openFromElement('#artist-import-modal-content')"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <span>Import CSV</span>
            </button>

            <!-- Create Artist Button -->
            <a href="{{ route('admin.artists.create') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors duration-200 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Create Artist</span>
            </a>
        </div>
    </div>

    <!-- Import Modal body content (rendered inside global admin modal) -->
    <div id="artist-import-modal-content" class="hidden">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Import Artists from CSV</h3>
        <form action="{{ route('admin.artists.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4 space-y-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Upload a CSV file with artist data. The file should match the export format.
                </p>
                <a href="{{ route('admin.artists.import-template') }}" class="inline-flex items-center text-xs text-purple-600 hover:text-purple-800 hover:underline">
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

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('admin.artists.index') }}" id="ajax-search-form" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-64">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search artists..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            </div>
            <div>
                <x-genre-select 
                    name="genre" 
                    id="genre-filter"
                    :value="request('genre')" 
                    placeholder="All Genres"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    use-names
                />
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                    Search
                </button>
                @if(request()->hasAny(['search', 'genre']))
                <a href="#" onclick="event.preventDefault(); if(window.ajaxSearchInstance) { window.ajaxSearchInstance.clearFilters(); } else { window.location.href = '{{ route('admin.artists.index') }}'; }" class="text-purple-600 hover:text-purple-800 px-4 py-2 flex items-center font-medium transition-colors duration-200">
                    Clear Filters
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Artists Table -->
    <div id="ajax-results">
        @include('admin.artists._results', ['artists' => $artists])
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
        
        const url = this.form.action || window.location.pathname;
        const fullUrl = `${url}?${params.toString()}`;
        
        try {
            const response = await fetch(fullUrl, {
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
                // Update browser URL without reload
                window.history.pushState({}, '', fullUrl);
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

