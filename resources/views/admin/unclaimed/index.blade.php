@extends('layouts.admin')

@section('title', 'Unclaimed Items - Admin Panel')
@section('page-title', 'Unclaimed Items')
@section('description', 'Manage unclaimed artists, venues, events, and organisers')

@section('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- All Card -->
        <a href="{{ route('admin.unclaimed.index') }}" 
           class="block p-4 rounded-xl border-2 transition-all duration-200 {{ $type === 'all' ? 'bg-gray-900 border-gray-900 text-white' : 'bg-white border-gray-200 hover:border-gray-400' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold">{{ $counts['all'] }}</p>
                    <p class="text-sm {{ $type === 'all' ? 'text-gray-300' : 'text-gray-500' }}">All</p>
                </div>
                <div class="p-2 rounded-lg {{ $type === 'all' ? 'bg-white/10' : 'bg-gray-100' }}">
                    <svg class="w-6 h-6 {{ $type === 'all' ? 'text-white' : 'text-gray-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </div>
            </div>
        </a>

        <!-- Artists Card -->
        <a href="{{ route('admin.unclaimed.index', ['type' => 'artist']) }}" 
           class="block p-4 rounded-xl border-2 transition-all duration-200 {{ $type === 'artist' ? 'bg-purple-600 border-purple-600 text-white' : 'bg-white border-gray-200 hover:border-purple-400' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold">{{ $counts['artist'] }}</p>
                    <p class="text-sm {{ $type === 'artist' ? 'text-purple-100' : 'text-gray-500' }}">Artists</p>
                </div>
                <div class="p-2 rounded-lg {{ $type === 'artist' ? 'bg-white/10' : 'bg-purple-100' }}">
                    <svg class="w-6 h-6 {{ $type === 'artist' ? 'text-white' : 'text-purple-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </div>
            </div>
        </a>

        <!-- Venues Card -->
        <a href="{{ route('admin.unclaimed.index', ['type' => 'venue']) }}" 
           class="block p-4 rounded-xl border-2 transition-all duration-200 {{ $type === 'venue' ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-gray-200 hover:border-blue-400' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold">{{ $counts['venue'] }}</p>
                    <p class="text-sm {{ $type === 'venue' ? 'text-blue-100' : 'text-gray-500' }}">Venues</p>
                </div>
                <div class="p-2 rounded-lg {{ $type === 'venue' ? 'bg-white/10' : 'bg-blue-100' }}">
                    <svg class="w-6 h-6 {{ $type === 'venue' ? 'text-white' : 'text-blue-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
        </a>

        <!-- Events Card -->
        <a href="{{ route('admin.unclaimed.index', ['type' => 'event']) }}" 
           class="block p-4 rounded-xl border-2 transition-all duration-200 {{ $type === 'event' ? 'bg-green-600 border-green-600 text-white' : 'bg-white border-gray-200 hover:border-green-400' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold">{{ $counts['event'] }}</p>
                    <p class="text-sm {{ $type === 'event' ? 'text-green-100' : 'text-gray-500' }}">Events</p>
                </div>
                <div class="p-2 rounded-lg {{ $type === 'event' ? 'bg-white/10' : 'bg-green-100' }}">
                    <svg class="w-6 h-6 {{ $type === 'event' ? 'text-white' : 'text-green-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </a>

        <!-- Organisers Card -->
        <a href="{{ route('admin.unclaimed.index', ['type' => 'organiser']) }}" 
           class="block p-4 rounded-xl border-2 transition-all duration-200 {{ $type === 'organiser' ? 'bg-orange-500 border-orange-500 text-white' : 'bg-white border-gray-200 hover:border-orange-400' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-2xl font-bold">{{ $counts['organiser'] }}</p>
                    <p class="text-sm {{ $type === 'organiser' ? 'text-orange-100' : 'text-gray-500' }}">Organisers</p>
                </div>
                <div class="p-2 rounded-lg {{ $type === 'organiser' ? 'bg-white/10' : 'bg-orange-100' }}">
                    <svg class="w-6 h-6 {{ $type === 'organiser' ? 'text-white' : 'text-orange-600' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </a>
    </div>

    <!-- Search, Filters, and Bulk Actions Bar -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <!-- Search and Filters Row -->
        <div class="p-4 border-b border-gray-200">
            <form method="GET" id="ajax-search-form" class="space-y-4">
                <input type="hidden" name="type" value="{{ $type }}">
                
                <!-- Search Row -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search Input (searches both name and email) -->
                    <div class="relative flex-1 min-w-[200px]">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search by name or email..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search
                    </button>
                    
                    @if(request('search'))
                        <a href="{{ route('admin.unclaimed.index', ['type' => $type]) }}" 
                           class="btn-secondary">
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>
        </div>
        
        <!-- Bulk Actions Row (hidden by default, shown when items are selected) -->
        <div id="bulk-actions-bar" class="hidden p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span id="selected-count" class="text-sm font-medium text-gray-700">0 selected</span>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        type="button"
                        id="bulk-send-email-btn"
                        onclick="sendBulkClaimEmails()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Send Claim Emails
                    </button>
                    <button 
                        type="button"
                        onclick="clearSelection()"
                        class="btn-secondary text-sm"
                    >
                        Clear Selection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    @include('admin.unclaimed._table')
</div>

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/ajax-search.js') }}"></script>
<script>
    let ajaxSearchInstance;
    
    // Checkbox functionality - define globally so it works after AJAX updates
    function initCheckboxFunctionality() {
        const selectAllCheckbox = document.getElementById('select-all');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');

        if (selectAllCheckbox) {
            // Remove existing listeners by cloning
            const newSelectAll = selectAllCheckbox.cloneNode(true);
            selectAllCheckbox.parentNode.replaceChild(newSelectAll, selectAllCheckbox);
            
            newSelectAll.addEventListener('change', function() {
                document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = this.checked);
                updateBulkActionsBar();
            });
        }

        itemCheckboxes.forEach(checkbox => {
            // Remove existing listeners by cloning
            const newCheckbox = checkbox.cloneNode(true);
            checkbox.parentNode.replaceChild(newCheckbox, checkbox);
            
            newCheckbox.addEventListener('change', function() {
                updateSelectAllState();
                updateBulkActionsBar();
            });
        });
        
        updateBulkActionsBar();
    }

    function updateSelectAllState() {
        const selectAllCheckbox = document.getElementById('select-all');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const allChecked = itemCheckboxes.length > 0 && Array.from(itemCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
    }

    function updateBulkActionsBar() {
        const checked = document.querySelectorAll('.item-checkbox:checked');
        const count = checked.length;
        const bulkActionsBar = document.getElementById('bulk-actions-bar');
        const selectedCountEl = document.getElementById('selected-count');
        
        if (selectedCountEl) {
            selectedCountEl.textContent = count + ' selected';
        }
        
        if (bulkActionsBar) {
            if (count > 0) {
                bulkActionsBar.classList.remove('hidden');
            } else {
                bulkActionsBar.classList.add('hidden');
            }
        }

        const bulkEmailBtn = document.getElementById('bulk-send-email-btn');
        if (bulkEmailBtn) {
            bulkEmailBtn.disabled = count === 0;
        }
    }

    function clearSelection() {
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const selectAllCheckbox = document.getElementById('select-all');
        
        itemCheckboxes.forEach(cb => cb.checked = false);
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
        updateBulkActionsBar();
    }

    async function sendBulkClaimEmails() {
        const checked = document.querySelectorAll('.item-checkbox:checked');
        
        if (checked.length === 0) {
            alert('Please select at least one item to send emails.');
            return;
        }

        const items = Array.from(checked).map(cb => cb.value);
        const count = items.length;

        if (!confirm(`Send claim invitation emails to ${count} selected item${count > 1 ? 's' : ''}?`)) {
            return;
        }

        const bulkEmailBtn = document.getElementById('bulk-send-email-btn');
        const originalText = bulkEmailBtn.innerHTML;
        bulkEmailBtn.disabled = true;
        bulkEmailBtn.innerHTML = `
            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Sending...
        `;

        try {
            const response = await fetch('{{ route("admin.unclaimed.bulk-send-email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ items: items })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                alert(`Success! ${data.sent} email${data.sent > 1 ? 's' : ''} sent successfully.${data.failed > 0 ? ` ${data.failed} failed.` : ''}`);
                clearSelection();
                // Reload the page to show updated status
                window.location.reload();
            } else {
                throw new Error(data.message || 'Failed to send emails');
            }
        } catch (error) {
            console.error('Error sending bulk emails:', error);
            alert('Error: ' + (error.message || 'Failed to send emails. Please try again.'));
        } finally {
            bulkEmailBtn.disabled = false;
            bulkEmailBtn.innerHTML = originalText;
        }
    }

    // Make functions globally available
    window.initCheckboxFunctionality = initCheckboxFunctionality;
    window.clearSelection = clearSelection;
    window.sendBulkClaimEmails = sendBulkClaimEmails;

    document.addEventListener('DOMContentLoaded', function() {
        ajaxSearchInstance = AjaxSearch.init();
        
        // Initialize checkbox functionality on page load
        initCheckboxFunctionality();
        
        // Reinitialize after AJAX table updates
        window.addEventListener('ajax-results-updated', function() {
            initCheckboxFunctionality();
        });
        
        // Ensure openLinkModal is available globally
        if (typeof openLinkModal === 'undefined') {
            console.warn('openLinkModal function not found, waiting for script to load...');
        }
    });
</script>
@endpush
@endsection


