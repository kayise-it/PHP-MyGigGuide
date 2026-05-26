@if($entity->hasPendingClaim())
@php
    $claimant = $entity->pendingClaimUser;
@endphp
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-5">
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h3 class="text-lg font-semibold text-amber-900">Pending claim request</h3>
            <p class="text-sm text-amber-800 mt-1">
                Requested
                @if($entity->pending_claim_at)
                    {{ $entity->pending_claim_at->diffForHumans() }}
                @endif
                @if($claimant)
                    by <strong>{{ $claimant->name ?: $claimant->username }}</strong>
                    ({{ $claimant->email }})
                @endif
            </p>
            @if(!empty($entity->claim_request_message))
                <div class="mt-3 p-3 bg-white rounded-lg border border-amber-100">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Message from claimant</p>
                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $entity->claim_request_message }}</p>
                </div>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.unclaimed.approve-claim', ['type' => $type, 'id' => $entity->id]) }}"
                  onsubmit="return confirm('Approve this claim and link the page to the user?');">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                    Approve claim
                </button>
            </form>
            <form method="POST" action="{{ route('admin.unclaimed.reject-claim', ['type' => $type, 'id' => $entity->id]) }}"
                  class="flex items-end gap-2"
                  onsubmit="return confirm('Reject this claim request?');">
                @csrf
                <div>
                    <label for="reject_reason_{{ $type }}_{{ $entity->id }}" class="sr-only">Rejection reason (optional)</label>
                    <input type="text" id="reject_reason_{{ $type }}_{{ $entity->id }}" name="reason" placeholder="Reason (optional)"
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-48">
                </div>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                    Reject
                </button>
            </form>
        </div>
    </div>
</div>
@endif
