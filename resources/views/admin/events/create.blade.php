@extends('layouts.admin')

@section('title', 'Create Event - Admin Panel')
@section('description', 'Create a new event in the system.')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Create New Event</h1>
            <p class="text-gray-600">Add a new event to the system</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Event Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Event Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                        <input type="date" id="date" name="date" value="{{ old('date') }}" required
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('date') border-red-500 @enderror">
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Time -->
                    <div>
                        <label for="time" class="block text-sm font-medium text-gray-700 mb-2">Time *</label>
                        <input type="time" id="time" name="time" value="{{ old('time') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('time') border-red-500 @enderror">
                        @error('time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Venue -->
                    <div>
                        <label for="venue_id" class="block text-sm font-medium text-gray-700 mb-2">Venue *</label>
                        <x-venue-selector 
                            name="venue_id" 
                            :selectedVenueId="old('venue_id')"
                            placeholder="Select a venue..."
                            userRole="all"
                            required
                            :hasError="$errors->has('venue_id')"
                        />
                        @error('venue_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price (R)</label>
                        <input type="number" id="price" name="price" value="{{ old('price') }}" step="0.01" min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('price') border-red-500 @enderror">
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                        <select id="status" name="status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('status') border-red-500 @enderror">
                            <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="ongoing" {{ old('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Categories -->
                    <div class="md:col-span-2">
                        <label for="categories" class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                        <x-category-combobox 
                            name="categories" 
                            :values="old('categories', [])"
                            placeholder="Select event categories (e.g., Concert, Festival)..."
                            class="@error('categories') border-red-500 @enderror"
                        />
                        @error('categories')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Choose one or more categories that best describe this event</p>
                    </div>

                    <!-- Artists Selection -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Artists</label>
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
                                        <button type="button" id="quick-artist-save" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Save</button>
                                    </div>
                                </div>
                                <p id="quick-artist-status" class="mt-2 text-sm text-gray-500"></p>
                            </div>
                        </div>
                        @error('artists')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            Don't see an artist? <a href="{{ route('admin.artists.create') }}" id="open-quick-artist" class="text-purple-600 hover:text-purple-700">Add a new artist</a>
                        </p>
                    </div>

                    <!-- Create On Behalf Of -->
                    <div class="md:col-span-2">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Create Event On Behalf Of *</label>
                        <x-user-selector 
                            name="user_id" 
                            :selectedUserId="old('user_id')"
                            placeholder="Select a user to create event on behalf of..."
                            userRole="all"
                            required
                            class="{{ $errors->has('user_id') ? 'border-red-500' : '' }}"
                        />
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">As a superuser, you can create events on behalf of any user, artist, organiser, or venue owner.</p>
                    </div>

                    <!-- Poster -->
                    <div class="md:col-span-2">
                        <label for="poster" class="block text-sm font-medium text-gray-700 mb-2">Event Poster</label>
                        <input type="file" id="poster" name="poster" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('poster') border-red-500 @enderror">
                        @error('poster')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gallery -->
                    <div class="md:col-span-2">
                        <label for="gallery" class="block text-sm font-medium text-gray-700 mb-2">Gallery Images</label>
                        <input type="file" id="gallery" name="gallery[]" accept="image/*" multiple
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('gallery') border-red-500 @enderror">
                        @error('gallery')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">You can select multiple images</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-8">
                    <a href="{{ route('admin.events.index') }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
        if (!items || items.length === 0) {
            resultsBox.innerHTML = '<div class="px-3 py-2 text-gray-500">No artists found</div>';
            resultsBox.classList.remove('hidden');
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

    // Initial render
    renderSelectedTags();

    // Search functionality
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        if (query.length < 2) {
            resultsBox.classList.add('hidden');
            return;
        }
        const filtered = allArtists.filter(artist => 
            artist.stage_name.toLowerCase().includes(query) || 
            (artist.genre && artist.genre.toLowerCase().includes(query))
        );
        renderResults(filtered);
    });

    // Remove selected artists
    selectedContainer.addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON') {
            const id = e.target.getAttribute('data-id');
            selectedIds.delete(id);
            renderSelectedTags();
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
            resultsBox.classList.add('hidden');
        }
    });
});

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

    openLink.addEventListener('click', (e) => {
        e.preventDefault();
        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            openLink.textContent = 'Cancel';
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

            // Reset form
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
@endsection
