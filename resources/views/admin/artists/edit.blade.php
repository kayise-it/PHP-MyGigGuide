@extends('layouts.admin')

@section('title', 'Edit Artist - Admin Panel')
@section('description', 'Edit artist information and details.')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Artist: {{ $artist->stage_name }}</h1>
            <p class="text-gray-600">Update artist information and details</p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->has('general'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $errors->first('general') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.artists.update', $artist) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Profile Picture -->
                <div class="mb-6">
                    <x-profile-picture-upload 
                        name="profile_picture"
                        id="profile_picture"
                        :currentImage="$artist->profile_picture"
                        showCurrent="true"
                        maxSize="10MB"
                        class="w-full"
                        previewSize="h-32 w-32"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Stage Name -->
                    <div>
                        <label for="stage_name" class="block text-sm font-medium text-gray-700 mb-2">Stage Name *</label>
                        <input type="text" id="stage_name" name="stage_name" value="{{ old('stage_name', $artist->stage_name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stage_name') border-red-500 @enderror">
                        @error('stage_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Real Name -->
                    <div>
                        <label for="real_name" class="block text-sm font-medium text-gray-700 mb-2">Real Name *</label>
                        <input type="text" id="real_name" name="real_name" value="{{ old('real_name', $artist->real_name) }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('real_name') border-red-500 @enderror">
                        @error('real_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bio -->
                    <div class="md:col-span-2">
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio *</label>
                        <textarea id="bio" name="bio" rows="4" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('bio') border-red-500 @enderror">{{ old('bio', $artist->bio) }}</textarea>
                        @error('bio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Genre -->
                    <div>
                        <label for="genre" class="block text-sm font-medium text-gray-700 mb-2">Genre *</label>
                        <x-genre-select 
                            id="genre" 
                            name="genre" 
                            :value="old('genre', $artist->genre)" 
                            required 
                            use-names
                            class="w-full {{ $errors->has('genre') ? 'border-red-500' : '' }}" 
                        />
                        @error('genre')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Email -->
                    <div class="md:col-span-2">
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Contact Email
                            @if(!$artist->user_id)
                                <span class="text-red-500">*</span>
                                <span class="text-gray-500 text-xs">(required for unclaimed artists)</span>
                            @endif
                        </label>
                        <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $artist->contact_email) }}"
                               @if(!$artist->user_id) required @endif
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_email') border-red-500 @enderror">
                        @error('contact_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $artist->phone_number) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('phone_number') border-red-500 @enderror">
                        @error('phone_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Instagram -->
                    <div>
                        <label for="instagram" class="block text-sm font-medium text-gray-700 mb-2">Instagram</label>
                        <input type="url" id="instagram" name="instagram" value="{{ old('instagram', $artist->instagram) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('instagram') border-red-500 @enderror">
                        @error('instagram')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Facebook -->
                    <div>
                        <label for="facebook" class="block text-sm font-medium text-gray-700 mb-2">Facebook</label>
                        <input type="url" id="facebook" name="facebook" value="{{ old('facebook', $artist->facebook) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('facebook') border-red-500 @enderror">
                        @error('facebook')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Twitter -->
                    <div>
                        <label for="twitter" class="block text-sm font-medium text-gray-700 mb-2">Twitter</label>
                        <input type="url" id="twitter" name="twitter" value="{{ old('twitter', $artist->twitter) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('twitter') border-red-500 @enderror">
                        @error('twitter')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- YouTube Videos -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">YouTube Videos</label>
                        
                        <!-- Existing Videos -->
                        @if($artist->youtubeVideos->count() > 0)
                        <div class="mb-6 space-y-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Videos</label>
                            @foreach($artist->youtubeVideos as $video)
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
                        <p class="mt-2 text-sm text-gray-500">Add YouTube video URLs to showcase the artist</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-8">
                    <a href="{{ route('admin.artists.show', $artist) }}" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                        Update Artist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
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
@endsection
