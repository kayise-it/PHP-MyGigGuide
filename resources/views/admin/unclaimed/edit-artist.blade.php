@extends('layouts.admin')

@section('title', 'Edit Unclaimed Artist - Admin Panel')
@section('page-title', 'Edit Unclaimed Artist')
@section('description', 'Edit unclaimed artist information and details.')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.unclaimed.index') }}" class="hover:text-purple-600">Unclaimed</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <a href="{{ route('admin.unclaimed.index', ['type' => 'artist']) }}" class="hover:text-purple-600">Artists</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-gray-900 font-medium">{{ $entity->stage_name }}</span>
        </nav>
    </div>

    <!-- Header with Type Badge -->
    <div class="flex items-center gap-4 mb-6">
        @include('admin.unclaimed._type-badge', ['itemType' => 'artist'])
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $entity->stage_name }}</h1>
            <p class="text-gray-500">Update artist information and details</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @include('admin.unclaimed._pending-claim-review', ['entity' => $entity, 'type' => 'artist'])

        <form method="POST" action="{{ route('admin.unclaimed.update', ['type' => 'artist', 'id' => $entity->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Stage Name -->
                <div>
                    <label for="stage_name" class="block text-sm font-medium text-gray-700 mb-2">Stage Name *</label>
                    <input type="text" id="stage_name" name="stage_name" value="{{ old('stage_name', $entity->stage_name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('stage_name') border-red-500 @enderror">
                    @error('stage_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Real Name -->
                <div>
                    <label for="real_name" class="block text-sm font-medium text-gray-700 mb-2">Real Name</label>
                    <input type="text" id="real_name" name="real_name" value="{{ old('real_name', $entity->real_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('real_name') border-red-500 @enderror">
                    @error('real_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Email -->
                <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Contact Email *</label>
                    <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $entity->contact_email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('contact_email') border-red-500 @enderror">
                    @error('contact_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Used for claim matching</p>
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $entity->phone_number) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('phone_number') border-red-500 @enderror">
                    @error('phone_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Genre -->
                <div>
                    <label for="genre" class="block text-sm font-medium text-gray-700 mb-2">Genre *</label>
                    <x-genre-select 
                        id="genre" 
                        name="genre" 
                        :value="old('genre', $entity->genre)" 
                        required 
                        use-names
                        class="w-full @error('genre') border-red-500 @enderror" 
                    />
                    @error('genre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                    <input type="url" id="website" name="website" value="{{ old('website', $entity->website) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('website') border-red-500 @enderror">
                    @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bio -->
                <div class="md:col-span-2">
                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                    <textarea id="bio" name="bio" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('bio') border-red-500 @enderror">{{ old('bio', $entity->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Social Links -->
                <div>
                    <label for="instagram" class="block text-sm font-medium text-gray-700 mb-2">Instagram</label>
                    <input type="url" id="instagram" name="instagram" value="{{ old('instagram', $entity->instagram) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('instagram') border-red-500 @enderror">
                    @error('instagram')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="facebook" class="block text-sm font-medium text-gray-700 mb-2">Facebook</label>
                    <input type="url" id="facebook" name="facebook" value="{{ old('facebook', $entity->facebook) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('facebook') border-red-500 @enderror">
                    @error('facebook')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="twitter" class="block text-sm font-medium text-gray-700 mb-2">Twitter</label>
                    <input type="url" id="twitter" name="twitter" value="{{ old('twitter', $entity->twitter) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('twitter') border-red-500 @enderror">
                    @error('twitter')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Profile Picture -->
                @if($entity->profile_picture)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Profile Picture</label>
                    <div class="flex items-center space-x-4">
                        <img src="{{ Storage::url($entity->profile_picture) }}" alt="Current Profile Picture" class="h-24 w-24 object-cover rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Current profile picture</p>
                            <p class="text-xs text-gray-500">Upload a new image below to replace it</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Profile Picture Upload -->
                <div class="md:col-span-2">
                    <label for="profile_picture" class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('profile_picture') border-red-500 @enderror">
                    @error('profile_picture')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Leave empty to keep current picture</p>
                </div>
            </div>

            <!-- Link to User Section -->
            <div class="mt-8 pt-6 border-t border-gray-200" x-data="linkUserSection()" @user-selected.window="onUserSelected($event.detail)">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Link to User</h3>
                <p class="text-sm text-gray-500 mb-4">Manually link this artist to a registered user instead of waiting for them to claim it.</p>
                
                <div class="flex items-end gap-4">
                    <div class="flex-1 max-w-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <x-user-selector 
                            name="link_user_id" 
                            placeholder="Search and select a user..."
                        />
                        <!-- Checking indicator -->
                        <div x-show="checkingConflict" class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Checking for conflicts...
                        </div>
                    </div>
                    <button type="button" @click.prevent.stop="linkToUser($event)" :disabled="loading || checkingConflict" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <span x-show="!loading">Link User</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                </div>
                
                <!-- Error Message -->
                <div x-show="errorMessage" x-cloak class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="errorMessage"></div>

                <!-- Conflict Confirmation Modal -->
                <div x-show="showConflict" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @keydown.escape.window="showConflict = false">
                    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden" @click.outside="showConflict = false">
                        <div class="px-6 py-4 border-b border-gray-200 bg-amber-50">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-amber-100 rounded-full">
                                    <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">User Already Has Profile</h3>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-sm text-gray-600 mb-3">
                                <span class="font-medium text-gray-900" x-text="conflictData.user_name"></span> already has an existing artist profile:
                            </p>
                            <div class="flex items-center gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200 mb-4">
                                <div class="p-2 bg-purple-100 rounded-lg">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900" x-text="conflictData.existing_name"></p>
                                    <p class="text-xs text-gray-500">ID: <span x-text="conflictData.existing_id"></span></p>
                                </div>
                            </div>
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-6">
                                <p class="text-sm text-amber-800">
                                    <strong>Warning:</strong> Proceeding will unlink the existing profile and link <strong>{{ $entity->stage_name }}</strong> instead. The old profile will become unclaimed.
                                </p>
                            </div>
                            <div class="flex justify-end gap-3">
                                <button type="button" @click="showConflict = false" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                    Cancel
                                </button>
                                <button type="button" @click="forceLink()" :disabled="loading" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    <span x-show="!loading">Replace & Link</span>
                                    <span x-show="loading">Processing...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.unclaimed.index', ['type' => 'artist']) }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    Update Artist
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function linkUserSection() {
    return {
        loading: false,
        checkingConflict: false,
        errorMessage: '',
        showConflict: false,
        conflictData: {},
        selectedUserId: null,
        
        // Called when user-selector dispatches user-selected event
        async onUserSelected(detail) {
            if (!detail || !detail.user) return;
            
            const user = detail.user;
            this.selectedUserId = user.id;
            
            
            this.checkingConflict = true;
            this.errorMessage = '';
            this.showConflict = false;
            
            try {
                const response = await fetch('{{ route("admin.unclaimed.check-link-conflict", ["type" => "artist", "id" => $entity->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ user_id: user.id })
                });
                
                const data = await response.json();
                
                
                if (data.has_conflict && data.conflict) {
                    this.conflictData = {
                        ...data.conflict,
                        user_name: user.name
                    };
                    this.showConflict = true;
                }
            } catch (error) {
                console.error('Error checking conflict:', error);
            } finally {
                this.checkingConflict = false;
            }
        },
        
        async linkToUser(event) {
            // Prevent any form submission as additional safeguard
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Use the reactive selectedUserId from Alpine.js state, fallback to DOM query
            const userId = this.selectedUserId || document.querySelector('input[name="link_user_id"]')?.value;
            
            if (!userId) {
                this.errorMessage = 'Please select a user first';
                return false;
            }
            
            
            this.selectedUserId = userId;
            this.loading = true;
            this.errorMessage = '';
            
            try {
                // Get CSRF token with fallback
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                
                
                // Log request details before sending
                const requestUrl = '{{ route("admin.unclaimed.link-user", ["type" => "artist", "id" => $entity->id]) }}';
                const requestBody = { user_id: userId };
                
                
                const response = await fetch(requestUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(requestBody)
                });
                
                // Log response details before parsing
                
                // Check if response is actually JSON before parsing
                let data;
                const contentType = response.headers.get('content-type');
                const isJson = contentType && contentType.includes('application/json');
                
                if (isJson) {
                    try {
                        data = await response.json();
                    } catch (parseError) {
                        // Log JSON parse error
                        
                        const text = await response.text();
                        this.errorMessage = `Server error (${response.status}): ${text.substring(0, 200)}`;
                        return;
                    }
                } else {
                    // Non-JSON response (likely HTML error page)
                    const text = await response.text();
                    
                    // Log non-JSON response
                    
                    // Try to extract error message from HTML if possible
                    const errorMatch = text.match(/<title[^>]*>([^<]+)<\/title>/i) || text.match(/<h1[^>]*>([^<]+)<\/h1>/i);
                    const errorTitle = errorMatch ? errorMatch[1] : 'Server Error';
                    
                    this.errorMessage = `Server returned ${response.status} ${response.statusText}: ${errorTitle}`;
                    return;
                }
                
                
                if (response.status === 409 && data.conflict) {
                    this.conflictData = data.data;
                    this.showConflict = true;
                } else if (response.ok) {
                    // Validate success response structure
                    if (!data || (data.success === undefined && !data.conflict)) {
                        // Log unexpected success response structure
                    }
                    
                    // Log successful link
                    
                    window.location.href = '{{ route("admin.unclaimed.index", ["type" => "artist"]) }}';
                } else {
                    this.errorMessage = data.message || data.error || 'An error occurred';
                }
            } catch (error) {
                console.error('Error:', error);
                
                // Determine error type
                const errorType = error.name || 'Unknown';
                const isNetworkError = errorType === 'TypeError' && error.message.includes('fetch');
                const isTimeoutError = error.message.includes('timeout');
                
                // Log detailed error information
                
                // Provide user-friendly error messages
                if (isNetworkError) {
                    this.errorMessage = 'Network error: Unable to connect to server. Please check your internet connection and try again.';
                } else if (isTimeoutError) {
                    this.errorMessage = 'Request timeout: The server took too long to respond. Please try again.';
                } else {
                    this.errorMessage = `Error: ${error.message || 'An unexpected error occurred'}`;
                }
            } finally {
                this.loading = false;
            }
        },
        
        async forceLink() {
            this.loading = true;
            
            try {
                const response = await fetch('{{ route("admin.unclaimed.link-user", ["type" => "artist", "id" => $entity->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ 
                        user_id: this.selectedUserId,
                        force_replace: true
                    })
                });
                
                if (response.ok) {
                    window.location.href = '{{ route("admin.unclaimed.index", ["type" => "artist"]) }}';
                } else {
                    const data = await response.json();
                    this.errorMessage = data.message || 'Failed to link user';
                    this.showConflict = false;
                }
            } catch (error) {
                console.error('Error:', error);
                this.errorMessage = 'Failed to process request';
                this.showConflict = false;
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endsection

