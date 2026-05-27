@extends('layouts.app')

@section('title', 'Edit Event - My Gig Guide')
@section('description', 'Edit event details and manage all aspects.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Event</h1>
                    <p class="text-gray-600 mt-2">Update your event details and manage all aspects</p>
                </div>
                <a href="{{ route('events.show', $event) }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Event
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('events.update', $event) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

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
                            value="{{ old('name', $event->name) }}"
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
                        >{{ old('description', $event->description) }}</textarea>
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
                            value="{{ old('date', $event->date->format('Y-m-d')) }}"
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
                            value="{{ old('time', $event->time ? $event->time->format('H:i') : '') }}"
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
                            value="{{ old('price', $event->price) }}"
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
                            value="{{ old('capacity', $event->capacity) }}"
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
                            value="{{ old('ticket_url', $event->ticket_url) }}"
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
                            :values="old('categories', $event->categories->pluck('id')->toArray())"
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
                        :selectedVenueId="old('venue_id', $event->venue_id)"
                        placeholder="Choose a venue for your event..."
                        userRole="organiser"
                        :organiserId="auth()->user()->id"
                        required
                        :hasError="$errors->has('venue_id')"
                    />
                    @error('venue_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">
                        Don't see your venue? <a href="{{ route('venues.create') }}" class="text-purple-600 hover:text-purple-700">Add a new venue</a>
                    </p>
                </div>
            </div>

            <!-- Artists Selection -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Artists</h2>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Booked Artists</label>
                    <x-artist-combobox name="artists" id="artists" :values="old('artists', $event->artists->pluck('id')->toArray())" class="w-full" />
                    @error('artists')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">Don't see an artist? <a href="{{ route('artists.create') }}" class="text-purple-600 hover:text-purple-700">Add a new artist</a></p>
                </div>
            </div>

            <!-- YouTube Videos -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">YouTube Videos</h2>
                
                <!-- Existing Videos -->
                @if($event->youtubeVideos->count() > 0)
                <div class="mb-6 space-y-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Videos</label>
                    @foreach($event->youtubeVideos as $video)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <input type="hidden" name="youtube_video_ids[]" value="{{ $video->id }}">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $video->youtube_url }}</p>
                            @if($video->title)
                            <p class="text-xs text-gray-500">{{ $video->title }}</p>
                            @endif
                        </div>
                        <button type="button" onclick="removeExistingVideo(this)" class="px-3 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600">
                            Remove
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
                
                <!-- New Videos -->
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
                    <!-- Current Poster -->
                    @if($event->poster)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Current Poster
                            </label>
                            <div class="flex items-center space-x-4">
                                <img src="{{ Storage::url($event->poster) }}" alt="Current poster" class="h-32 w-auto rounded-lg border border-gray-200">
                                <div>
                                    <p class="text-sm text-gray-600">Upload a new poster to replace this one</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Poster Upload (compact if poster exists) -->
                    <div>
                        <label for="poster" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ $event->poster ? 'Replace Poster' : 'Event Poster' }}
                        </label>
                        @if($event->poster)
                            <label class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-purple-700 hover:bg-gray-50 cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M4 12l4-4a3 3 0 014 0l4 4m-6-2v10"/></svg>
                                <span>Choose new poster</span>
                                <input id="poster" name="poster" type="file" class="sr-only" accept="image/*" onchange="previewImage(this, 'poster-preview')">
                            </label>
                        @else
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
                        @endif
                        <div id="poster-preview" class="mt-4 hidden">
                            <img class="h-32 w-auto mx-auto rounded-lg" alt="Poster preview">
                        </div>
                        @error('poster')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Current Gallery (if any) -->
                    @php
                        $galleryImages = is_string($event->gallery) ? json_decode($event->gallery, true) : ($event->gallery ?? []);
                        if (!is_array($galleryImages)) { $galleryImages = []; }
                    @endphp
                    @if(count($galleryImages) > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Gallery</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                @foreach($galleryImages as $img)
                                    <img src="{{ Storage::url($img) }}" alt="Gallery image" class="h-24 w-full object-cover rounded-lg border border-gray-200">
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Gallery Upload (compact if existing gallery) -->
                    <div>
                        <label for="gallery" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ count($galleryImages) > 0 ? 'Add More Images' : 'Event Gallery' }}
                        </label>
                        @if(count($galleryImages) > 0)
                            <label class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-purple-700 hover:bg-gray-50 cursor-pointer">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M4 12l4-4a3 3 0 014 0l4 4m-6-2v10"/></svg>
                                <span>Upload images</span>
                                <input id="gallery" name="gallery[]" type="file" class="sr-only" accept="image/*" multiple onchange="previewGallery(this, 'gallery-preview')">
                            </label>
                        @else
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
                        @endif
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
                <a href="{{ route('events.show', $event) }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Update Event
                </button>
            </div>
        </form>

        <div class="{{ $siteBrand->detailPanelClass() }} mt-8 border-red-500/30">
            <h2 class="text-xl font-bold text-red-400 mb-2">Delete event</h2>
            <p class="text-slate-400 text-sm mb-4">Permanently remove this event and its images. This cannot be undone.</p>
            <form action="{{ route('events.destroy', $event) }}" method="POST"
                onsubmit="return confirm('Delete this event permanently? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    Delete event
                </button>
            </form>
        </div>
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
    updateRemoveButtons();
}

function removeYoutubeVideoInput(button) {
    button.closest('.youtube-video-input').remove();
    updateRemoveButtons();
}

function removeExistingVideo(button) {
    button.closest('.flex.items-center').remove();
}

function updateRemoveButtons() {
    const inputs = document.querySelectorAll('.youtube-video-input');
    inputs.forEach((input) => {
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

document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
});
</script>
@endsection
