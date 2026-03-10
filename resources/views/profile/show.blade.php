@extends('layouts.app')

@section('title', 'Profile - My Gig Guide')
@section('description', 'View and manage your profile information.')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Profile</h1>
                    <p class="text-gray-600 mt-2">Manage your account information and preferences</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Profile
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Information -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Account Information</h2>
                    
                    <div class="space-y-6">
                        <!-- Basic Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                <p class="text-gray-900">{{ $user->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                <p class="text-gray-900">{{ $user->username }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <p class="text-gray-900">{{ $user->email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account Type</label>
                                <div class="flex flex-wrap gap-2">
                                    @php($roles = $user->roles ?? collect())
                                    @forelse($roles as $role)
                                        <span class="inline-block px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 rounded-full">
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-gray-500">General user</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Role-specific Information -->
                        @if($profile)
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                    @if($user->hasRole('artist'))
                                        Artist Profile
                                    @elseif($user->hasRole('organiser'))
                                        Organization Profile
                                    @endif
                                </h3>
                                
                                @if($user->hasRole('artist'))
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Stage Name</label>
                                            <p class="text-gray-900">{{ $profile->stage_name ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Real Name</label>
                                            <p class="text-gray-900">{{ $profile->real_name ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Genre</label>
                                            <p class="text-gray-900">{{ $profile->genre ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                            <p class="text-gray-900">{{ $profile->contact_phone ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                            <p class="text-gray-900">{{ $profile->contact_email ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                            <p class="text-gray-900">
                                                @if($profile->website)
                                                    <a href="{{ $profile->website }}" target="_blank" class="text-purple-600 hover:text-purple-700">
                                                        {{ $profile->website }}
                                                    </a>
                                                @else
                                                    Not set
                                                @endif
                                            </p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                                            <p class="text-gray-900">{{ $profile->bio ?? 'No bio available' }}</p>
                                        </div>
                                    </div>
                                @elseif($user->hasRole('organiser'))
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Organization Name</label>
                                            <p class="text-gray-900">{{ $profile->organisation_name ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Phone</label>
                                            <p class="text-gray-900">{{ $profile->contact_phone ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Email</label>
                                            <p class="text-gray-900">{{ $profile->contact_email ?? 'Not set' }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                                            <p class="text-gray-900">
                                                @if($profile->website)
                                                    <a href="{{ $profile->website }}" target="_blank" class="text-purple-600 hover:text-purple-700">
                                                        {{ $profile->website }}
                                                    </a>
                                                @else
                                                    Not set
                                                @endif
                                            </p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                            <p class="text-gray-900">{{ $profile->description ?? 'No description available' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Profile Picture -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Picture</h3>
                    <div class="flex items-center space-x-4">
                        @php($hasProfileImage = $user->profile_picture && !str_contains($user->profile_picture, '/tmp/php') && !str_contains($user->profile_picture, 'tmp.php'))

                        @if($hasProfileImage)
                            <img src="{{ Storage::url($user->profile_picture) }}" alt="Profile Picture" class="h-20 w-20 rounded-full object-cover border-4 border-purple-200">
                        @else
                            <div class="h-20 w-20 rounded-full bg-purple-100 flex items-center justify-center">
                                <span class="text-purple-600 text-2xl font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </span>
                            </div>
                        @endif

                        <div>
                            <p class="text-sm text-gray-600">Profile picture</p>
                            <a href="{{ route('profile.edit') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                {{ $hasProfileImage ? 'Change photo' : 'Upload photo' }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Account Stats -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Stats</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Member since</span>
                            <span class="font-medium">{{ $user->created_at->format('M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Last updated</span>
                            <span class="font-medium">{{ $user->updated_at->diffForHumans() }}</span>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Email verified</span>
                                @if($user->email_verified_at)
                                    <span class="text-green-600 font-medium">Yes</span>
                                @else
                                    <span class="text-red-600 font-medium">No</span>
                                @endif
                            </div>
                            @if(!$user->email_verified_at)
                                <a href="{{ route('verification.notice') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                                    Verify Email
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-purple-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('profile.edit') }}" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Profile
                        </a>
                        <a href="{{ route('dashboard') }}" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Go to Dashboard
                        </a>
                        @if($user->hasRole('artist'))
                            <a href="{{ route('events.create') }}" class="flex items-center w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                List an Event
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="bg-white rounded-2xl shadow-sm border border-red-100 p-6">
                    <h3 class="text-lg font-semibold text-red-900 mb-4">Danger Zone</h3>
                    <p class="text-sm text-gray-600 mb-4">Once you delete your account, there is no going back. All your data including events, venues, and images will be permanently deleted.</p>
                    <button onclick="openDeleteModal()" class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div id="deleteModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <!-- Backdrop -->
        <div id="modalBackdrop" class="fixed inset-0 bg-black/50 transition-opacity"></div>
        
        <!-- Modal Content -->
        <div class="relative transform overflow-hidden rounded-lg bg-white shadow-xl transition-all w-full max-w-md">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Delete Account</h3>
                <p class="text-sm text-gray-500 mb-6 text-center">
                    This action cannot be undone. This will permanently delete your account, all your events, venues, and all associated data.
                </p>
                <form id="deleteAccountForm" action="{{ route('profile.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Enter your password to confirm</label>
                        <input type="password" name="password" id="password" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Your password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('error')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeDeleteModal()" 
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                            Delete Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('password').focus();
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.getElementById('deleteAccountForm').reset();
}

// Close modal when clicking on backdrop
document.getElementById('modalBackdrop').addEventListener('click', function() {
    closeDeleteModal();
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('deleteModal').classList.contains('hidden')) {
        closeDeleteModal();
    }
});
</script>
@endsection