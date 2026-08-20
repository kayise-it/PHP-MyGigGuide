@extends('layouts.admin')

@section('title', 'Edit Unclaimed Event - Admin Panel')
@section('page-title', 'Edit Unclaimed Event')
@section('description', 'Edit unclaimed event information and details.')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('admin.unclaimed.index') }}" class="hover:text-purple-600">Unclaimed</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <a href="{{ route('admin.unclaimed.index', ['type' => 'event']) }}" class="hover:text-purple-600">Events</a>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-gray-900 font-medium">{{ $entity->name }}</span>
        </nav>
    </div>

    <!-- Header with Type Badge -->
    <div class="flex items-center gap-4 mb-6">
        @include('admin.unclaimed._type-badge', ['itemType' => 'event'])
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $entity->name }}</h1>
            <p class="text-gray-500">Update event information and details</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.unclaimed.update', ['type' => 'event', 'id' => $entity->id]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Event Name -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Event Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $entity->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <input type="date" id="date" name="date" value="{{ old('date', $entity->date?->format('Y-m-d')) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Time -->
                <div>
                    <label for="time" class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                    <input type="time" id="time" name="time" value="{{ old('time', $entity->time?->format('H:i')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('time') border-red-500 @enderror">
                    @error('time')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Venue -->
                <div>
                    <label for="venue_id" class="block text-sm font-medium text-gray-700 mb-2">Venue</label>
                    <select id="venue_id" name="venue_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('venue_id') border-red-500 @enderror">
                        <option value="">Select a venue...</option>
                        @foreach(\App\Models\Venue::orderBy('name')->get() as $venue)
                            <option value="{{ $venue->id }}" {{ old('venue_id', $entity->venue_id) == $venue->id ? 'selected' : '' }}>
                                {{ $venue->name }} ({{ $venue->city }})
                            </option>
                        @endforeach
                    </select>
                    @error('venue_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                    <input type="text" id="price" name="price" value="{{ old('price', $entity->price) }}" placeholder="e.g. R150 or Free"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('price') border-red-500 @enderror">
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ticket URL -->
                <div class="md:col-span-2">
                    <label for="ticket_url" class="block text-sm font-medium text-gray-700 mb-2">Ticket URL</label>
                    <input type="url" id="ticket_url" name="ticket_url" value="{{ old('ticket_url', $entity->ticket_url) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('ticket_url') border-red-500 @enderror">
                    @error('ticket_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- TikTok -->
                <div class="md:col-span-2">
                    <label for="tiktok" class="block text-sm font-medium text-gray-700 mb-2">TikTok</label>
                    <input type="url" id="tiktok" name="tiktok" value="{{ old('tiktok', $entity->tiktok) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('tiktok') border-red-500 @enderror"
                           placeholder="https://www.tiktok.com/@event">
                    @error('tiktok')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description', $entity->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Poster -->
                @if($entity->poster)
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Event Poster</label>
                    <div class="flex items-center space-x-4">
                        <img src="{{ Storage::url($entity->poster) }}" alt="Current Poster" class="h-32 w-auto object-cover rounded-lg">
                        <div>
                            <p class="text-sm text-gray-600">Current event poster</p>
                            <p class="text-xs text-gray-500">Upload a new image below to replace it</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Poster Upload -->
                <div class="md:col-span-2">
                    <label for="poster" class="block text-sm font-medium text-gray-700 mb-2">Event Poster</label>
                    <input type="file" id="poster" name="poster" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('poster') border-red-500 @enderror">
                    @error('poster')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Leave empty to keep current poster</p>
                </div>
            </div>

            <!-- Link to User Section -->
            <div class="mt-8 pt-6 border-t border-gray-200" x-data="linkUserSection()">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Link to User/Organiser</h3>
                <p class="text-sm text-gray-500 mb-4">Manually link this event to a registered user or organiser.</p>
                
                <div class="flex items-end gap-4">
                    <div class="flex-1 max-w-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <x-user-selector 
                            name="link_user_id" 
                            placeholder="Search and select a user..."
                        />
                    </div>
                    <button type="button" @click="linkToUser()" :disabled="loading" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50">
                        <span x-show="!loading">Link User</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                </div>
                
                <!-- Error Message -->
                <div x-show="errorMessage" x-cloak class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="errorMessage"></div>
            </div>

            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.unclaimed.index', ['type' => 'event']) }}" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Update Event
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
            
            if (!confirm('Are you sure you want to link this event to the selected user?')) {
                return;
            }
            
            this.loading = true;
            this.errorMessage = '';
            
            try {
                const response = await fetch('{{ route("admin.unclaimed.link-user", ["type" => "event", "id" => $entity->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId })
                });
                
                if (response.ok) {
                    window.location.href = '{{ route("admin.unclaimed.index", ["type" => "event"]) }}';
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

