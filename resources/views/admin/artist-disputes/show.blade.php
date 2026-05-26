@extends('layouts.admin')

@section('title', 'Review Dispute - Admin Panel')
@section('description', 'Review and resolve artist claim dispute')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.artist-disputes.index') }}" class="text-purple-600 hover:text-purple-800 mb-4 inline-block">
            ← Back to Disputes
        </a>
        <h1 class="text-2xl font-bold text-gray-900">
            @if($artist->dispute_raised || $artist->claim_status === 'disputed')
                Review Claim Dispute
            @else
                Review Pending Claim
            @endif
        </h1>
    </div>

    @if($artist->dispute_raised)
    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800">
                    ⚠️ DISPUTE RAISED - This claim has been disputed by the artist's email owner.
                </p>
            </div>
        </div>
    </div>
    @endif

    @if($artist->hasPendingClaim() && !empty($artist->claim_request_message))
    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 mb-6">
        <p class="text-sm font-medium text-amber-900 mb-1">Message from claimant (manual request)</p>
        <p class="text-sm text-amber-950 whitespace-pre-wrap">{{ $artist->claim_request_message }}</p>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Artist Information -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Artist Profile</h2>
            <div class="space-y-3">
                @if($artist->profile_picture)
                    <img src="{{ Storage::url($artist->profile_picture) }}" alt="{{ $artist->stage_name }}" class="w-24 h-24 rounded-full mb-4" />
                @endif
                <div>
                    <span class="text-sm text-gray-500">Stage Name:</span>
                    <p class="font-medium text-gray-900">{{ $artist->stage_name }}</p>
                </div>
                @if($artist->real_name)
                <div>
                    <span class="text-sm text-gray-500">Real Name:</span>
                    <p class="font-medium text-gray-900">{{ $artist->real_name }}</p>
                </div>
                @endif
                @if($artist->genre)
                <div>
                    <span class="text-sm text-gray-500">Genre:</span>
                    <p class="font-medium text-gray-900">{{ $artist->genre }}</p>
                </div>
                @endif
                <div>
                    <span class="text-sm text-gray-500">Contact Email:</span>
                    <p class="font-medium text-gray-900">{{ $artist->contact_email }}</p>
                </div>
                @if($artist->phone_number)
                <div>
                    <span class="text-sm text-gray-500">Phone:</span>
                    <p class="font-medium text-gray-900">{{ $artist->phone_number }}</p>
                </div>
                @endif
                <div>
                    <span class="text-sm text-gray-500">Created:</span>
                    <p class="text-sm text-gray-600">{{ $artist->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Claimant Information -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Claimant Information</h2>
            @if($artist->pendingClaimUser)
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-500">Name:</span>
                        <p class="font-medium text-gray-900">{{ $artist->pendingClaimUser->name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Email:</span>
                        <p class="font-medium text-gray-900">{{ $artist->pendingClaimUser->email }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Username:</span>
                        <p class="font-medium text-gray-900">{{ $artist->pendingClaimUser->username }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Registered:</span>
                        <p class="text-sm text-gray-600">{{ $artist->pendingClaimUser->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">Email Verified:</span>
                        <p class="text-sm {{ $artist->pendingClaimUser->hasVerifiedEmail() ? 'text-green-600' : 'text-red-600' }}">
                            {{ $artist->pendingClaimUser->hasVerifiedEmail() ? 'Yes' : 'No' }}
                        </p>
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <p class="text-sm text-yellow-800">
                            <strong>No Active Claimant:</strong> This artist is marked as disputed but has no pending claim. 
                            The dispute may have been raised manually or the pending claim was cleared.
                        </p>
                    </div>
                    @if($artist->dispute_reason)
                    <div>
                        <span class="text-sm text-gray-500">Dispute Reason:</span>
                        <p class="text-sm text-gray-900 mt-1">{{ $artist->dispute_reason }}</p>
                    </div>
                    @endif
                    @if($artist->dispute_raised_at)
                    <div>
                        <span class="text-sm text-gray-500">Dispute Raised:</span>
                        <p class="text-sm text-gray-600">{{ $artist->dispute_raised_at->format('M d, Y g:i A') }}</p>
                    </div>
                    @endif
                    @if($artist->user_id)
                    <div>
                        <span class="text-sm text-gray-500">Currently Linked To:</span>
                        <p class="text-sm text-gray-900">
                            User ID: {{ $artist->user_id }}
                            @if($artist->user)
                                ({{ $artist->user->name }} - {{ $artist->user->email }})
                            @endif
                        </p>
                    </div>
                    @else
                    <div>
                        <span class="text-sm text-gray-500">Status:</span>
                        <p class="text-sm text-gray-900">Unclaimed (not linked to any user)</p>
                    </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Timeline -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
        <div class="space-y-4">
            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 text-xs font-bold">1</span>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Claim Initiated</p>
                    <p class="text-sm text-gray-500">{{ $artist->pending_claim_at?->format('M d, Y g:i A') ?? 'N/A' }}</p>
                </div>
            </div>
            @if($artist->warning_email_sent_at)
            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                    <span class="text-yellow-600 text-xs font-bold">2</span>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Warning Email Sent</p>
                    <p class="text-sm text-gray-500">{{ $artist->warning_email_sent_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
            @endif
            @if($artist->dispute_raised_at)
            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="text-red-600 text-xs font-bold">!</span>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-900">Dispute Raised</p>
                    <p class="text-sm text-gray-500">{{ $artist->dispute_raised_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
            @endif
            @if($artist->grace_period_ends_at)
            <div class="flex items-start">
                <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                    <span class="text-gray-600 text-xs font-bold">⏰</span>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-900">Grace Period Ends</p>
                    <p class="text-sm text-gray-500">{{ $artist->grace_period_ends_at->format('M d, Y g:i A') }} 
                        ({{ $artist->grace_period_ends_at->isPast() ? 'Expired' : $artist->grace_period_ends_at->diffForHumans() }})
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Resolution Actions</h2>
        
        @if($artist->dispute_raised)
            <div class="mb-4">
                <form method="POST" action="{{ route('admin.artist-disputes.clear-dispute', $artist) }}" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Are you sure? This will clear the dispute flag.')" 
                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                        Clear Dispute (If Raised in Error)
                    </button>
                </form>
            </div>
        @endif

        @if($artist->pending_claim_user_id)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Approve -->
            <form method="POST" action="{{ route('admin.artist-disputes.approve', $artist) }}">
                @csrf
                <div class="border border-green-200 rounded-lg p-4">
                    <h3 class="font-semibold text-green-900 mb-2">✓ Approve Claim</h3>
                    <p class="text-sm text-gray-600 mb-3">Link the artist profile to the claimant's account.</p>
                    <textarea name="admin_notes" placeholder="Admin notes (optional)" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3 text-sm" rows="3"></textarea>
                    <button type="submit" onclick="return confirm('Approve this claim? The artist profile will be linked to {{ $artist->pendingClaimUser->name ?? 'the claimant' }}.')" 
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Approve Claim
                    </button>
                </div>
            </form>

            <!-- Reject -->
            <form method="POST" action="{{ route('admin.artist-disputes.reject', $artist) }}">
                @csrf
                <div class="border border-red-200 rounded-lg p-4">
                    <h3 class="font-semibold text-red-900 mb-2">✗ Reject Claim</h3>
                    <p class="text-sm text-gray-600 mb-3">Reject this claim and keep the artist profile unclaimed.</p>
                    <textarea name="rejection_reason" placeholder="Reason for rejection (will be sent to claimant)" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-3 text-sm" rows="3" required></textarea>
                    <button type="submit" onclick="return confirm('Reject this claim? The artist profile will remain unclaimed.')" 
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Reject Claim
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-sm text-yellow-800">
                <strong>Note:</strong> This artist is marked as disputed but has no active pending claim. 
                You can clear the dispute flag if it was raised in error, or manually link this artist to a user from the Unclaimed Items page.
            </p>
        </div>
        @endif
    </div>
</div>
@endsection







