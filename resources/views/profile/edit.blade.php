@extends('layouts.app')

@section('title', 'Edit Profile - My Gig Guide')
@section('description', 'Update your profile information and preferences.')

@section('content')
<div class="{{ $siteBrand->pageContentShellClass() }} min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
                    <p class="text-gray-600 mt-2">Update your account information and preferences</p>
                </div>
                <a href="{{ route('profile.show') }}" class="btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Profile
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('name') border-red-300 @enderror"
                        />
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                            Username
                        </label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="{{ old('username', $user->username) }}"
                            readonly
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed"
                        />
                        <p class="mt-1 text-sm text-gray-500">Username cannot be changed</p>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            readonly
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed"
                        />
                        <p class="mt-1 text-sm text-gray-500">Email address cannot be changed</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Account Type
                        </label>
                        <div class="flex space-x-2">
                            @foreach($user->roles as $role)
                                <span class="inline-block px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 rounded-full">
                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                </span>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Account type cannot be changed</p>
                    </div>
                </div>
            </div>

            <!-- Profile Picture -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Profile Picture</h2>
                
                <div class="flex items-start space-x-6">
                    <!-- Current Profile Picture -->
                    <div class="flex-shrink-0">
                        @php
                            $profileImage = asset('logos/logo2.jpeg');
                            if ($user->profile_picture && !str_contains($user->profile_picture, '/tmp/php') && !str_contains($user->profile_picture, 'tmp.php')) {
                                $profileImage = Storage::url($user->profile_picture);
                            }
                        @endphp
                        <img id="current-profile-picture" src="{{ $profileImage }}" alt="Current Profile Picture" class="h-32 w-32 rounded-lg object-cover border-4 border-gray-200">
                        <p class="text-sm text-gray-600 mt-2 text-center">Current Picture</p>
                    </div>
                    
                    <!-- Upload New Picture -->
                    <div class="flex-1">
                        <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">
                            Upload New Profile Picture
                        </label>
                        <input
                            type="file"
                            id="profile_picture"
                            name="profile_picture"
                            accept="image/*"
                            onchange="previewImage(this)"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('profile_picture') border-red-300 @enderror"
                        />
                        @error('profile_picture')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">
                            Upload a JPG, PNG, or GIF image. Maximum size: 10MB.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Role-specific Information -->
            @if($user->hasRole('artist') || $user->hasRole('organiser'))
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        @if($user->hasRole('artist'))
                            Artist Profile
                        @elseif($user->hasRole('organiser'))
                            Organization Profile
                        @endif
                    </h2>
                    
                    @if($user->hasRole('artist'))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="stage_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stage Name
                                </label>
                                <input
                                    type="text"
                                    id="stage_name"
                                    name="stage_name"
                                    value="{{ old('stage_name', $profile->stage_name ?? '') }}"
                                    required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stage_name') border-red-300 @enderror"
                                />
                                @error('stage_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="real_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Real Name
                                </label>
                                <input
                                    type="text"
                                    id="real_name"
                                    name="real_name"
                                    value="{{ old('real_name', $profile->real_name ?? '') }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('real_name') border-red-300 @enderror"
                                />
                                @error('real_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="genre" class="block text-sm font-medium text-gray-700 mb-2">
                                    Genre
                                </label>
                                <x-genre-select 
                                    id="genre" 
                                    name="genre" 
                                    :value="old('genre', $profile->genre ?? '')" 
                                    required 
                                    use-names
                                    class="block w-full @error('genre') border-red-300 @enderror" 
                                />
                                @error('genre')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Contact Phone
                                </label>
                                <input
                                    type="text"
                                    id="phone_number"
                                    name="phone_number"
                                    value="{{ old('phone_number', $profile->phone_number ?? '') }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('phone_number') border-red-300 @enderror"
                                />
                                @error('phone_number')
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
                                    value="{{ old('contact_email', $profile->contact_email ?? '') }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_email') border-red-300 @enderror"
                                />
                                @error('contact_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="instagram" class="block text-sm font-medium text-gray-700 mb-2">
                                    Instagram URL
                                </label>
                                <input
                                    type="url"
                                    id="instagram"
                                    name="instagram"
                                    value="{{ old('instagram', $profile->instagram ?? '') }}"
                                    placeholder="https://instagram.com/..."
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('instagram') border-red-300 @enderror"
                                />
                                @error('instagram')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="facebook" class="block text-sm font-medium text-gray-700 mb-2">
                                    Facebook URL
                                </label>
                                <input
                                    type="url"
                                    id="facebook"
                                    name="facebook"
                                    value="{{ old('facebook', $profile->facebook ?? '') }}"
                                    placeholder="https://facebook.com/..."
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('facebook') border-red-300 @enderror"
                                />
                                @error('facebook')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="twitter" class="block text-sm font-medium text-gray-700 mb-2">
                                    X / Twitter URL
                                </label>
                                <input
                                    type="url"
                                    id="twitter"
                                    name="twitter"
                                    value="{{ old('twitter', $profile->twitter ?? '') }}"
                                    placeholder="https://x.com/..."
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('twitter') border-red-300 @enderror"
                                />
                                @error('twitter')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="tiktok" class="block text-sm font-medium text-gray-700 mb-2">
                                    TikTok URL
                                </label>
                                <input
                                    type="url"
                                    id="tiktok"
                                    name="tiktok"
                                    value="{{ old('tiktok', $profile->tiktok ?? '') }}"
                                    placeholder="https://www.tiktok.com/@artist"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('tiktok') border-red-300 @enderror"
                                />
                                @error('tiktok')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="artist_profile_picture" class="block text-sm font-medium text-gray-700 mb-2">
                                    Artist page photo
                                </label>
                                @php
                                    $artistPhoto = null;
                                    if ($profile?->profile_picture && !str_contains($profile->profile_picture, '/tmp/php')) {
                                        $artistPhoto = Storage::url($profile->profile_picture);
                                    }
                                @endphp
                                @if($artistPhoto)
                                    <img src="{{ $artistPhoto }}" alt="Artist page photo" class="h-24 w-24 rounded-lg object-cover border border-gray-200 mb-3">
                                @endif
                                <input
                                    type="file"
                                    id="artist_profile_picture"
                                    name="artist_profile_picture"
                                    accept="image/*"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('artist_profile_picture') border-red-300 @enderror"
                                />
                                @error('artist_profile_picture')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Shown on your public artist page (not your account avatar above).</p>
                            </div>

                            <div class="md:col-span-2">
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bio
                                </label>
                                <textarea
                                    id="bio"
                                    name="bio"
                                    rows="4"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('bio') border-red-300 @enderror"
                                >{{ old('bio', $profile->bio ?? '') }}</textarea>
                                @error('bio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @elseif($user->hasRole('organiser'))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="organisation_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Organization Name
                                </label>
                                <input
                                    type="text"
                                    id="organisation_name"
                                    name="organisation_name"
                                    value="{{ old('organisation_name', $profile->organisation_name ?? '') }}"
                                    required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('organisation_name') border-red-300 @enderror"
                                />
                                @error('organisation_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="organiser_phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Contact Phone
                                </label>
                                <input
                                    type="text"
                                    id="organiser_phone_number"
                                    name="phone_number"
                                    value="{{ old('phone_number', $profile->phone_number ?? '') }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('phone_number') border-red-300 @enderror"
                                />
                                @error('phone_number')
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
                                    value="{{ old('contact_email', $profile->contact_email ?? '') }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_email') border-red-300 @enderror"
                                />
                                @error('contact_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                                    Website
                                </label>
                                <input
                                    type="url"
                                    id="website"
                                    name="website"
                                    value="{{ old('website', $profile->website ?? '') }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('website') border-red-300 @enderror"
                                />
                                @error('website')
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
                                >{{ old('description', $profile->description ?? '') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Password Change -->
            <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Change Password</h2>
                <p class="text-sm text-gray-600 mb-6">Leave blank to keep current password</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                            Current Password
                        </label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('current_password') border-red-300 @enderror"
                        />
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            New Password
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('password') border-red-300 @enderror"
                        />
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm New Password
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        />
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('profile.show') }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Update Profile
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Update the current picture display
            const currentPicture = document.querySelector('#current-profile-picture');
            if (currentPicture) {
                currentPicture.src = e.target.result;
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

