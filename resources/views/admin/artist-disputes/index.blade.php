@extends('layouts.admin')

@section('title', 'Artist Claim Disputes - Admin Panel')
@section('description', 'Manage artist profile claim disputes')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Artist Claim Disputes</h1>
            <p class="text-gray-600">Review and resolve artist profile claim disputes</p>
        </div>
        <div class="flex gap-2">
            @php
                try {
                    // Count all disputed items (with or without pending claims)
                    $disputedCount = \App\Models\Artist::where(function($q) {
                        $q->where('dispute_raised', true)
                          ->orWhere('claim_status', 'disputed');
                    })->count();
                    // Count pending items (must have pending_claim_user_id)
                    $pendingCount = \App\Models\Artist::where('claim_status', 'pending')
                        ->whereNotNull('pending_claim_user_id')->count();
                } catch (\Exception $e) {
                    $disputedCount = 0;
                    $pendingCount = 0;
                }
            @endphp
            <a href="{{ route('admin.artist-disputes.index', ['status' => 'disputed']) }}" 
               class="px-4 py-2 {{ request('status') === 'disputed' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700' }} rounded-lg hover:bg-red-700 transition-colors">
                Disputed ({{ $disputedCount }})
            </a>
            <a href="{{ route('admin.artist-disputes.index', ['status' => 'pending']) }}" 
               class="px-4 py-2 {{ request('status') === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-200 text-gray-700' }} rounded-lg hover:bg-yellow-700 transition-colors">
                Pending ({{ $pendingCount }})
            </a>
            <a href="{{ route('admin.artist-disputes.index') }}" 
               class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                All
            </a>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" class="mb-4">
        <div class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by artist name, email..." 
                   class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 flex-1" />
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}" />
            @endif
            <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Search
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.artist-disputes.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Clear
                </a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Artist</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Claimant</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Claim Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dispute Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($disputes as $artist)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4">
                        <div class="flex items-center">
                            @if($artist->profile_picture)
                                <img src="{{ Storage::url($artist->profile_picture) }}" alt="{{ $artist->stage_name }}" class="w-10 h-10 rounded-full mr-3" />
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <div class="font-medium text-gray-900">{{ $artist->stage_name }}</div>
                                <div class="text-sm text-gray-500">{{ $artist->contact_email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        @if($artist->pendingClaimUser)
                            <div>
                                <div class="font-medium text-gray-900">{{ $artist->pendingClaimUser->name }}</div>
                                <div class="text-sm text-gray-500">{{ $artist->pendingClaimUser->email }}</div>
                            </div>
                        @else
                            <span class="text-gray-400">N/A</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        @if($artist->dispute_raised)
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Disputed</span>
                        @elseif($artist->claim_status === 'pending')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($artist->claim_status) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-500">
                        {{ $artist->pending_claim_at?->diffForHumans() ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-500">
                        {{ $artist->dispute_raised_at?->diffForHumans() ?? '-' }}
                    </td>
                    <td class="px-4 py-4">
                        <a href="{{ route('admin.artist-disputes.show', $artist) }}" 
                           class="text-purple-600 hover:text-purple-800 font-medium">
                            Review
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                        No disputes found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $disputes->links() }}</div>
</div>
@endsection



