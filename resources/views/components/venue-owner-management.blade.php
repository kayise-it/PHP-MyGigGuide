@props(['venue' => null])

@if(auth()->check() && auth()->user()->hasRole('superuser'))
    <div class="bg-white rounded-lg shadow-sm border-2 border-purple-200 p-6" 
         x-data="venueManagement({{ $venue ? $venue->id : 'null' }})"
         @user-selected="selectedUserId = $event.detail.user.id">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Venue Management</h2>
            <span class="px-2 py-1 text-xs font-semibold text-purple-700 bg-purple-100 rounded">Superuser Only</span>
        </div>

        <!-- Add User Form -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg" style="overflow: visible;">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Add User to Venue</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select User</label>
                    <div class="relative" style="z-index: 10;">
                        <x-user-selector 
                            name="selected_user_id" 
                            placeholder="Search for a user..."
                            :required="true"
                            class="w-full"
                        />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select x-model="newUserRole" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="primary">Owner (Primary)</option>
                        <option value="co_owner">Co-Owner</option>
                        <option value="manager">Manager</option>
                    </select>
                </div>
                <button 
                    type="button"
                    @click="addUser" 
                    :disabled="!selectedUserId || loading" 
                    class="mt-4 w-full bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!loading">Save</span>
                    <span x-show="loading" style="display: none;">Saving...</span>
                </button>
            </div>
        </div>

        <!-- Current Owners -->
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-700">Current Team Members</h3>
            
            @if($venue)
                <!-- Primary Owner -->
                <div>
                    <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Owner (Primary)</h4>
                    <div class="space-y-2">
                        @php
                            $primaryOwners = $venue->owners->filter(function($owner) {
                                return $owner->pivot->role === 'primary';
                            });
                        @endphp
                        @forelse($primaryOwners as $owner)
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-200">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-semibold">{{ substr($owner->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $owner->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $owner->email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <select @change="updateRole({{ $owner->id }}, $event.target.value)" class="text-xs px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-purple-500">
                                        <option value="primary" selected>Owner</option>
                                        <option value="co_owner">Co-Owner</option>
                                        <option value="manager">Manager</option>
                                    </select>
                                    <button @click="removeUser({{ $owner->id }})" class="text-red-600 hover:text-red-700 text-xs font-medium">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">No primary owner assigned</p>
                        @endforelse
                    </div>
                </div>

                <!-- Co-Owners -->
                <div>
                    <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Co-Owners</h4>
                    <div class="space-y-2">
                        @php
                            $coOwners = $venue->owners->filter(function($owner) {
                                return $owner->pivot->role === 'co_owner';
                            });
                        @endphp
                        @forelse($coOwners as $owner)
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-semibold">{{ substr($owner->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $owner->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $owner->email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <select @change="updateRole({{ $owner->id }}, $event.target.value)" class="text-xs px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-purple-500">
                                        <option value="primary">Owner</option>
                                        <option value="co_owner" selected>Co-Owner</option>
                                        <option value="manager">Manager</option>
                                    </select>
                                    <button @click="removeUser({{ $owner->id }})" class="text-red-600 hover:text-red-700 text-xs font-medium">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">No co-owners assigned</p>
                        @endforelse
                    </div>
                </div>

                <!-- Managers -->
                <div>
                    <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Managers</h4>
                    <div class="space-y-2">
                        @php
                            $managers = $venue->owners->filter(function($owner) {
                                return $owner->pivot->role === 'manager';
                            });
                        @endphp
                        @forelse($managers as $owner)
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-center space-x-3">
                                    <div class="h-8 w-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-semibold">{{ substr($owner->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $owner->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $owner->email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <select @change="updateRole({{ $owner->id }}, $event.target.value)" class="text-xs px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-purple-500">
                                        <option value="primary">Owner</option>
                                        <option value="co_owner">Co-Owner</option>
                                        <option value="manager" selected>Manager</option>
                                    </select>
                                    <button @click="removeUser({{ $owner->id }})" class="text-red-600 hover:text-red-700 text-xs font-medium">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 italic">No managers assigned</p>
                        @endforelse
                    </div>
                </div>
            @else
                <!-- For new venues, show empty state -->
                <div class="space-y-4">
                    <div>
                        <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Owner (Primary)</h4>
                        <p class="text-sm text-gray-500 italic">No primary owner assigned</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Co-Owners</h4>
                        <p class="text-sm text-gray-500 italic">No co-owners assigned</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold text-gray-600 uppercase mb-2">Managers</h4>
                        <p class="text-sm text-gray-500 italic">No managers assigned</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
    function venueManagement(venueId) {
        return {
            venueId: venueId,
            newUserRole: 'co_owner',
            selectedUserId: null,
            loading: false,
            
            init() {
                // Listen for user-selected events from the user-selector component
                this.$watch('selectedUserId', (value) => {
                    if (value) {
                        console.log('User selected:', value);
                    }
                });
            },
            
            async addUser() {
                // For new venues, store in hidden fields to be processed after venue creation
                if (!this.venueId) {
                    const hiddenInput = document.querySelector('[name="selected_user_id"]');
                    const userId = this.selectedUserId || (hiddenInput ? hiddenInput.value : null);
                    
                    if (!userId) {
                        alert('Please select a user');
                        return;
                    }
                    
                    // Create hidden input fields for form submission
                    const form = document.querySelector('form[action*="venues"]');
                    if (!form) {
                        alert('Form not found');
                        return;
                    }
                    
                    // Check if user already added
                    const existingInput = form.querySelector(`input[name="venue_owners[${userId}]"]`);
                    if (existingInput) {
                        alert('This user is already added');
                        return;
                    }
                    
                    // Create hidden input for this owner
                    const hiddenOwnerInput = document.createElement('input');
                    hiddenOwnerInput.type = 'hidden';
                    hiddenOwnerInput.name = `venue_owners[${userId}]`;
                    hiddenOwnerInput.value = this.newUserRole;
                    form.appendChild(hiddenOwnerInput);
                    
                    // Add to UI
                    this.addOwnerToUI(userId, this.newUserRole);
                    
                    // Reset form
                    this.selectedUserId = null;
                    if (hiddenInput) hiddenInput.value = '';
                    
                    return;
                }
                
                // Get user ID from hidden input as fallback
                const hiddenInput = document.querySelector('[name="selected_user_id"]');
                const userId = this.selectedUserId || (hiddenInput ? hiddenInput.value : null);
                
                if (!userId) {
                    alert('Please select a user');
                    return;
                }
                
                const formData = new FormData();
                formData.append('user_id', userId);
                formData.append('role', this.newUserRole);
                
                this.loading = true;
                
                try {
                    const response = await fetch(`/admin/venues/${this.venueId}/add-role`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok) {
                        // Reset form
                        this.selectedUserId = null;
                        if (hiddenInput) hiddenInput.value = '';
                        // Reload page to show updated list
                        location.reload();
                    } else {
                        alert(result.message || 'Error adding user');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred');
                } finally {
                    this.loading = false;
                }
            },
            
            addOwnerToUI(userId, role) {
                // Get user name from the user selector if available
                const userSelector = document.querySelector('[name="selected_user_id"]');
                let userName = `User #${userId}`;
                
                // Try to get user name from the selector's display
                if (userSelector) {
                    const selectorContainer = userSelector.closest('.user-selector-container');
                    if (selectorContainer) {
                        const displayElement = selectorContainer.querySelector('.selected-user-name');
                        if (displayElement) {
                            userName = displayElement.textContent.trim();
                        }
                    }
                }
                
                this.displayOwner({ id: userId, name: userName, email: '' }, role);
            },
            
            displayOwner(user, role) {
                // Create a container for pending owners if it doesn't exist
                let pendingContainer = document.getElementById('pending-owners-container');
                if (!pendingContainer) {
                    const currentMembers = document.querySelector('.space-y-4');
                    if (currentMembers) {
                        pendingContainer = document.createElement('div');
                        pendingContainer.id = 'pending-owners-container';
                        pendingContainer.className = 'mb-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200';
                        pendingContainer.innerHTML = '<h4 class="text-xs font-semibold text-yellow-700 uppercase mb-2">Pending Owners (will be added after creation)</h4><div id="pending-owners-list" class="space-y-2"></div>';
                        currentMembers.insertBefore(pendingContainer, currentMembers.firstChild);
                    }
                }
                
                if (pendingContainer) {
                    const list = document.getElementById('pending-owners-list');
                    const ownerDiv = document.createElement('div');
                    ownerDiv.className = 'flex items-center justify-between p-2 bg-white rounded border border-yellow-300';
                    ownerDiv.id = `pending-owner-${user.id}`;
                    ownerDiv.innerHTML = `
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-gray-900">${user.name || `User #${user.id}`}</span>
                            <span class="text-xs text-gray-500">(${role})</span>
                        </div>
                        <button type="button" onclick="removePendingOwner(${user.id})" class="text-red-600 hover:text-red-700 text-xs font-medium">
                            Remove
                        </button>
                    `;
                    list.appendChild(ownerDiv);
                }
            },
            
            removePendingOwner(userId) {
                // Remove from form
                const form = document.querySelector('form[action*="venues"]');
                if (form) {
                    const input = form.querySelector(`input[name="venue_owners[${userId}]"]`);
                    if (input) input.remove();
                }
                
                // Remove from UI
                const ownerDiv = document.getElementById(`pending-owner-${userId}`);
                if (ownerDiv) ownerDiv.remove();
                
                // Remove container if empty
                const list = document.getElementById('pending-owners-list');
                if (list && list.children.length === 0) {
                    const container = document.getElementById('pending-owners-container');
                    if (container) container.remove();
                }
            },
            
            async updateRole(userId, role) {
                if (!this.venueId) return;
                
                const formData = new FormData();
                formData.append('user_id', userId);
                formData.append('role', role);
                
                try {
                    const response = await fetch(`/admin/venues/${this.venueId}/update-role`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert(result.message || 'Error updating role');
                        location.reload();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred');
                    location.reload();
                }
            },
            
            async removeUser(userId) {
                if (!this.venueId) return;
                
                if (!confirm('Are you sure you want to remove this user from the venue?')) {
                    return;
                }
                
                const formData = new FormData();
                formData.append('user_id', userId);
                
                try {
                    const response = await fetch(`/admin/venues/${this.venueId}/remove-role`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok) {
                        location.reload();
                    } else {
                        alert(result.message || 'Error removing user');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred');
                }
            }
        }
    }
    
    // Global function for removing pending owners
    window.removePendingOwner = function(userId) {
        const component = document.querySelector('[x-data*="venueManagement"]');
        if (component && component.__x && component.__x.$data && typeof component.__x.$data.removePendingOwner === 'function') {
            component.__x.$data.removePendingOwner(userId);
        }
    };
    </script>
@endif

