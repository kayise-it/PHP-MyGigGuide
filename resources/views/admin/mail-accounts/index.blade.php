@extends('layouts.admin')

@section('title', 'Email Accounts - My Gig Guide')
@section('page-title', 'Email Accounts')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Email Accounts</h2>
            <p class="mt-1 text-sm text-gray-600">Manage email accounts for @mygigguide.co.za</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.mail-accounts.create') }}" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Create Email Account
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input 
                    type="text" 
                    name="search" 
                    id="search"
                    value="{{ request('search') }}"
                    placeholder="Search by email address..."
                    class="form-input"
                >
            </div>
            <div class="sm:w-48">
                <label for="domain" class="block text-sm font-medium text-gray-700 mb-2">Domain</label>
                <select name="domain" id="domain" class="form-input">
                    <option value="">All Domains</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain }}" {{ request('domain') === $domain ? 'selected' : '' }}>
                            {{ $domain }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'domain']))
                <a href="{{ route('admin.mail-accounts.index') }}" class="btn-secondary">
                    Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Email Accounts Table -->
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email Address
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Domain
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($accounts as $account)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $account->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">{{ $account->domain_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.mail-accounts.show', $account->id) }}" 
                                       class="text-purple-600 hover:text-purple-900">
                                        View Config
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('admin.mail-accounts.edit', $account->id) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        Edit
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <form action="{{ route('admin.mail-accounts.destroy', $account->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this email account? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                No email accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($accounts->hasPages())
            <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
                {{ $accounts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
