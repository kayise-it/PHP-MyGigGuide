@extends('layouts.admin')

@section('title', 'Edit Venue - Admin Panel')
@section('description', 'Edit venue information and details.')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Venue: {{ $venue->name }}</h1>
            <p class="text-gray-600">Update venue information and det7667ails</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.venues.update', $venue) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Venue Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Venue Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $venue->name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $venue->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address with Google Maps Autocomplete -->
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                        <input type="text" id="address" name="address" value="{{ old('address', $venue->address) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('address') border-red-500 @enderror"
                               placeholder="Start typing address..." autocomplete="off">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    

                    <!-- City -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                        <input type="text" id="city" name="city" value="{{ old('city', $venue->city) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('city') border-red-500 @enderror">
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Email -->
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $venue->contact_email) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_email') border-red-500 @enderror">
                        @error('contact_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Owner Email -->
                    <div>
                        <label for="owner_email" class="block text-sm font-medium text-gray-700 mb-2">Venue Owner Email</label>
                        @if($venue->user)
                            <input
                                type="email"
                                id="owner_email"
                                name="owner_email"
                                value="{{ old('owner_email', $venue->user->email) }}"
                                @unless(auth()->check() && auth()->user()->hasRole('superuser')) disabled @endunless
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('owner_email') border-red-500 @enderror {{ auth()->check() && auth()->user()->hasRole('superuser') ? '' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }}"
                            >
                            @unless(auth()->check() && auth()->user()->hasRole('superuser'))
                                <p class="mt-1 text-xs text-gray-500">Only superusers can update the owner email.</p>
                            @endunless
                        @else
                            <p class="text-sm text-gray-500">No owner account linked to this venue.</p>
                        @endif
                        @error('owner_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Capacity -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                        <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $venue->capacity) }}" min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('capacity') border-red-500 @enderror">
                        @error('capacity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Latitude -->
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                        <input type="number" id="latitude" name="latitude" value="{{ old('latitude', $venue->latitude) }}" step="any"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('latitude') border-red-500 @enderror">
                        @error('latitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Longitude -->
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                        <input type="number" id="longitude" name="longitude" value="{{ old('longitude', $venue->longitude) }}" step="any"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('longitude') border-red-500 @enderror">
                        @error('longitude')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Current Main Picture -->
                    @if($venue->main_picture)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Main Picture</label>
                            <div class="flex items-center space-x-4">
                                <img src="{{ Storage::url($venue->main_picture) }}" alt="Current Main Picture" class="h-32 w-32 object-cover rounded-lg">
                                <div>
                                    <p class="text-sm text-gray-600">Current main picture</p>
                                    <p class="text-xs text-gray-500">Upload a new image below to replace it</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Main Picture -->
                    <div class="md:col-span-2">
                        <label for="main_picture" class="block text-sm font-medium text-gray-700 mb-2">Main Picture</label>
                        <input type="file" id="main_picture" name="main_picture" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('main_picture') border-red-500 @enderror">
                        @error('main_picture')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Leave empty to keep current picture</p>
                    </div>

                    <!-- Current Gallery -->
                    @if($venue->venue_gallery)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Gallery</label>
                            <div class="grid grid-cols-4 gap-4">
                                @foreach($venue->venue_gallery as $image)
                                    <img src="{{ Storage::url($image) }}" alt="Gallery Image" class="h-24 w-full object-cover rounded-lg">
                                @endforeach
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Upload new images below to replace the gallery</p>
                        </div>
                    @endif

                    <!-- Gallery -->
                    <div class="md:col-span-2">
                        <label for="gallery" class="block text-sm font-medium text-gray-700 mb-2">Gallery Images</label>
                        <input type="file" id="gallery" name="gallery[]" accept="image/*" multiple
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('gallery') border-red-500 @enderror">
                        @error('gallery')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">You can select multiple images. Leave empty to keep current gallery.</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-8">
                    <a href="{{ route('admin.venues.show', $venue) }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        Update Venue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('head')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key', 'AIzaSyBvOkBw3cLWlIqFIBi5DswhQcKf3eJvA8Y') }}&libraries=places&callback=initGoogleMaps" async defer></script>
@endpush

@push('scripts')
<script>
let autocomplete;

function initGoogleMaps() {
    initAutocomplete();
}

function initAutocomplete() {
    autocomplete = new google.maps.places.Autocomplete(
        document.getElementById('address'),
        {
            types: ['establishment', 'geocode'],
            componentRestrictions: { country: 'za' } // Restrict to South Africa
        }
    );

    const setFromPlace = (place) => {
        if (!place || !place.geometry || !place.geometry.location) return false;
        const address = place.formatted_address || document.getElementById('address').value;
        const lat = place.geometry.location.lat();
        const lng = place.geometry.location.lng();
        document.getElementById('address').value = address;
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        
        // Also try to extract city from address components
        if (place.address_components) {
            for (let component of place.address_components) {
                if (component.types.includes('locality') || component.types.includes('administrative_area_level_2')) {
                    document.getElementById('city').value = component.long_name;
                    break;
                }
            }
        }
        return true;
    };

    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        setFromPlace(place);
    });

    // Fallback: user typed address but didn't pick a suggestion
    const addressInput = document.getElementById('address');
    const geocoder = new google.maps.Geocoder();
    const geocodeNow = () => {
        const val = addressInput.value && addressInput.value.trim();
        if (!val) return;
        geocoder.geocode({ address: val, componentRestrictions: { country: 'ZA' } }, (results, status) => {
            if (status === 'OK' && results && results[0]) {
                setFromPlace(results[0]);
            }
        });
    };
    addressInput.addEventListener('blur', geocodeNow);
    addressInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); geocodeNow(); }});
}
</script>
@endpush
@endsection
