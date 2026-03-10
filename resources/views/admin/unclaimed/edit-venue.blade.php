@extends('layouts.admin')

@section('title', 'Edit Unclaimed Venue - Admin Panel')
@section('page-title', 'Edit Unclaimed Venue')
@section('description', 'Edit unclaimed venue information and details.')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.unclaimed.index') }}" class="hover:text-purple-600">Unclaimed</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <a href="{{ route('admin.unclaimed.index', ['type' => 'venue']) }}" class="hover:text-purple-600">Venues</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-gray-900 font-medium">{{ $entity->name }}</span>
        </nav>
    </div>

    <!-- Header with Type Badge -->
    <div class="flex items-center gap-4 mb-6">
        @include('admin.unclaimed._type-badge', ['itemType' => 'venue'])
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $entity->name }}</h1>
            <p class="text-gray-500">Update venue information and details</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.unclaimed.update', ['type' => 'venue', 'id' => $entity->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Venue Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Venue Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $entity->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                    <input type="text" id="city" name="city" value="{{ old('city', $entity->city) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('city') border-red-500 @enderror">
                    @error('city')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contact Email -->
                <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">Contact Email *</label>
                    <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email', $entity->contact_email) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('contact_email') border-red-500 @enderror">
                    @error('contact_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Used for claim matching</p>
                </div>

                <!-- Phone Number -->
                <div>
                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $entity->phone_number) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone_number') border-red-500 @enderror">
                    @error('phone_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <input type="text" id="address" name="address" value="{{ old('address', $entity->address) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('address') border-red-500 @enderror">
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">Capacity</label>
                    <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $entity->capacity) }}" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('capacity') border-red-500 @enderror">
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                    <input type="url" id="website" name="website" value="{{ old('website', $entity->website) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('website') border-red-500 @enderror">
                    @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $entity->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Image -->
                @if($entity->main_picture)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Main Picture</label>
                    <div class="flex items-center space-x-4">
                        <img src="{{ Storage::url($entity->main_picture) }}" alt="Current Picture" class="h-24 w-32 object-cover rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Current venue picture</p>
                            <p class="text-xs text-gray-500">Upload a new image below to replace it</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Main Picture Upload -->
                <div class="md:col-span-2">
                    <label for="main_picture" class="block text-sm font-medium text-gray-700 mb-2">Main Picture</label>
                    <input type="file" id="main_picture" name="main_picture" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('main_picture') border-red-500 @enderror">
                    @error('main_picture')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Leave empty to keep current picture</p>
                </div>
            </div>

            <!-- Link to User Section -->
            <div class="mt-8 pt-6 border-t border-gray-200" x-data="linkUserSection()">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Link to User</h3>
                <p class="text-sm text-gray-500 mb-4">Manually link this venue to a registered user instead of waiting for them to claim it.</p>
                
                <div class="flex items-end gap-4">
                    <div class="flex-1 max-w-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <x-user-selector 
                            name="link_user_id" 
                            placeholder="Search and select a user..."
                        />
                    </div>
                    <button type="button" @click="linkToUser()" :disabled="loading" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50">
                        <span x-show="!loading">Link User</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                </div>
                
                <!-- Error Message -->
                <div x-show="errorMessage" x-cloak class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="errorMessage"></div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.unclaimed.index', ['type' => 'venue']) }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Update Venue
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function linkUserSection() {
    return {
        loading: false,
        errorMessage: '',
        
        async linkToUser() {
            const userId = document.querySelector('input[name="link_user_id"]').value;
            if (!userId) {
                this.errorMessage = 'Please select a user first';
                return;
            }
            
            if (!confirm('Are you sure you want to link this venue to the selected user?')) {
                return;
            }
            
            this.loading = true;
            this.errorMessage = '';
            
            try {
                const response = await fetch('{{ route("admin.unclaimed.link-user", ["type" => "venue", "id" => $entity->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId })
                });
                
                if (response.ok) {
                    window.location.href = '{{ route("admin.unclaimed.index", ["type" => "venue"]) }}';
                } else {
                    const data = await response.json();
                    this.errorMessage = data.message || 'An error occurred';
                }
            } catch (error) {
                console.error('Error:', error);
                this.errorMessage = 'Failed to process request';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endsection

