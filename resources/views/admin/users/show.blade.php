@extends('layouts.admin')

@section('title', 'User Details - My Gig Guide')
@section('page-title', 'User Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
            <p class="mt-1 text-sm text-gray-600">User account details and activity</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit User
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Users
            </a>
        </div>
    </div>

    <!-- User Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Basic Information -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="flex items-center">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 flex items-center justify-center">
                            <span class="text-white text-xl font-semibold">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-4">
                            <h4 class="text-lg font-medium text-gray-900">{{ $user->name }}</h4>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            <p class="text-xs text-gray-400">{{ '@' . $user->username }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Username</label>
                            <p class="mt-1 text-sm text-gray-900">{{ '@' . $user->username }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($user->is_active) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Joined</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last login</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('M j, Y g:i A') }}
                                    <span class="text-gray-500">({{ $user->last_login_at->diffForHumans() }})</span>
                                @else
                                    Never
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Last app use</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($user->last_api_used_at)
                                    {{ \Carbon\Carbon::parse($user->last_api_used_at)->format('M j, Y g:i A') }}
                                    <span class="text-gray-500">({{ \Carbon\Carbon::parse($user->last_api_used_at)->diffForHumans() }})</span>
                                @else
                                    No API activity
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles & Permissions -->
            <div class="mt-6 bg-white shadow-sm rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Roles & Permissions</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
                            <div class="flex flex-wrap gap-2">
                                @forelse($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500">No roles assigned</span>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                            <div class="flex flex-wrap gap-2">
                                @forelse($user->permissions as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst(str_replace('_', ' ', $permission->name)) }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500">No direct permissions assigned</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="w-full">
                        @csrf
                        @method('PATCH')
                        <x-admin.button variant="secondary" :full="true" class="justify-center">
                            @if($user->is_active)
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                </svg>
                                Deactivate User
                            @else
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Activate User
                            @endif
                        </x-admin.button>
                    </form>
                    
                    <!-- Email verification controls (visible to admins) -->
                    <div class="w-full grid grid-cols-1 gap-2">
                        @if(!$user->email_verified_at)
                        <form method="POST" action="{{ route('admin.users.verify-email', $user) }}" class="w-full">
                            @csrf
                            @method('PATCH')
                            <x-admin.button variant="primary" :full="true" class="justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Mark Email Verified
                            </x-admin.button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('admin.users.unverify-email', $user) }}" class="w-full" onsubmit="return confirm('Mark this account as unverified?')">
                            @csrf
                            @method('PATCH')
                            <x-admin.button variant="secondary" :full="true" class="justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728M5.636 5.636l12.728 12.728" />
                                </svg>
                                Mark Email Unverified
                            </x-admin.button>
                        </form>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="w-full" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <x-admin.button variant="danger" :full="true" class="justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z" />
                            </svg>
                            Delete User
                        </x-admin.button>
                    </form>
                </div>
            </div>

            <!-- Account Statistics -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Account Statistics</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Events Created</span>
                        <span class="text-sm font-medium text-gray-900">{{ $user->events->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Venues Owned</span>
                        <span class="text-sm font-medium text-gray-900">{{ $user->venues->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Ratings Given</span>
                        <span class="text-sm font-medium text-gray-900">{{ $user->ratings->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Last Login</span>
                        <span class="text-sm font-medium text-gray-900">{{ $user->updated_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

