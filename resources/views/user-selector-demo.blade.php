@extends('layouts.app')

@section('title', 'User Selector Demo')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">User Selector Component</h1>
            <p class="mt-2 text-gray-600">A reusable user selector with search and filter functionality</p>
        </div>

        <div class="space-y-8">
            <!-- Basic Usage -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Usage</h2>
                <p class="text-gray-600 mb-4">Simple user selector without any special configuration:</p>
                
                <x-user-selector 
                    name="user_id" 
                    placeholder="Select a user..."
                />
            </div>

            <!-- With Pre-selected User -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">With Pre-selected User</h2>
                <p class="text-gray-600 mb-4">User selector with a pre-selected user:</p>
                
                <x-user-selector 
                    name="user_id_2" 
                    :selectedUserId="1"
                    placeholder="Select a user..."
                />
            </div>

            <!-- For Admin -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">For Admin</h2>
                <p class="text-gray-600 mb-4">User selector configured for admin use (shows all users):</p>
                
                <x-user-selector 
                    name="user_id_3" 
                    userRole="all"
                    placeholder="Select a user..."
                />
            </div>

            <!-- In Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">In Form</h2>
                <p class="text-gray-600 mb-4">User selector integrated into a form:</p>
                
                <form class="space-y-4" x-data="{ selectedUser: null }" @user-selected="selectedUser = $event.detail.user">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Name</label>
                        <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent" placeholder="Enter event name">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Create On Behalf Of</label>
                        <x-user-selector 
                            name="user_id" 
                            placeholder="Select a user to create event on behalf of..."
                            userRole="all"
                            required
                        />
                    </div>
                    
                    <div x-show="selectedUser" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                        <h3 class="font-medium text-green-800">Selected User:</h3>
                        <p class="text-green-700" x-text="selectedUser ? selectedUser.name + ' (' + selectedUser.role_name + ') - ' + selectedUser.email : ''"></p>
                    </div>
                    
                    <button type="submit" class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors">
                        Create Event
                    </button>
                </form>
            </div>

            <!-- Usage Examples -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Usage Examples</h2>
                
                <div class="space-y-4">
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">Basic Usage</h3>
                        <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"><code>&lt;x-user-selector 
    name="user_id" 
    placeholder="Select a user..."
/&gt;</code></pre>
                    </div>
                    
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">With Pre-selected User</h3>
                        <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"><code>&lt;x-user-selector 
    name="user_id" 
    :selectedUserId="1"
    placeholder="Select a user..."
/&gt;</code></pre>
                    </div>
                    
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">For Admin</h3>
                        <pre class="bg-gray-100 p-3 rounded text-sm overflow-x-auto"><code>&lt;x-user-selector 
    name="user_id" 
    userRole="all"
    placeholder="Select a user..."
/&gt;</code></pre>
                    </div>
                </div>
            </div>

            <!-- Features -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Features</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900">Search & Filter</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Real-time search by name or email</li>
                            <li>• Filter by user role (Admin, Organiser, Artist, User)</li>
                            <li>• Filter by account status (Active/Inactive)</li>
                            <li>• Clear all filters with one click</li>
                        </ul>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900">User Information</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• User name and email display</li>
                            <li>• Role badges with color coding</li>
                            <li>• Account creation date</li>
                            <li>• Account status indicators</li>
                        </ul>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900">User Experience</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Responsive design for all devices</li>
                            <li>• Loading states and animations</li>
                            <li>• Pagination for large user lists</li>
                            <li>• Keyboard navigation support</li>
                        </ul>
                    </div>
                    
                    <div class="space-y-2">
                        <h3 class="font-medium text-gray-900">Integration</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Works with any form</li>
                            <li>• Custom event dispatching</li>
                            <li>• Alpine.js integration</li>
                            <li>• Tailwind CSS styling</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

