@extends('layouts.admin')

@section('title', 'Edit Event - Admin Panel')
@section('description', 'Edit event information and details.')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Event: {{ $event->name }}</h1>
            <p class="text-gray-600">Update event information and details</p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Event Name -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Event Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $event->name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea id="description" name="description" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $event->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                        <input type="date" id="date" name="date" value="{{ old('date', $event->date->format('Y-m-d')) }}" required
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('date') border-red-500 @enderror">
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Time -->
                    <div>
                        <label for="time" class="block text-sm font-medium text-gray-700 mb-2">Time *</label>
                        <input type="time" id="time" name="time" value="{{ old('time', $event->time ? $event->time->format('H:i') : '') }}" required
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
                            :selectedVenueId="old('venue_id', $event->venue_id)"
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
                        <input type="number" id="price" name="price" value="{{ old('price', $event->price) }}" step="0.01" min="0"
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
                            <option value="upcoming" {{ old('status', $event->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="ongoing" {{ old('status', $event->status) == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ old('status', $event->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $event->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Capacity -->
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                        <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $event->capacity) }}" min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('capacity') border-red-500 @enderror">
                        @error('capacity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Categories -->
                    <div class="md:col-span-2">
                        <label for="categories" class="block text-sm font-medium text-gray-700 mb-2">Categories</label>
                        <x-category-combobox 
                            name="categories" 
                            :values="old('categories', $event->categories->pluck('id')->toArray())"
                            placeholder="Select event categories (e.g., Concert, Festival)..."
                            class="@error('categories') border-red-500 @enderror"
                        />
                        @error('categories')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Choose one or more categories that best describe this event</p>
                    </div>

                    <!-- Change Owner -->
                    <div class="md:col-span-2">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Change Event Owner</label>
                        <x-user-selector 
                            name="user_id" 
                            :selectedUserId="old('user_id', $event->owner_id)"
                            placeholder="Select a user to transfer ownership to..."
                            userRole="all"
                            required
                            class="{{ $errors->has('user_id') ? 'border-red-500' : '' }}"
                        />
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">As a superuser, you can transfer event ownership to any user.</p>
                    </div>

                    <!-- Current Poster -->
                    @if($event->poster)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Poster</label>
                            <div class="flex items-center space-x-4">
                                <img id="currentPosterImg" src="{{ Storage::url($event->poster) }}" alt="Current Poster" class="h-32 w-32 object-cover rounded-lg">
                                <div>
                                    <p class="text-sm text-gray-600">Current poster image</p>
                                    <p class="text-xs text-gray-500">Upload a new image below to replace it</p>
                                </div>
                            </div>
                            <!-- Temp preview (hidden until a new poster is selected) -->
                            <div id="posterTempPreview" class="mt-3 hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Poster Preview</label>
                                <img class="h-32 w-32 object-cover rounded-lg" alt="New Poster Preview">
                            </div>
                        </div>
                    @endif

                    <!-- Poster -->
                    <div class="md:col-span-2">
                        <label for="poster" class="block text-sm font-medium text-gray-700 mb-2">Event Poster</label>
                        <input type="file" id="poster" name="poster" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('poster') border-red-500 @enderror">
                        @error('poster')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Leave empty to keep current poster</p>
                    </div>

                    <!-- Current Gallery with delete controls -->
                    @if($event->gallery)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Gallery</label>
                            <div id="currentGallery" class="grid grid-cols-4 gap-4">
                                @foreach($event->gallery as $image)
                                    <div class="relative group" data-path="{{ $image }}">
                                        <img src="{{ Storage::url($image) }}" alt="Gallery Image" class="h-24 w-full object-cover rounded-lg">
                                        <button type="button" class="absolute top-1 right-1 bg-red-600 text-white rounded-full h-7 w-7 flex items-center justify-center shadow hover:bg-red-700" title="Remove image" onclick="markGalleryDelete(this)" aria-label="Remove image">×</button>
                                    </div>
                                @endforeach
                            </div>
                            <div id="deleteGalleryInputs"></div>
                            <p class="mt-2 text-xs text-gray-500">Click the × to remove specific images. Upload below to add more.</p>
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

                <!-- Small helper script for poster preview and gallery deletions -->
                <script>
                (function() {
                    const posterInput = document.getElementById('poster');
                    if (posterInput) {
                        posterInput.addEventListener('change', function() {
                            const file = this.files && this.files[0];
                            const temp = document.getElementById('posterTempPreview');
                            const current = document.getElementById('currentPosterImg');
                            if (!file || !temp) return;
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                temp.querySelector('img').src = e.target.result;
                                temp.classList.remove('hidden');
                                if (current) {
                                    current.parentElement.style.display = 'none';
                                }
                            };
                            reader.readAsDataURL(file);
                        });
                    }

                    window.markGalleryDelete = function(btn) {
                        const wrapper = btn.closest('[data-path]');
                        if (!wrapper) return;
                        const path = wrapper.getAttribute('data-path');
                        // Add hidden input once
                        const inputsHolder = document.getElementById('deleteGalleryInputs');
                        if (inputsHolder && !inputsHolder.querySelector(`input[value="${path}"]`)) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'delete_gallery[]';
                            input.value = path;
                            inputsHolder.appendChild(input);
                        }
                        // Visually mark/remove
                        wrapper.classList.add('opacity-50');
                        wrapper.style.position = 'relative';
                        const overlay = document.createElement('div');
                        overlay.className = 'absolute inset-0 bg-white/70 rounded-lg flex items-center justify-center text-sm text-red-700 font-medium';
                        overlay.textContent = 'Will be removed';
                        wrapper.appendChild(overlay);
                    }
                })();
                </script>

                <div class="flex justify-end space-x-4 mt-8">
                    <a href="{{ route('admin.events.show', $event) }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

