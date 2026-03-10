@extends('layouts.app')

@section('title', 'Create Event - My Gig Guide')
@section('description', 'Create a new event and manage all the details.')

@push('styles')
<style>
    /* Ensure Google Places Autocomplete dropdown appears above other elements */
    .pac-container {
        z-index: 9999 !important;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        margin-top: 4px;
    }
    
    .pac-item {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .pac-item:hover {
        background-color: #f3f4f6;
    }
    
    .pac-item-selected {
        background-color: #ede9fe !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Create New Event</h1>
                    <p class="text-gray-600 mt-2">Set up your event details and manage all aspects</p>
                </div>
                <a href="{{ route('events.index') }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Events
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('events.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Basic Information -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Event Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Event Name *
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-300 @enderror"
                            placeholder="Enter event name"
                        />
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('description') border-red-300 @enderror"
                            placeholder="Describe your event..."
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                            Event Date *
                        </label>
                        <input
                            type="date"
                            id="date"
                            name="date"
                            value="{{ old('date') }}"
                            required
                            min="{{ date('Y-m-d') }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('date') border-red-300 @enderror"
                        />
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="time" class="block text-sm font-medium text-gray-700 mb-2">
                            Event Time *
                        </label>
                        <input
                            type="time"
                            id="time"
                            name="time"
                            value="{{ old('time') }}"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('time') border-red-300 @enderror"
                        />
                        @error('time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                            Price (R)
                        </label>
                        <input
                            type="number"
                            id="price"
                            name="price"
                            value="{{ old('price') }}"
                            min="0"
                            step="0.01"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('price') border-red-300 @enderror"
                            placeholder="0.00"
                        />
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                            Capacity
                        </label>
                        <input
                            type="number"
                            id="capacity"
                            name="capacity"
                            value="{{ old('capacity') }}"
                            min="1"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('capacity') border-red-300 @enderror"
                            placeholder="Maximum attendees"
                        />
                        @error('capacity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ticket_url" class="block text-sm font-medium text-gray-700 mb-2">
                            Ticket URL
                        </label>
                        <input
                            type="url"
                            id="ticket_url"
                            name="ticket_url"
                            value="{{ old('ticket_url') }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('ticket_url') border-red-300 @enderror"
                            placeholder="https://example.com/tickets"
                        />
                        @error('ticket_url')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="categories" class="block text-sm font-medium text-gray-700 mb-2">
                            Categories
                        </label>
                        <x-category-combobox 
                            name="categories" 
                            :values="old('categories', [])"
                            placeholder="Select event categories (e.g., Concert, Festival)..."
                            class="@error('categories') border-red-300 @enderror"
                        />
                        @error('categories')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Choose one or more categories that best describe your event</p>
                    </div>
                </div>
            </div>

            <!-- Venue Selection -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Venue</h2>
                
                <div>
                    <label for="venue_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Venue *
                    </label>
                    <x-venue-selector 
                        name="venue_id" 
                        :selectedVenueId="old('venue_id')"
                        placeholder="Choose a venue for your event..."
                        userRole="organiser"
                        :organiserId="auth()->user()->id"
                        required
                        :hasError="$errors->has('venue_id')"
                    />
                    @error('venue_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <div id="quick-venue-panel" class="mt-4 hidden">
                        <div class="rounded-xl border border-purple-200 bg-purple-50/40 p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-purple-900">Quick add venue</p>
                                    <p class="text-xs text-purple-700 mt-1">Create a basic venue now. You can complete the profile later.</p>
                                </div>
                                <button type="button" id="close-quick-venue" class="text-purple-600 hover:text-purple-800 text-2xl leading-none">×</button>
                            </div>
                            <div class="mt-3 space-y-3">
                                <div>
                                    <input type="text" id="quick-venue-name" placeholder="Venue name*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <input type="text" id="quick-venue-address" placeholder="Search for address or place*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" autocomplete="off" />
                                    <p class="mt-1 text-xs text-gray-500">💡 Start typing to search for places using Google</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <input type="number" id="quick-venue-capacity" placeholder="Capacity (optional)" min="1" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                                    <input type="tel" id="quick-venue-phone" placeholder="Phone (optional)" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-3">
                                <button type="button" id="quick-venue-save" class="btn-primary !py-2">Save Venue</button>
                            </div>
                            <p id="quick-venue-status" class="mt-2 text-sm text-gray-500"></p>
                        </div>
                    </div>
                    
                    <p class="mt-2 text-sm text-gray-500">
                        Don't see your venue? <a href="#" id="open-quick-venue" class="text-purple-600 hover:text-purple-700">Add a new venue</a>
                    </p>
                </div>
            </div>

            <!-- Artists Selection -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Artists</h2>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Seleddct Artists
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            id="artist-search"
                            placeholder="Search artists by name or genre..."
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            autocomplete="off"
                        />
                        <div
                            id="artist-results"
                            class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto hidden"
                        ></div>
                    </div>
                    <div id="selected-artists" class="mt-3 flex flex-wrap gap-2"></div>
                    <div id="artist-hidden-inputs"></div>
                    <div id="quick-artist-panel" class="mt-4 hidden">
                        <div class="rounded-xl border border-purple-200 bg-purple-50/40 p-4">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-purple-900">Quick add unclaimed artist</p>
                                    <p class="text-xs text-purple-700 mt-1">Create a basic artist now. You can complete the profile later.</p>
                                </div>
                                <button type="button" id="close-quick-artist" class="text-purple-600 hover:text-purple-800">×</button>
                            </div>
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                                <input type="text" id="quick-artist-name" placeholder="Stage name*" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent" />
                                <x-genre-select 
                                    id="quick-artist-genre" 
                                    name="genre" 
                                    placeholder="Genre (optional)" 
                                />
                                <div class="flex items-center gap-2">
                                    <button type="button" id="quick-artist-save" class="btn-primary !py-2">Save</button>
                                </div>
                            </div>
                            <p id="quick-artist-status" class="mt-2 text-sm text-gray-500"></p>
                        </div>
                    </div>
                    @error('artists')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">
                        Don't see an artist? <a href="{{ route('artists.create') }}" id="open-quick-artist" class="text-purple-600 hover:text-purple-700">Add a new artist</a>
                    </p>
                </div>
            </div>

            <!-- YouTube Videos -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">YouTube Videos</h2>
                
                <div id="youtube-videos-container" class="space-y-4">
                    <div class="youtube-video-input flex gap-2">
                        <input
                            type="url"
                            name="youtube_videos[]"
                            placeholder="https://www.youtube.com/watch?v=..."
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('youtube_videos.*') border-red-300 @enderror"
                        />
                        <button type="button" onclick="removeYoutubeVideoInput(this)" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 hidden remove-video-btn">
                            Remove
                        </button>
                    </div>
                </div>
                
                <button type="button" onclick="addYoutubeVideoInput()" class="mt-4 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    + Add Another Video
                </button>
                
                @error('youtube_videos.*')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-sm text-gray-500">Add YouTube video URLs to showcase your event</p>
            </div>

            <!-- Event Images -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Event Images</h2>
                
                <div class="space-y-6">
                    <!-- Poster Upload -->
                    <div>
                        <label for="poster" class="block text-sm font-medium text-gray-700 mb-2">
                            Event Poster
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="poster" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                        <span>Upload a poster</span>
                                        <input id="poster" name="poster" type="file" class="sr-only" accept="image/*" onchange="previewImage(this, 'poster-preview')">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                            </div>
                        </div>
                        <div id="poster-preview" class="mt-4 hidden">
                            <img class="h-32 w-auto mx-auto rounded-lg" alt="Poster preview">
                        </div>
                        @error('poster')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gallery Upload -->
                    <div>
                        <label for="gallery" class="block text-sm font-medium text-gray-700 mb-2">
                            Event Gallery
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="gallery" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                        <span>Upload gallery images</span>
                                        <input id="gallery" name="gallery[]" type="file" class="sr-only" accept="image/*" multiple onchange="previewGallery(this, 'gallery-preview')">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB each</p>
                            </div>
                        </div>
                        <div id="gallery-preview" class="mt-4 hidden grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Gallery previews will be inserted here -->
                        </div>
                        @error('gallery')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('events.index') }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create Event
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
}

function previewGallery(input, previewId) {
    const preview = document.getElementById(previewId);
    const files = input.files;
    
    preview.innerHTML = '';
    
    if (files.length > 0) {
        preview.classList.remove('hidden');
        
        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Gallery preview ${index + 1}" class="h-24 w-full object-cover rounded-lg">
                    <button type="button" onclick="removeGalleryImage(this)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full h-6 w-6 flex items-center justify-center text-xs hover:bg-red-600">
                        ×
                    </button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    } else {
        preview.classList.add('hidden');
    }
}

function removeGalleryImage(button) {
    button.parentElement.remove();
}

// Artist search + tag selector
document.addEventListener('DOMContentLoaded', () => {
    const allArtists = @json($artists->map->only(['id','stage_name','genre']));

    const searchInput = document.getElementById('artist-search');
    const resultsBox = document.getElementById('artist-results');
    const selectedContainer = document.getElementById('selected-artists');
    const hiddenInputsContainer = document.getElementById('artist-hidden-inputs');

    const selectedIds = new Set(@json(old('artists', [])));

    function renderSelectedTags() {
        selectedContainer.innerHTML = '';
        hiddenInputsContainer.innerHTML = '';
        selectedIds.forEach((id) => {
            const artist = allArtists.find(a => String(a.id) === String(id));
            if (!artist) return;
            const tag = document.createElement('span');
            tag.className = 'inline-flex items-center gap-1 bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm';
            tag.innerHTML = `${artist.stage_name}<button type="button" data-id="${artist.id}" class="ml-1 text-purple-600 hover:text-purple-800">×</button>`;
            selectedContainer.appendChild(tag);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'artists[]';
            input.value = artist.id;
            hiddenInputsContainer.appendChild(input);
        });
    }

    function renderResults(items) {
        if (!items.length) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            return;
        }
        resultsBox.innerHTML = '';
        items.slice(0, 30).forEach((artist) => {
            const isSelected = selectedIds.has(String(artist.id)) || selectedIds.has(artist.id);
            if (isSelected) return; // don't show already selected
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'w-full text-left px-3 py-2 hover:bg-gray-50 flex justify-between items-center';
            row.innerHTML = `<span class="font-medium text-gray-900">${artist.stage_name}</span><span class="text-sm text-gray-500 ml-3">${artist.genre || ''}</span>`;
            row.addEventListener('click', () => {
                selectedIds.add(artist.id);
                renderSelectedTags();
                resultsBox.classList.add('hidden');
                searchInput.value = '';
            });
            resultsBox.appendChild(row);
        });
        resultsBox.classList.remove('hidden');
    }

    function filter(query) {
        const q = query.trim().toLowerCase();
        if (!q) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            return;
        }
        const matches = allArtists.filter(a =>
            (a.stage_name && a.stage_name.toLowerCase().includes(q)) ||
            (a.genre && a.genre.toLowerCase().includes(q))
        );
        renderResults(matches);
    }

    searchInput?.addEventListener('input', (e) => {
        filter(e.target.value);
    });

    document.addEventListener('click', (e) => {
        if (!resultsBox.contains(e.target) && e.target !== searchInput) {
            resultsBox.classList.add('hidden');
        }
    });

    selectedContainer.addEventListener('click', (e) => {
        const btn = e.target.closest('button[data-id]');
        if (!btn) return;
        selectedIds.delete(String(btn.dataset.id));
        renderSelectedTags();
    });

    renderSelectedTags();
});
</script>

<script>
// Quick add unclaimed artist
document.addEventListener('DOMContentLoaded', () => {
    const openLink = document.getElementById('open-quick-artist');
    const panel = document.getElementById('quick-artist-panel');
    const closeBtn = document.getElementById('close-quick-artist');
    const saveBtn = document.getElementById('quick-artist-save');
    const nameInput = document.getElementById('quick-artist-name');
    const genreInput = document.getElementById('quick-artist-genre');
    const status = document.getElementById('quick-artist-status');

    if (!openLink) return;

    function openPanel() {
        panel.classList.remove('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => nameInput.focus(), 200);
        openLink.textContent = 'Close quick add';
    }

    openLink.addEventListener('click', (e) => {
        e.preventDefault();
        if (panel.classList.contains('hidden')) {
            openPanel();
        } else {
            panel.classList.add('hidden');
            openLink.textContent = 'Add a new artist';
        }
    });

    closeBtn.addEventListener('click', () => {
        panel.classList.add('hidden');
        openLink.textContent = 'Add a new artist';
    });

    saveBtn.addEventListener('click', async () => {
        status.textContent = '';
        const stage_name = nameInput.value.trim();
        const genre = genreInput.value.trim();
        if (!stage_name) {
            status.textContent = 'Stage name is required';
            status.classList.remove('text-green-600');
            status.classList.add('text-red-600');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-60');
        status.textContent = 'Saving...';
        status.classList.remove('text-red-600');
        status.classList.add('text-gray-500');

        try {
            const response = await fetch(`{{ route('artists.quick-store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': `{{ csrf_token() }}`,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ stage_name, genre })
            });

            if (!response.ok) throw new Error('Request failed');
            const artist = await response.json();

            // Add to selected tags immediately
            const selectedContainer = document.getElementById('selected-artists');
            const hiddenInputsContainer = document.getElementById('artist-hidden-inputs');

            // Create tag
            const tag = document.createElement('span');
            tag.className = 'inline-flex items-center gap-1 bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm';
            tag.innerHTML = `${artist.stage_name}<button type="button" data-id="${artist.id}" class="ml-1 text-purple-600 hover:text-purple-800">×</button>`;
            selectedContainer.appendChild(tag);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'artists[]';
            input.value = artist.id;
            hiddenInputsContainer.appendChild(input);

            status.textContent = 'Added';
            status.classList.remove('text-gray-500');
            status.classList.add('text-green-600');
            nameInput.value = '';
            genreInput.value = '';
            panel.classList.add('hidden');
            openLink.textContent = 'Add a new artist';
        } catch (e) {
            status.textContent = 'Failed to save';
            status.classList.remove('text-gray-500');
            status.classList.add('text-red-600');
        } finally {
            saveBtn.disabled = false;
            saveBtn.classList.remove('opacity-60');
        }
    });
});
</script>

<script>
let venueAutocomplete;
let selectedVenueCoordinates = { latitude: null, longitude: null };

// Initialize Google Places Autocomplete for venue address
function initVenueAutocomplete() {
    const addressInput = document.getElementById('quick-venue-address');
    if (!addressInput || !window.google || !window.google.maps) return;

    venueAutocomplete = new google.maps.places.Autocomplete(addressInput, {
        types: ['establishment', 'geocode'],
        componentRestrictions: { country: 'za' } // Restrict to South Africa
    });

    venueAutocomplete.addListener('place_changed', function() {
        const place = venueAutocomplete.getPlace();
        
        if (!place.geometry || !place.geometry.location) {
            console.log('No details available for input: ' + place.name);
            return;
        }

        // Use the formatted address from Google
        addressInput.value = place.formatted_address;
        
        // Store coordinates
        selectedVenueCoordinates.latitude = place.geometry.location.lat();
        selectedVenueCoordinates.longitude = place.geometry.location.lng();
        
        // Auto-fill venue name if empty and place has a name
        const nameInput = document.getElementById('quick-venue-name');
        if (!nameInput.value && place.name) {
            nameInput.value = place.name;
        }

        console.log('Venue selected:', place.formatted_address);
        console.log('Coordinates:', selectedVenueCoordinates);
    });
}

// Load Google Maps API if not already loaded
function loadGoogleMapsForVenue() {
    // Check if Google Maps is already loaded
    if (window.google && window.google.maps) {
        initVenueAutocomplete();
        return;
    }

    // Check if script is already being loaded
    if (document.querySelector('script[src*="maps.googleapis.com"]')) {
        // Wait for it to load
        const checkGoogleMaps = setInterval(() => {
            if (window.google && window.google.maps) {
                clearInterval(checkGoogleMaps);
                initVenueAutocomplete();
            }
        }, 100);
        return;
    }

    // Load the script
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initVenueAutocomplete`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
}

// Quick add venue
document.addEventListener('DOMContentLoaded', () => {
    const openLink = document.getElementById('open-quick-venue');
    const panel = document.getElementById('quick-venue-panel');
    const closeBtn = document.getElementById('close-quick-venue');
    const saveBtn = document.getElementById('quick-venue-save');
    const nameInput = document.getElementById('quick-venue-name');
    const addressInput = document.getElementById('quick-venue-address');
    const capacityInput = document.getElementById('quick-venue-capacity');
    const phoneInput = document.getElementById('quick-venue-phone');
    const status = document.getElementById('quick-venue-status');
    const venueSelect = document.getElementById('venue_id');

    if (!openLink) return;

    // Load Google Maps API when page loads
    loadGoogleMapsForVenue();

    function openPanel() {
        panel.classList.remove('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => nameInput.focus(), 200);
        openLink.textContent = 'Close quick add';
    }

    openLink.addEventListener('click', (e) => {
        e.preventDefault();
        if (panel.classList.contains('hidden')) {
            openPanel();
        } else {
            panel.classList.add('hidden');
            openLink.textContent = 'Add a new venue';
        }
    });

    closeBtn.addEventListener('click', () => {
        panel.classList.add('hidden');
        openLink.textContent = 'Add a new venue';
    });

    saveBtn.addEventListener('click', async () => {
        status.textContent = '';
        const name = nameInput.value.trim();
        const address = addressInput.value.trim();
        const capacity = capacityInput.value.trim();
        const phone = phoneInput.value.trim();

        if (!name) {
            status.textContent = 'Venue name is required';
            status.classList.remove('text-green-600');
            status.classList.add('text-red-600');
            return;
        }

        if (!address) {
            status.textContent = 'Address is required';
            status.classList.remove('text-green-600');
            status.classList.add('text-red-600');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-60');
        status.textContent = 'Saving...';
        status.classList.remove('text-red-600');
        status.classList.add('text-gray-500');

        try {
            // Prepare data including coordinates from Google Places
            const venueData = { 
                name,
                address,
                latitude: selectedVenueCoordinates.latitude,
                longitude: selectedVenueCoordinates.longitude
            };
            if (capacity) {
                const parsedCap = parseInt(capacity, 10);
                if (!Number.isNaN(parsedCap)) venueData.capacity = parsedCap;
            }
            if (phone) {
                venueData.phone = phone;
            }

            const response = await fetch(`{{ route('venues.quick-store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': `{{ csrf_token() }}`,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(venueData)
            });
            if (!response.ok) {
                let message = 'Request failed';
                try {
                    const err = await response.json();
                    if (err && err.message) message = err.message;
                    // Show first validation error if present
                    if (err && err.errors) {
                        const firstKey = Object.keys(err.errors)[0];
                        if (firstKey && err.errors[firstKey][0]) message = err.errors[firstKey][0];
                    }
                } catch (_) {}
                throw new Error(message);
            }
            const venue = await response.json();

            // Select the new venue in the Alpine-powered venue selector
            const hiddenInput = document.querySelector('input[name="venue_id"]');
            if (hiddenInput) hiddenInput.value = venue.id;
            
            // Update Alpine component state if available to reflect selection in UI
            const venueSelectorRoot = document.querySelector('.venue-selector');
            // Broadcast a window-level event so the venue selector can react
            window.dispatchEvent(new CustomEvent('venue-quick-added', { detail: { venue } }));

            status.textContent = 'Venue added and selected!';
            status.classList.remove('text-gray-500');
            status.classList.add('text-green-600');
            
            // Clear form and coordinates
            nameInput.value = '';
            addressInput.value = '';
            capacityInput.value = '';
            phoneInput.value = '';
            selectedVenueCoordinates = { latitude: null, longitude: null };
            
            // Close panel immediately and scroll to selector so the user sees selection
            panel.classList.add('hidden');
            openLink.textContent = 'Add a new venue';
            const selectorDisplay = document.querySelector('.venue-selector');
            if (selectorDisplay) selectorDisplay.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (e) {
            status.textContent = e && e.message ? e.message : 'Failed to save venue';
            status.classList.remove('text-gray-500');
            status.classList.add('text-red-600');
        } finally {
            saveBtn.disabled = false;
            saveBtn.classList.remove('opacity-60');
        }
    });
});

// YouTube Videos Management
function addYoutubeVideoInput() {
    const container = document.getElementById('youtube-videos-container');
    const newInput = document.createElement('div');
    newInput.className = 'youtube-video-input flex gap-2';
    newInput.innerHTML = `
        <input
            type="url"
            name="youtube_videos[]"
            placeholder="https://www.youtube.com/watch?v=..."
            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
        />
        <button type="button" onclick="removeYoutubeVideoInput(this)" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 remove-video-btn">
            Remove
        </button>
    `;
    container.appendChild(newInput);
    
    // Show remove buttons if there's more than one input
    updateRemoveButtons();
}

function removeYoutubeVideoInput(button) {
    button.closest('.youtube-video-input').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const inputs = document.querySelectorAll('.youtube-video-input');
    inputs.forEach((input, index) => {
        const removeBtn = input.querySelector('.remove-video-btn');
        if (removeBtn) {
            if (inputs.length > 1) {
                removeBtn.classList.remove('hidden');
            } else {
                removeBtn.classList.add('hidden');
            }
        }
    });
}

// Initialize remove buttons visibility
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
});
</script>
@endsection

