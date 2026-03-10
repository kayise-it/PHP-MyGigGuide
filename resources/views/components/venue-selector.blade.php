@props([
    'selectedVenueId' => null,
    'name' => 'venue_id',
    'placeholder' => 'Select a venue...',
    'userRole' => 'all',
    'organiserId' => null,
    'artistId' => null,
    'required' => false,
    'class' => '',
    'hasError' => false
])

<div class="relative venue-selector" x-data="venueSelector({
    selectedVenueId: @js($selectedVenueId),
    name: @js($name),
    placeholder: @js($placeholder),
    userRole: @js($userRole),
    organiserId: @js($organiserId),
    artistId: @js($artistId),
    required: @js($required),
    hasError: @js($hasError)
})">
    <!-- Selected Venue Display -->
    <div
        @click="toggleDropdown()"
        :class="{
            'border-purple-500 ring-2 ring-purple-200': isOpen,
            'border-gray-200 hover:border-gray-300': !isOpen && !hasError,
            'border-red-500 ring-2 ring-red-200': hasError
        }"
        class="relative w-full bg-white border-2 rounded-xl p-4 cursor-pointer transition-all duration-200 {{ $class }}"
    >
        <template x-if="selectedVenue">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-purple-100 p-2 rounded-lg">
                        <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900" x-text="selectedVenue.name"></h3>
                        <p class="text-sm text-gray-600" x-text="selectedVenue.location"></p>
                        <p class="text-xs text-gray-500" x-show="selectedVenue.capacity" x-text="'Capacity: ' + selectedVenue.capacity"></p>
                    </div>
                </div>
                <svg class="h-5 w-5 text-gray-400 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </template>
        
        <template x-if="!selectedVenue">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-gray-100 p-2 rounded-lg">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <span class="text-gray-500" x-text="placeholder"></span>
                </div>
                <svg class="h-5 w-5 text-gray-400 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </template>
    </div>

    <!-- Hidden Input -->
    <input type="hidden" :name="name" :value="selectedVenueId" x-model="selectedVenueId">

    <!-- Dropdown -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg z-50 max-h-96 overflow-hidden">
        
        <!-- Search and Filters -->
        <div class="p-4 border-b border-gray-100">
            <!-- Search Bar -->
            <div class="relative mb-3">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    type="text"
                    x-model="searchTerm"
                    @input.debounce.300ms="searchVenues()"
                    placeholder="Search venues by name or location..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                />
            </div>

            <!-- Filters Toggle -->
            <div class="flex items-center justify-between">
                <button
                    @click="showFilters = !showFilters"
                    class="flex items-center space-x-2 text-sm text-purple-600 hover:text-purple-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                    </svg>
                    <span>Filters</span>
                </button>
                
                <button
                    x-show="hasActiveFilters"
                    @click="clearFilters()"
                    class="flex items-center space-x-1 text-sm text-gray-500 hover:text-gray-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span>Clear</span>
                </button>
            </div>

            <!-- Filter Options -->
            <div x-show="showFilters" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 max-h-0"
                 x-transition:enter-end="opacity-100 max-h-96"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 max-h-96"
                 x-transition:leave-end="opacity-0 max-h-0"
                 class="mt-3 space-y-3 pt-3 border-t border-gray-100">
                
                <!-- Location Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Location</label>
                    <div class="grid grid-cols-2 gap-1">
                        <template x-for="location in popularLocations.slice(0, 6)" :key="location">
                            <button
                                @click="selectLocation(location)"
                                :class="{
                                    'bg-purple-100 text-purple-700': selectedLocation === location,
                                    'bg-gray-100 text-gray-600 hover:bg-gray-200': selectedLocation !== location
                                }"
                                class="px-2 py-1 text-xs rounded transition-colors"
                                x-text="location"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- Capacity Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Capacity Range</label>
                    <div class="flex space-x-2">
                        <input
                            type="number"
                            x-model="capacityRange.min"
                            @input.debounce.300ms="searchVenues()"
                            placeholder="Min"
                            class="flex-1 px-2 py-1 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-purple-500"
                        />
                        <input
                            type="number"
                            x-model="capacityRange.max"
                            @input.debounce.300ms="searchVenues()"
                            placeholder="Max"
                            class="flex-1 px-2 py-1 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-purple-500"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Venue List -->
        <div class="max-h-64 overflow-y-auto">
            <template x-if="loading">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                    <span class="ml-2 text-sm text-gray-600">Loading venues...</span>
                </div>
            </template>
            
            <template x-if="!loading && filteredVenues.length === 0">
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <p class="text-sm">No venues found</p>
                    <p class="text-xs text-gray-400 mt-1">Try adjusting your search criteria</p>
                </div>
            </template>
            
            <template x-if="!loading && filteredVenues.length > 0">
                <div class="divide-y divide-gray-100">
                    <template x-for="venue in filteredVenues" :key="venue.id">
                        <div
                            @click="selectVenue(venue)"
                            :class="{
                                'bg-purple-50': selectedVenueId === venue.id,
                                'border-l-4 border-l-green-400': venue.isOwnVenue
                            }"
                            class="p-3 cursor-pointer transition-colors hover:bg-gray-50"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="font-medium text-gray-900" x-text="venue.name"></h4>
                                        <span x-show="venue.isOwnVenue" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                            Your Venue
                                        </span>
                                        <svg x-show="selectedVenueId === venue.id" class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-600 flex items-center mt-1">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span x-text="venue.location"></span>
                                    </p>
                                    <p x-show="venue.capacity" class="text-xs text-gray-500 flex items-center mt-1">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        <span x-text="'Capacity: ' + venue.capacity"></span>
                                    </p>
                                    <p x-show="venue.owner && !venue.isOwnVenue" class="text-xs text-gray-500 flex items-center mt-1">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span x-text="'Owned by: ' + (venue.owner ? venue.owner.name : 'Unknown')"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Pagination -->
        <div x-show="pagination && pagination.totalPages > 1" class="border-t border-gray-100 p-3 flex items-center justify-between">
            <span class="text-xs text-gray-500" x-text="'Page ' + (pagination ? pagination.page : 1) + ' of ' + (pagination ? pagination.totalPages : 1) + ' (' + (pagination ? pagination.total : 0) + ' venues)'"></span>
            <div class="flex space-x-1">
                <button
                    @click="previousPage()"
                    :disabled="currentPage === 1"
                    class="px-2 py-1 text-xs border border-gray-200 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Previous
                </button>
                <button
                    @click="nextPage()"
                    :disabled="currentPage === (pagination ? pagination.totalPages : 1)"
                    class="px-2 py-1 text-xs border border-gray-200 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function venueSelector(config) {
    return {
        // Configuration
        selectedVenueId: config.selectedVenueId,
        name: config.name,
        placeholder: config.placeholder,
        userRole: config.userRole,
        organiserId: config.organiserId,
        artistId: config.artistId,
        required: config.required,
        hasError: config.hasError,
        
        // State
        isOpen: false,
        loading: false,
        searchTerm: '',
        selectedLocation: '',
        capacityRange: { min: '', max: '' },
        showFilters: false,
        currentPage: 1,
        venues: [],
        filteredVenues: [],
        selectedVenue: null,
        pagination: null,
        
        // Popular South African cities
        popularLocations: [
            'Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Sandton', 
            'Rosebank', 'Soweto', 'Centurion', 'Stellenbosch', 'Port Elizabeth'
        ],
        
        // Computed
        get hasActiveFilters() {
            return this.searchTerm || this.selectedLocation || this.capacityRange.min || this.capacityRange.max;
        },
        
        // Methods
        init() {
            this.fetchVenues();
            this.findSelectedVenue();
            // Listen for quick-add completion to auto-select the new venue
            window.addEventListener('venue-quick-added', (e) => {
                const v = e && e.detail ? e.detail.venue : null;
                if (!v) return;
                this.selectedVenueId = v.id;
                this.selectedVenue = {
                    id: v.id,
                    name: v.name,
                    location: v.address || v.location || '',
                    capacity: v.capacity || null
                };
                this.isOpen = false;
            });
        },
        
        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.fetchVenues();
            }
        },
        
        async fetchVenues() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    limit: 20,
                    user_role: this.userRole,
                    organiser_id: this.organiserId || '',
                    artist_id: this.artistId || ''
                });
                
                if (this.searchTerm) params.append('search', this.searchTerm);
                if (this.selectedLocation) params.append('location', this.selectedLocation);
                if (this.capacityRange.min) params.append('capacity_min', this.capacityRange.min);
                if (this.capacityRange.max) params.append('capacity_max', this.capacityRange.max);
                
                const response = await fetch(`/api/venues/search?${params}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                this.venues = data.venues || [];
                this.filteredVenues = data.venues || [];
                this.pagination = data.pagination;
            } catch (error) {
                console.error('Error fetching venues:', error);
                this.venues = [];
                this.filteredVenues = [];
            } finally {
                this.loading = false;
            }
        },
        
        async searchVenues() {
            this.currentPage = 1;
            await this.fetchVenues();
        },
        
        selectVenue(venue) {
            this.selectedVenue = venue;
            this.selectedVenueId = venue.id;
            this.isOpen = false;
            
            // Dispatch custom event for parent components
            this.$dispatch('venue-selected', { venue: venue });
        },
        
        selectLocation(location) {
            this.selectedLocation = location;
            this.showFilters = false;
            this.searchVenues();
        },
        
        clearFilters() {
            this.searchTerm = '';
            this.selectedLocation = '';
            this.capacityRange = { min: '', max: '' };
            this.currentPage = 1;
            this.searchVenues();
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.fetchVenues();
            }
        },
        
        nextPage() {
            if (this.pagination && this.currentPage < this.pagination.totalPages) {
                this.currentPage++;
                this.fetchVenues();
            }
        },
        
        findSelectedVenue() {
            if (this.selectedVenueId && this.venues.length > 0) {
                this.selectedVenue = this.venues.find(v => v.id == this.selectedVenueId);
            }
        }
    }
}
</script>
