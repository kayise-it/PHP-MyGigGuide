<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $user->name }}</h2>
            <p class="mt-1 text-sm text-gray-600">Quick overview and actions for this account.</p>
        </div>
        <div class="mt-3 sm:mt-0 flex flex-wrap gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-primary btn-sm">
                Edit User
            </a>
            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-secondary btn-sm">
                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                  onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger btn-sm">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-gray-200 rounded-xl">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Basic Information</h3>
                </div>
                <div class="px-4 py-4 space-y-4">
                    <div class="flex items-center">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-purple-500 to-blue-500 flex items-center justify-center">
                            <span class="text-white text-lg font-semibold">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                            <div class="text-xs text-gray-400">@{{ $user->username }}</div>
                        </div>
                    </div>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($user->is_active) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Joined</dt>
                            <dd class="mt-1 text-gray-900">{{ $user->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Last login</dt>
                            <dd class="mt-1 text-gray-900">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('M j, Y g:i A') }}
                                    <span class="text-gray-500">({{ $user->last_login_at->diffForHumans() }})</span>
                                @else
                                    Never
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Last app use</dt>
                            <dd class="mt-1 text-gray-900">
                                @if($user->last_api_used_at)
                                    {{ \Carbon\Carbon::parse($user->last_api_used_at)->format('M j, Y g:i A') }}
                                    <span class="text-gray-500">({{ \Carbon\Carbon::parse($user->last_api_used_at)->diffForHumans() }})</span>
                                @else
                                    No API activity
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Roles</dt>
                            <dd class="mt-1 flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @empty
                                    <span class="text-gray-500">No roles</span>
                                @endforelse
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Verified</dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $user->email_verified_at ? 'Yes' : 'No' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Quick Stats</h3>
                </div>
                <div class="px-4 py-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Events Created</span>
                        <span class="font-medium text-gray-900">{{ $user->events->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Venues Owned</span>
                        <span class="font-medium text-gray-900">{{ $user->venues->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Ratings Given</span>
                        <span class="font-medium text-gray-900">{{ $user->ratings->count() }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ route('admin.users.show', $user) }}"
               class="block text-xs text-gray-400 hover:text-gray-600 text-right">
                Open full user page →
            </a>
        </div>
    </div>
</div>

