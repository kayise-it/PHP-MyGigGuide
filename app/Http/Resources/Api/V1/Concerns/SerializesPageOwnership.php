<?php

namespace App\Http\Resources\Api\V1\Concerns;

use App\Models\User;
use Illuminate\Http\Request;

trait SerializesPageOwnership
{
    /**
     * @return array<string, mixed>
     */
    protected function pageOwnershipFields(Request $request): array
    {
        $user = $request->user('sanctum');
        $status = method_exists($this->resource, 'getPublicOwnershipStatus')
            ? $this->resource->getPublicOwnershipStatus()
            : 'unclaimed';

        $userPending = $user instanceof User
            && $this->resource->pending_claim_user_id === $user->id
            && method_exists($this->resource, 'hasPendingClaim')
            && $this->resource->hasPendingClaim();

        return [
            'ownership_status' => $status,
            'user_claim_pending' => $userPending,
            'can_request_claim' => $user instanceof User
                && in_array($status, ['unclaimed'], true)
                && ! $userPending
                && (
                    ! method_exists($this->resource, 'hasPendingClaim')
                    || ! $this->resource->hasPendingClaim()
                ),
        ];
    }
}
