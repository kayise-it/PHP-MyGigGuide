<div id="ajax-results">
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="w-12 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input type="checkbox" id="select-all" class="form-checkbox h-4 w-4 text-purple-600 rounded border-gray-300">
                </th>
                @if($type === 'all')
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                @endif
                <th class="w-16 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
            @forelse($unclaimed as $item)
                @php
                    // Handle both unified (all) and type-specific queries
                    if ($type === 'all') {
                        $itemType = $item->type;
                        $itemId = $item->id;
                        $itemName = $item->name;
                        $itemEmail = $item->email;
                        $itemCreatedAt = $item->created_at;
                        $hasPendingClaim = $item->has_pending_claim;
                        $hasDispute = $item->has_dispute;
                        $typeColor = $item->type_color;
                        $itemImage = $item->image;
                        $model = $item->model;
                    } else {
                        $itemType = $type;
                        $itemId = $item->id;
                        $itemName = $item->getDisplayName();
                        $itemEmail = $item->getClaimEmail();
                        $itemCreatedAt = $item->created_at;
                        $hasPendingClaim = $item->hasPendingClaim();
                        $hasDispute = $item->hasDisputedClaim();
                        $typeColor = $item->getTypeColorClass();
                        $itemImage = match($type) {
                            'artist' => $item->profile_picture,
                            'venue' => $item->main_picture,
                            'event' => $item->poster,
                            'organiser' => $item->logo,
                            default => null
                        };
                        $model = $item;
                    }
                @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <!-- Checkbox -->
                    <td class="w-12 px-4 py-3">
                        <input type="checkbox" name="items[]" value="{{ $itemType }}-{{ $itemId }}" class="item-checkbox form-checkbox h-4 w-4 text-purple-600 rounded border-gray-300">
                    </td>

                    <!-- Type Badge (only for 'all' view) -->
                    @if($type === 'all')
                    <td class="px-4 py-3">
                        @include('admin.unclaimed._type-badge', ['itemType' => $itemType])
                    </td>
                    @endif

                    <!-- Image -->
                    <td class="w-16 px-4 py-3">
                        @if($itemImage)
                            <img src="{{ Storage::url($itemImage) }}" alt="{{ $itemName }}" class="w-10 h-10 rounded-lg object-cover">
                        @else
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                @include('admin.unclaimed._type-icon', ['itemType' => $itemType, 'class' => 'h-5 w-5 text-gray-400'])
                            </div>
                        @endif
                    </td>

                    <!-- Name -->
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">{{ $itemName }}</div>
                        @if($itemType === 'artist' && isset($model->genre))
                            <div class="text-sm text-gray-500">{{ $model->genre }}</div>
                        @elseif($itemType === 'venue' && isset($model->city))
                            <div class="text-sm text-gray-500">{{ $model->city }}</div>
                        @elseif($itemType === 'event' && isset($model->date))
                            <div class="text-sm text-gray-500">{{ $model->date?->format('M d, Y') }}</div>
                        @endif
                    </td>

                    <!-- Email -->
                    <td class="px-4 py-3">
                        @if($itemEmail)
                            <span class="text-gray-600">{{ $itemEmail }}</span>
                        @else
                            <span class="text-gray-400 italic">No email</span>
                        @endif
                    </td>

                    <!-- Status -->
                    <td class="px-4 py-3">
                        @if($hasDispute)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Disputed
                            </span>
                        @elseif($hasPendingClaim)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Pending Claim
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                Unclaimed
                            </span>
                        @endif
                    </td>

                    <!-- Created -->
                    <td class="px-4 py-3 text-sm text-gray-500">
                        {{ $itemCreatedAt?->diffForHumans() }}
                    </td>

                    <!-- Actions -->
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.unclaimed.edit', ['type' => $itemType, 'id' => $itemId]) }}" 
                               class="p-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            @if($itemEmail)
                            <form action="{{ route('admin.unclaimed.send-invite', ['type' => $itemType, 'id' => $itemId]) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Send Claim Invite">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </form>
                            @endif

                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                                <div x-show="open" 
                                     @click.outside="open = false"
                                     x-transition
                                     class="absolute right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 py-1.5 z-50">
                                    <a href="{{ route('admin.unclaimed.edit', ['type' => $itemType, 'id' => $itemId]) }}" 
                                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 whitespace-nowrap">
                                        <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    <button type="button"
                                            onclick="openLinkModal('{{ $itemType }}', {{ $itemId }}, '{{ addslashes($itemName) }}')"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 whitespace-nowrap">
                                        <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        Link to User
                                    </button>
                                    <div class="border-t border-gray-100 my-1.5"></div>
                                    <form action="{{ route('admin.unclaimed.destroy', ['type' => $itemType, 'id' => $itemId]) }}" 
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this {{ $itemType }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 whitespace-nowrap">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $type === 'all' ? 8 : 7 }}" class="px-4 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="p-4 bg-gray-100 rounded-full mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 font-medium">No unclaimed {{ $type === 'all' ? 'items' : Str::plural($type) }} found</p>
                            <p class="text-gray-400 text-sm mt-1">All items have been claimed or matched with users</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($unclaimed->hasPages())
<div class="mt-4">
    {{ $unclaimed->links() }}
</div>
@endif
</div>

<!-- Link to User Modal -->
<div id="link-modal" 
     x-data="linkModalData()"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black/50 z-[9998] flex items-center justify-center"
     @keydown.escape.window="closeModal()"
     @user-selected.window="onUserSelected($event.detail)"
     @open-link-modal.window="openModal($event.detail.type, $event.detail.id, $event.detail.name)"
     style="display: none;">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto relative" style="z-index: 9999;" @click.outside="closeModal()">
        <!-- Main Link Form -->
        <template x-if="!showConflict">
            <div>
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Link to User</h3>
                    <p class="text-sm text-gray-500 mt-1">Link <span x-text="itemName" class="font-medium text-gray-900"></span> to a registered user</p>
                </div>
                <form @submit.prevent="submitLink()" class="p-6 overflow-visible relative">
                    <div class="mb-6 overflow-visible relative">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                        <x-user-selector 
                            name="user_id" 
                            placeholder="Search and select a user..."
                            :required="true"
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
                    <div x-show="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm" x-text="errorMessage"></div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="loading || checkingConflict" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50">
                            <span x-show="!loading">Link User</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </template>

        <!-- Conflict Confirmation -->
        <template x-if="showConflict">
            <div>
                <div class="px-6 py-4 border-b border-gray-200 bg-amber-50">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-full">
                            <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">User Already Has Profile</h3>
                            <p class="text-sm text-amber-700">This action will replace an existing profile</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600 mb-3">
                            <span class="font-medium text-gray-900" x-text="conflictData.user_name || 'This user'"></span> already has an existing 
                            <span class="font-medium" x-text="conflictData.type || 'profile'"></span> profile:
                        </p>
                        <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-gray-200">
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
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-6">
                        <p class="text-sm text-amber-800">
                            <strong>Warning:</strong> Proceeding will unlink the existing 
                            <span class="font-medium" x-text="conflictData.type"></span> profile 
                            (<span class="font-medium" x-text="conflictData.existing_name"></span>) 
                            and link <span class="font-medium" x-text="itemName"></span> instead. 
                            <span class="font-semibold">The existing profile will become unclaimed</span> 
                            (its user_id will be set to null in the database).
                        </p>
                        <p class="text-xs text-amber-700 mt-2">
                            Note: Users can have multiple profile types (e.g., be both an artist and venue owner), 
                            but can only have one profile of each type. This action replaces the existing 
                            <span x-text="conflictData.type"></span> profile.
                        </p>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="cancelConflict()" class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="button" @click="forceLink()" :disabled="loading" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 replace-link-button">
                            <span x-show="!loading">Replace & Link</span>
                            <span x-show="loading" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
    // Trigger checkbox reinitialization after table loads (for AJAX updates)
    // The actual functions are defined in the index page
    if (typeof window.initCheckboxFunctionality === 'function') {
        // Use setTimeout to ensure DOM is fully updated
        setTimeout(() => {
            window.initCheckboxFunctionality();
        }, 100);
    }

    // Link modal Alpine.js data - make it globally available
    window.linkModalData = function linkModalData() {
        return {
            show: false,
            showConflict: false,
            loading: false,
            checkingConflict: false,
            errorMessage: '',
            itemName: '',
            itemType: '',
            itemId: null,
            conflictData: {},
            selectedUserId: null,
            selectedUserName: '',
            
            init() {
                // Listen for window events as fallback
                window.addEventListener('open-link-modal', (e) => {
                    if (e.detail && e.detail.type && e.detail.id && e.detail.name) {
                        this.openModal(e.detail.type, e.detail.id, e.detail.name);
                    }
                });
            },
            
            openModal(type, id, name) {
                this.itemType = type;
                this.itemId = id;
                this.itemName = name;
                this.showConflict = false;
                this.errorMessage = '';
                this.conflictData = {};
                this.selectedUserId = null;
                this.selectedUserName = '';
                this.show = true;
            },
            
            closeModal() {
                this.show = false;
                this.showConflict = false;
                this.loading = false;
                this.checkingConflict = false;
                this.errorMessage = '';
            },
            
            // Called when user-selector dispatches user-selected event
            async onUserSelected(detail) {
                if (!this.show || !detail || !detail.user) return;
                
                const user = detail.user;
                this.selectedUserId = user.id;
                this.selectedUserName = user.name;
                
                // Only check conflict for artist/organiser (hasOne relationships)
                if (!['artist', 'organiser'].includes(this.itemType)) {
                    return;
                }
                
                this.checkingConflict = true;
                this.errorMessage = '';
                
                try {
                    const response = await fetch(`/admin/unclaimed/${this.itemType}/${this.itemId}/check-link-conflict`, {
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
                    user_name: user.name || user.email || 'Unknown User'
                };
                        this.showConflict = true;
                    }
                } catch (error) {
                    console.error('Error checking conflict:', error);
                } finally {
                    this.checkingConflict = false;
                }
            },
            
            async submitLink() {
                const userId = document.querySelector('#link-modal input[name="user_id"]').value;
                if (!userId) {
                    this.errorMessage = 'Please select a user first';
                    return;
                }
                
                this.selectedUserId = userId;
                this.loading = true;
                this.errorMessage = '';
                
                try {
                    const response = await fetch(`/admin/unclaimed/${this.itemType}/${this.itemId}/link-user`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ user_id: userId })
                    });
                    
                    let data;
                    try {
                        data = await response.json();
                    } catch (e) {
                        // If response is not JSON, get text
                        const text = await response.text();
                        throw new Error(text || `Server error: ${response.status} ${response.statusText}`);
                    }
                    
                    if (response.status === 409 && data.conflict) {
                        // Show conflict confirmation
                        this.conflictData = data.data || data;
                        this.showConflict = true;
                    } else if (response.ok) {
                        // Success - reload page
                        window.location.reload();
                    } else {
                        // Show detailed error message
                        this.errorMessage = data.message || data.error || `Server error: ${response.status} ${response.statusText}`;
                        console.error('Link error:', data);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.errorMessage = error.message || 'Failed to process request. Please check the console for details.';
                } finally {
                    this.loading = false;
                }
            },
            
            cancelConflict() {
                this.showConflict = false;
                this.conflictData = {};
            },
            
            async forceLink() {
                this.loading = true;
                
                try {
                    const response = await fetch(`/admin/unclaimed/${this.itemType}/${this.itemId}/link-user`, {
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
                        window.location.reload();
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

    // Global function to open modal (called from table rows) - simplified and more reliable
    window.openLinkModal = function openLinkModal(type, id, name) {
        const modal = document.getElementById('link-modal');
        if (!modal) {
            console.error('Link modal not found');
            return;
        }
        
        // Try direct access first (if already initialized)
        if (modal.__x && modal.__x.$data && typeof modal.__x.$data.openModal === 'function') {
            modal.__x.$data.openModal(type, id, name);
            return;
        }
        
        // If Alpine is available, ensure initialization and use event system
        if (window.Alpine) {
            // Force initialization if not done
            if (!modal.__x) {
                try {
                    window.Alpine.initTree(modal);
                } catch (e) {
                    console.error('Error initializing Alpine tree:', e);
                }
            }
            
            // Wait a moment for initialization, then try again
            setTimeout(() => {
                if (modal.__x && modal.__x.$data && typeof modal.__x.$data.openModal === 'function') {
                    modal.__x.$data.openModal(type, id, name);
                } else {
                    // Use window event as fallback
                    window.dispatchEvent(new CustomEvent('open-link-modal', {
                        detail: { type, id, name }
                    }));
                }
            }, 50);
        } else {
            console.error('Alpine.js not loaded');
            alert('Please refresh the page. Alpine.js is not loaded.');
        }
    }

    function closeLinkModal() {
        const modal = document.getElementById('link-modal');
        if (modal && modal.__x) {
            modal.__x.$data.closeModal();
        }
    }
    
    // Ensure modal is initialized when DOM is ready and Alpine is loaded
    (function initModal() {
        function ensureModalInit() {
            const modal = document.getElementById('link-modal');
            if (modal && window.Alpine) {
                if (!modal.__x) {
                    window.Alpine.initTree(modal);
                }
            }
        }
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', ensureModalInit);
        } else {
            ensureModalInit();
        }
        
        // Also try after Alpine loads (if it loads after DOM)
        if (window.Alpine) {
            window.Alpine.nextTick(ensureModalInit);
        } else {
            // Wait for Alpine to load
            const checkAlpine = setInterval(() => {
                if (window.Alpine) {
                    clearInterval(checkAlpine);
                    ensureModalInit();
                }
            }, 100);
            
            // Stop checking after 5 seconds
            setTimeout(() => clearInterval(checkAlpine), 5000);
        }
        
        // Final attempt after a delay
        setTimeout(ensureModalInit, 1000);
    })();
</script>

