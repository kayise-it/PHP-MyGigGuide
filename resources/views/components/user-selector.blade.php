@props([
    'selectedUserId' => null,
    'name' => 'user_id',
    'placeholder' => 'Select a user...',
    'userRole' => 'all',
    'required' => false,
    'class' => ''
])

<div class="relative user-selector" x-data="userSelector({
    selectedUserId: @js($selectedUserId),
    name: @js($name),
    placeholder: @js($placeholder),
    userRole: @js($userRole),
    required: @js($required)
})">
    <!-- Selected User Display -->
    <div
        @click="toggleDropdown()"
        :class="{
            'border-purple-500 ring-2 ring-purple-200': isOpen,
            'border-gray-200 hover:border-gray-300': !isOpen
        }"
        class="relative w-full bg-white border-2 rounded-xl p-4 cursor-pointer transition-all duration-200 {{ $class }}"
    >
        <template x-if="selectedUser">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-purple-100 p-2 rounded-lg">
                        <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900" x-text="selectedUser.name"></h3>
                        <p class="text-sm text-gray-600" x-text="selectedUser.email"></p>
                        <p class="text-xs text-gray-500" x-text="selectedUser.role_name"></p>
                    </div>
                </div>
                <svg class="h-5 w-5 text-gray-400 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </template>
        
        <template x-if="!selectedUser">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-gray-100 p-2 rounded-lg">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <span class="text-gray-500" x-text="placeholder"></span>
                </div>
                <svg class="h-5 w-5 text-gray-400 transition-transform" :class="{ 'rotate-180': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </template>
    </div>

    <!-- Hidden Input -->
    <input type="hidden" :name="name" :value="selectedUserId" x-model="selectedUserId">

    <!-- Dropdown -->
    <div x-show="isOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg max-h-96 overflow-hidden"
         style="display: none; z-index: 9999;"
        
        <!-- Search and Filters -->
        <div class="p-4 border-b border-gray-100">
            <!-- Search Bar -->
            <div class="relative mb-3">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    type="text"
                    x-model="searchTerm"
                    @input.debounce.300ms="searchUsers()"
                    placeholder="Search users by name or email..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                />
            </div>

            <!-- Filters Toggle -->
            <div class="flex items-center justify-between">
                <button
                    @click="showFilters = !showFilters"
                    class="flex items-center space-x-2 text-sm text-purple-600 hover:text-purple-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                    </svg>
                    <span>Filters</span>
                </button>
                
                <button
                    x-show="hasActiveFilters"
                    @click="clearFilters()"
                    class="flex items-center space-x-1 text-sm text-gray-500 hover:text-gray-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span>Clear</span>
                </button>
            </div>

            <!-- Filter Options -->
            <div x-show="showFilters" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 max-h-0"
                 x-transition:enter-end="opacity-100 max-h-96"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 max-h-96"
                 x-transition:leave-end="opacity-0 max-h-0"
                 class="mt-3 space-y-3 pt-3 border-t border-gray-100">
                
                <!-- Role Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">User Role</label>
                    <div class="grid grid-cols-2 gap-1">
                        <template x-for="role in userRoles" :key="role">
                            <button
                                @click="selectRole(role)"
                                :class="{
                                    'bg-purple-100 text-purple-700': selectedRole === role,
                                    'bg-gray-100 text-gray-600 hover:bg-gray-200': selectedRole !== role
                                }"
                                class="px-2 py-1 text-xs rounded transition-colors"
                                x-text="role"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Account Status</label>
                    <div class="flex space-x-2">
                        <button
                            @click="selectStatus('active')"
                            :class="{
                                'bg-green-100 text-green-700': selectedStatus === 'active',
                                'bg-gray-100 text-gray-600 hover:bg-gray-200': selectedStatus !== 'active'
                            }"
                            class="px-2 py-1 text-xs rounded transition-colors"
                        >
                            Active
                        </button>
                        <button
                            @click="selectStatus('inactive')"
                            :class="{
                                'bg-red-100 text-red-700': selectedStatus === 'inactive',
                                'bg-gray-100 text-gray-600 hover:bg-gray-200': selectedStatus !== 'inactive'
                            }"
                            class="px-2 py-1 text-xs rounded transition-colors"
                        >
                            Inactive
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- User List -->
        <div class="max-h-64 overflow-y-auto">
            <template x-if="loading">
                <div class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                    <span class="ml-2 text-sm text-gray-600">Loading users...</span>
                </div>
            </template>
            
            <template x-if="!loading && filteredUsers.length === 0">
                <div class="text-center py-8 text-gray-500">
                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <p class="text-sm">No users found</p>
                    <p class="text-xs text-gray-400 mt-1">Try adjusting your search criteria</p>
                </div>
            </template>
            
            <template x-if="!loading && filteredUsers.length > 0">
                <div class="divide-y divide-gray-100">
                    <template x-for="user in filteredUsers" :key="user.id">
                        <div
                            @click="selectUser(user)"
                            :class="{
                                'bg-purple-50': selectedUserId === user.id
                            }"
                            class="p-3 cursor-pointer transition-colors hover:bg-gray-50"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h4 class="font-medium text-gray-900" x-text="user.name"></h4>
                                        <span :class="getRoleBadgeClass(user.role_name)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" x-text="user.role_name"></span>
                                        <svg x-show="selectedUserId === user.id" class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm text-gray-600 flex items-center mt-1">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <span x-text="user.email"></span>
                                    </p>
                                    <p class="text-xs text-gray-500 flex items-center mt-1">
                                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span x-text="'Joined: ' + formatDate(user.created_at)"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Pagination -->
        <div x-show="pagination && pagination.totalPages > 1" class="border-t border-gray-100 p-3 flex items-center justify-between">
            <span class="text-xs text-gray-500" x-text="'Page ' + (pagination ? pagination.page : 1) + ' of ' + (pagination ? pagination.totalPages : 1) + ' (' + (pagination ? pagination.total : 0) + ' users)'"></span>
            <div class="flex space-x-1">
                <button
                    @click="previousPage()"
                    :disabled="currentPage === 1"
                    class="px-2 py-1 text-xs border border-gray-200 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Previous
                </button>
                <button
                    @click="nextPage()"
                    :disabled="currentPage === (pagination ? pagination.totalPages : 1)"
                    class="px-2 py-1 text-xs border border-gray-200 rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function userSelector(config) {
    return {
        // Configuration
        selectedUserId: config.selectedUserId,
        name: config.name,
        placeholder: config.placeholder,
        userRole: config.userRole,
        required: config.required,
        
        // State
        isOpen: false,
        loading: false,
        searchTerm: '',
        selectedRole: '',
        selectedStatus: '',
        showFilters: false,
        currentPage: 1,
        users: [],
        filteredUsers: [],
        selectedUser: null,
        pagination: null,
        
        // User roles
        userRoles: ['All', 'User', 'Artist', 'Organiser', 'Admin'],
        
        // Computed
        get hasActiveFilters() {
            return this.searchTerm || this.selectedRole || this.selectedStatus;
        },
        
        // Methods
        init() {
            // Ensure dropdown starts closed
            this.isOpen = false;
            this.fetchUsers();
            this.findSelectedUser();
        },
        
        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.fetchUsers();
            }
        },
        
        async fetchUsers() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    page: this.currentPage,
                    limit: 20
                });
                
                if (this.searchTerm) params.append('search', this.searchTerm);
                if (this.selectedRole && this.selectedRole !== 'All') params.append('role', this.selectedRole.toLowerCase());
                if (this.selectedStatus) params.append('status', this.selectedStatus);
                
                const response = await fetch(`/admin/api/users/search?${params}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                this.users = data.users || [];
                this.filteredUsers = data.users || [];
                this.pagination = data.pagination;
            } catch (error) {
                console.error('Error fetching users:', error);
                this.users = [];
                this.filteredUsers = [];
            } finally {
                this.loading = false;
            }
        },
        
        async searchUsers() {
            this.currentPage = 1;
            await this.fetchUsers();
        },
        
        selectUser(user) {
            this.selectedUser = user;
            this.selectedUserId = user.id;
            this.isOpen = false;
            
            // Dispatch custom event for parent components
            this.$dispatch('user-selected', { user: user });
        },
        
        selectRole(role) {
            this.selectedRole = role;
            this.showFilters = false;
            this.searchUsers();
        },
        
        selectStatus(status) {
            this.selectedStatus = status;
            this.showFilters = false;
            this.searchUsers();
        },
        
        clearFilters() {
            this.searchTerm = '';
            this.selectedRole = '';
            this.selectedStatus = '';
            this.currentPage = 1;
            this.searchUsers();
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.fetchUsers();
            }
        },
        
        nextPage() {
            if (this.pagination && this.currentPage < this.pagination.totalPages) {
                this.currentPage++;
                this.fetchUsers();
            }
        },
        
        findSelectedUser() {
            if (this.selectedUserId && this.users.length > 0) {
                this.selectedUser = this.users.find(u => u.id == this.selectedUserId);
            }
        },
        
        getRoleBadgeClass(role) {
            const roleClasses = {
                'Admin': 'bg-red-100 text-red-800',
                'Organiser': 'bg-blue-100 text-blue-800',
                'Artist': 'bg-green-100 text-green-800',
                'User': 'bg-gray-100 text-gray-800'
            };
            return roleClasses[role] || 'bg-gray-100 text-gray-800';
        },
        
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        }
    }
}
</script>
