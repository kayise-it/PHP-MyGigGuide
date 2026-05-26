@extends('layouts.app')

@section('title', 'Edit Venue - My Gig Guide')
@section('description', 'Update your venue information.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Venue</h1>
                <p class="text-gray-600">Update your venue information</p>
            </div>

            <form action="{{ route('venues.update', $venue) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Venue Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Venue Name *
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $venue->name) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-300 @enderror"
                        placeholder="Enter venue name"
                    >
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address with Google Maps Autocomplete -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        Address *
                    </label>
                    <input
                        type="text"
                        id="address"
                        name="address"
                        value="{{ old('address', $venue->address) }}"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('address') border-red-300 @enderror"
                        placeholder="Start typing address..."
                        autocomplete="off"
                    >
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Start typing to search for an address</p>
                </div>

                <!-- Hidden fields for coordinates -->
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $venue->latitude) }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $venue->longitude) }}">

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('description') border-red-300 @enderror"
                        placeholder="Describe your venue..."
                    >{{ old('description', $venue->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                        Capacity *
                    </label>
                    <input
                        type="number"
                        id="capacity"
                        name="capacity"
                        value="{{ old('capacity', $venue->capacity) }}"
                        min="1"
                        required
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('capacity') border-red-300 @enderror"
                        placeholder="Maximum number of people"
                    >
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Phone
                        </label>
                        <input
                            type="tel"
                            id="contact_phone"
                            name="contact_phone"
                            value="{{ old('contact_phone', $venue->phone_number) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_phone') border-red-300 @enderror"
                            placeholder="Phone number"
                        >
                        @error('contact_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Email
                        </label>
                        <input
                            type="email"
                            id="contact_email"
                            name="contact_email"
                            value="{{ old('contact_email', $venue->contact_email) }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_email') border-red-300 @enderror"
                            placeholder="Email address"
                        >
                        @error('contact_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                        Website
                    </label>
                    <input
                        type="url"
                        id="website"
                        name="website"
                        value="{{ old('website', $venue->website) }}"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('website') border-red-300 @enderror"
                        placeholder="https://example.com"
                    >
                    @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                        Venue Image
                    </label>
                    @if($venue->main_picture)
                        <div class="mb-2">
                            <img src="{{ Storage::url($venue->main_picture) }}" alt="{{ $venue->name }}" class="h-20 w-20 object-cover rounded-lg">
                            <p class="text-sm text-gray-500 mt-1">Current image</p>
                        </div>
                    @endif
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/*"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('image') border-red-300 @enderror"
                    >
                    <p class="mt-1 text-sm text-gray-500">Upload a new image for your venue (max 10MB)</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amenities -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Amenities
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $amenities = [
                                'Parking', 'WiFi', 'Air Conditioning', 'Sound System',
                                'Lighting', 'Stage', 'Bar', 'Kitchen',
                                'Restrooms', 'Accessibility', 'Outdoor Space', 'Security'
                            ];
                            $selectedAmenities = $venue->amenities ? json_decode($venue->amenities, true) : [];
                        @endphp
                        @foreach($amenities as $amenity)
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="{{ $amenity }}"
                                    {{ in_array($amenity, old('amenities', $selectedAmenities)) ? 'checked' : '' }}
                                    class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-700">{{ $amenity }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('amenities')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- YouTube Videos -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        YouTube Videos
                    </label>
                    
                    <!-- Existing Videos -->
                    @if($venue->youtubeVideos->count() > 0)
                    <div class="mb-6 space-y-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Videos</label>
                        @foreach($venue->youtubeVideos as $video)
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
                    <p class="mt-2 text-sm text-gray-500">Add YouTube video URLs to showcase your venue</p>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('dashboard') }}" class="btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Update Venue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let autocomplete;
let map;

function initAutocomplete() {
    // Create the autocomplete object
    autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('address'),
        {
            types: ['establishment', 'geocode'],
            componentRestrictions: { country: 'za' } // Restrict to South Africa
        }
    );

    // When the user selects an address from the dropdown
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        
        if (!place.geometry || !place.geometry.location) {
            console.log('No details available for input: ' + place.name);
            return;
        }

        // Get the formatted address
        const address = place.formatted_address;
        
        // Get coordinates
        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();

        // Update the form fields
        document.getElementById('address').value = address;
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        console.log('Address selected:', address);
        console.log('Coordinates:', lat, lng);
    });
}

// Load Google Maps API
function loadGoogleMaps() {
    // Use centralized loader instead of injecting script directly
    addGoogleMapsCallback(() => initAutocomplete());
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadGoogleMaps();
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
@endpush

<!-- Owners Management Section (Only visible to venue owners) -->
@auth
@if(isset($isOwner) && $isOwner)
<div class="mt-8 bg-white rounded-xl shadow-sm border border-purple-100 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
        </svg>
        Venue Owners Management
    </h3>
    
    <!-- Current Owners List -->
    @if(isset($venueOwners) && $venueOwners->count() > 0)
    <div class="space-y-3 mb-4">
        @foreach($venueOwners as $owner)
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                    <span class="text-white text-sm font-semibold">{{ substr($owner->name ?? $owner->email, 0, 1) }}</span>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $owner->name ?? 'Unknown' }}</p>
                    <p class="text-xs text-gray-500">{{ $owner->email }}</p>
                    @if($owner->pivot && $owner->pivot->role === 'primary')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 mt-1">Primary Owner</span>
                    @elseif($owner->pivot && $owner->pivot->role === 'co_owner')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">Co-Owner</span>
                    @elseif($owner->pivot && $owner->pivot->role === 'manager')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 mt-1">Manager</span>
                    @endif
                </div>
            </div>
            @if(isset($isPrimaryOwner) && $isPrimaryOwner && $owner->id !== auth()->id())
            <form action="{{ route('venues.remove-owner', $venue) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this owner?');">
                @csrf
                @method('DELETE')
                <input type="hidden" name="user_id" value="{{ $owner->id }}">
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                    Remove
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm text-gray-500 mb-4">No owners listed.</p>
    @endif
    
    <!-- Pending Ownership Requests (Only visible to primary owner) -->
    @if(isset($isPrimaryOwner) && $isPrimaryOwner && isset($pendingRequests) && $pendingRequests->count() > 0)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="text-sm font-semibold text-gray-900 mb-3">Pending Ownership Requests</h4>
        <div class="space-y-3">
            @foreach($pendingRequests as $request)
            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $request->requester->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-gray-600">{{ $request->requester->email ?? '' }}</p>
                        @if($request->reason)
                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($request->reason, 100) }}</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">Requested: {{ $request->requested_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <form action="{{ route('venues.approve-request', [$venue, $request]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                Approve
                            </button>
                        </form>
                        <form action="{{ route('venues.reject-request', [$venue, $request]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @elseif(isset($isPrimaryOwner) && $isPrimaryOwner)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <p class="text-sm text-gray-500">No pending ownership requests.</p>
    </div>
    @endif
</div>
@endif
@endauth

@endsection

