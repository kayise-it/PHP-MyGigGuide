@extends('layouts.admin')

@section('title', 'Create Artist - Admin Panel')
@section('description', 'Create a new artist in the system.')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Create New Artist</h1>
            <p class="text-gray-600">Add a new artist to the system</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.artists.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Unclaimed Artist Option -->
                    <div class="md:col-span-2">
                        <label class="flex items-center space-x-2 p-4 bg-purple-50 border border-purple-200 rounded-lg cursor-pointer hover:bg-purple-100 transition-colors">
                            <input type="checkbox" 
                                   id="is_unclaimed" 
                                   name="is_unclaimed" 
                                   value="1"
                                   {{ old('is_unclaimed') ? 'checked' : '' }}
                                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Create as Unclaimed Artist</span>
                                <p class="text-xs text-gray-600">This artist profile will not be linked to any user account and can be claimed later</p>
                            </div>
                        </label>
                    </div>

                    <!-- User Selection -->
                    <div class="md:col-span-2" id="user-selection">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Assign to User
                            <span class="text-gray-500 text-xs">(optional if unclaimed)</span>
                        </label>
                        <select id="user_id" name="user_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('user_id') border-red-500 @enderror">
                            <option value="">No user (unclaimed)</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Email -->
                    <div class="md:col-span-2">
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Email
                            <span class="text-gray-500 text-xs">(required for unclaimed artists)</span>
                        </label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_email') border-red-500 @enderror">
                        @error('contact_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stage Name -->
                    <div>
                        <label for="stage_name" class="block text-sm font-medium text-gray-700 mb-2">Stage Name *</label>
                        <input type="text" id="stage_name" name="stage_name" value="{{ old('stage_name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stage_name') border-red-500 @enderror">
                        @error('stage_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Real Name -->
                    <div>
                        <label for="real_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Real Name
                            <span class="text-gray-500 text-xs">(optional)</span>
                        </label>
                        <input type="text" id="real_name" name="real_name" value="{{ old('real_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('real_name') border-red-500 @enderror">
                        @error('real_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Genre -->
                    <div>
                        <label for="genre" class="block text-sm font-medium text-gray-700 mb-2">Genre *</label>
                        <x-genre-combobox 
                            id="genre" 
                            name="genre" 
                            :value="old('genre')" 
                            required 
                            class="w-full @error('genre') border-red-500 @enderror" 
                        />
                        @error('genre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Experience Level -->
                    <div>
                        <label for="experience_level" class="block text-sm font-medium text-gray-700 mb-2">Experience Level</label>
                        <select id="experience_level" name="experience_level"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('experience_level') border-red-500 @enderror">
                            <option value="">Select experience level</option>
                            <option value="beginner" {{ old('experience_level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="intermediate" {{ old('experience_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="advanced" {{ old('experience_level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                            <option value="professional" {{ old('experience_level') == 'professional' ? 'selected' : '' }}>Professional</option>
                        </select>
                        @error('experience_level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bio -->
                    <div class="md:col-span-2">
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                        <textarea id="bio" name="bio" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('bio') border-red-500 @enderror">{{ old('bio') }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Profile Picture -->
                    <div class="md:col-span-2">
                        <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('profile_picture') border-red-500 @enderror">
                        @error('profile_picture')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- YouTube Videos -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">YouTube Videos</label>
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
                        <p class="mt-2 text-sm text-gray-500">Add YouTube video URLs to showcase the artist</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-8">
                    <a href="{{ route('admin.artists.index') }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        Create Artist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const unclaimedCheckbox = document.getElementById('is_unclaimed');
    const userSelect = document.getElementById('user_id');
    const userSelection = document.getElementById('user-selection');
    const contactEmailInput = document.getElementById('contact_email');

    function toggleUserSelection() {
        if (unclaimedCheckbox.checked) {
            userSelect.value = '';
            userSelect.disabled = true;
            userSelection.style.opacity = '0.5';
            // Make contact email required for unclaimed artists
            contactEmailInput.required = true;
            contactEmailInput.classList.add('border-purple-300');
        } else {
            userSelect.disabled = false;
            userSelection.style.opacity = '1';
            // Make contact email optional for claimed artists
            contactEmailInput.required = false;
            contactEmailInput.classList.remove('border-purple-300');
        }
    }

    // Initial state
    toggleUserSelection();

    // Listen for changes
    unclaimedCheckbox.addEventListener('change', toggleUserSelection);
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

