@extends('layouts.app')

@section('title', 'Create Venue - My Gig Guide')
@section('description', 'Add a new venue to your profile.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50 py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Create New Venue</h1>
                <p class="text-gray-600">Add a venue to your profile and start hosting events</p>
            </div>

            <form action="{{ route('venues.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <!-- Venue Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Venue Name *
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
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
                        value="{{ old('address') }}"
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
                <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">

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
                    >{{ old('description') }}</textarea>
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
                        value="{{ old('capacity') }}"
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
                            value="{{ old('contact_phone') }}"
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
                            value="{{ old('contact_email') }}"
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
                        value="{{ old('website') }}"
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
                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/*"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('image') border-red-300 @enderror"
                    >
                    <p class="mt-1 text-sm text-gray-500">Upload a photo of your venue (max 10MB)</p>
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
                        @endphp
                        @foreach($amenities as $amenity)
                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="{{ $amenity }}"
                                    {{ in_array($amenity, old('amenities', [])) ? 'checked' : '' }}
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Venue
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
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places&callback=initAutocomplete`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
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
@endsection
