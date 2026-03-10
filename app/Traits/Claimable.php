<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait Claimable
 * 
 * Provides common claim functionality for entities that can be claimed by users.
 * Used by Artist, Venue, Event, and Organiser models.
 */
trait Claimable
{
    /**
     * Get the fillable fields required for claim functionality.
     * Models using this trait should merge these into their $fillable array.
     */
    public static function getClaimFillableFields(): array
    {
        return [
            'pending_claim_user_id',
            'pending_claim_at',
            'dispute_raised',
            'dispute_raised_at',
            'dispute_reason',
            'claim_status',
            'grace_period_ends_at',
            'warning_email_sent_at',
        ];
    }

    /**
     * Get the casts required for claim functionality.
     * Models using this trait should merge these into their $casts array.
     */
    public static function getClaimCasts(): array
    {
        return [
            'pending_claim_at' => 'datetime',
            'dispute_raised_at' => 'datetime',
            'grace_period_ends_at' => 'datetime',
            'warning_email_sent_at' => 'datetime',
            'dispute_raised' => 'boolean',
        ];
    }

    /**
     * Check if this entity is unclaimed (no user owns it).
     */
    public function isUnclaimed(): bool
    {
        return $this->getOwnerUserId() === null;
    }

    /**
     * Check if this entity has a pending claim.
     */
    public function hasPendingClaim(): bool
    {
        return $this->pending_claim_user_id !== null && $this->claim_status === 'pending';
    }

    /**
     * Check if this entity has a disputed claim.
     */
    public function hasDisputedClaim(): bool
    {
        return $this->dispute_raised || $this->claim_status === 'disputed';
    }

    /**
     * Get the user who has a pending claim on this entity.
     */
    public function pendingClaimUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pending_claim_user_id');
    }

    /**
     * Scope to get only unclaimed entities.
     */
    public function scopeUnclaimed($query)
    {
        $ownerField = $this->getOwnerUserIdField();
        return $query->whereNull($ownerField);
    }

    /**
     * Scope to get entities with pending claims.
     */
    public function scopeWithPendingClaims($query)
    {
        return $query->whereNotNull('pending_claim_user_id')
                     ->where('claim_status', 'pending');
    }

    /**
     * Scope to get entities with disputes.
     */
    public function scopeWithDisputes($query)
    {
        return $query->where(function ($q) {
            $q->where('dispute_raised', true)
              ->orWhere('claim_status', 'disputed');
        });
    }

    /**
     * Get the email field name for this entity type.
     * Override in model if different.
     */
    public function getOwnerEmailField(): string
    {
        return 'contact_email';
    }

    /**
     * Get the email address for claim matching.
     */
    public function getClaimEmail(): ?string
    {
        $field = $this->getOwnerEmailField();
        return $this->{$field} ?? null;
    }

    /**
     * Get the display name field for this entity type.
     * Override in model if different.
     */
    public function getDisplayNameField(): string
    {
        // Default mappings based on model type
        $class = class_basename($this);
        
        return match ($class) {
            'Artist' => 'stage_name',
            'Organiser' => 'organisation_name',
            default => 'name',
        };
    }

    /**
     * Get the display name for this entity.
     */
    public function getDisplayName(): string
    {
        $field = $this->getDisplayNameField();
        return $this->{$field} ?? 'Unknown';
    }

    /**
     * Get the user ID field name for this entity type.
     * This determines which field makes the entity "claimed".
     */
    public function getOwnerUserIdField(): string
    {
        $class = class_basename($this);
        
        // Event uses owner_id/owner_type (polymorphic)
        if ($class === 'Event') {
            return 'owner_id';
        }
        
        return 'user_id';
    }

    /**
     * Get the owner user ID value.
     */
    public function getOwnerUserId(): ?int
    {
        $field = $this->getOwnerUserIdField();
        return $this->{$field};
    }

    /**
     * Get the claimable type identifier for routing/display.
     */
    public function getClaimableType(): string
    {
        return strtolower(class_basename($this));
    }

    /**
     * Get the icon name for this entity type (for UI).
     */
    public function getTypeIcon(): string
    {
        $class = class_basename($this);
        
        return match ($class) {
            'Artist' => 'microphone',
            'Venue' => 'building',
            'Event' => 'calendar',
            'Organiser' => 'users',
            default => 'circle',
        };
    }

    /**
     * Get the color class for this entity type (for UI badges).
     */
    public function getTypeColorClass(): string
    {
        $class = class_basename($this);
        
        return match ($class) {
            'Artist' => 'bg-purple-100 text-purple-700',
            'Venue' => 'bg-blue-100 text-blue-700',
            'Event' => 'bg-green-100 text-green-700',
            'Organiser' => 'bg-orange-100 text-orange-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Initiate a claim on this entity.
     */
    public function initiateClaim(User $user, ?\Carbon\Carbon $gracePeriodEndsAt = null): void
    {
        $this->update([
            'pending_claim_user_id' => $user->id,
            'pending_claim_at' => now(),
            'claim_status' => 'pending',
            'grace_period_ends_at' => $gracePeriodEndsAt,
        ]);
    }

    /**
     * Approve a pending claim.
     */
    public function approveClaim(): void
    {
        if (!$this->hasPendingClaim()) {
            return;
        }

        $ownerField = $this->getOwnerUserIdField();
        
        $this->update([
            $ownerField => $this->pending_claim_user_id,
            'claim_status' => 'approved',
            'pending_claim_user_id' => null,
            'pending_claim_at' => null,
            'grace_period_ends_at' => null,
        ]);
    }

    /**
     * Reject a pending claim.
     */
    public function rejectClaim(?string $reason = null): void
    {
        $this->update([
            'claim_status' => 'rejected',
            'pending_claim_user_id' => null,
            'pending_claim_at' => null,
            'grace_period_ends_at' => null,
            'dispute_reason' => $reason,
        ]);
    }

    /**
     * Raise a dispute on the claim.
     */
    public function raiseDispute(string $reason): void
    {
        $this->update([
            'dispute_raised' => true,
            'dispute_raised_at' => now(),
            'dispute_reason' => $reason,
            'claim_status' => 'disputed',
        ]);
    }

    /**
     * Clear claim data (used when manually linking to user).
     */
    public function clearClaimData(): void
    {
        // Some legacy tables may not yet have all claim tracking columns.
        // Build the update payload dynamically, only including columns that exist.
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($this->getTable());

        $fields = [
            'pending_claim_user_id' => null,
            'pending_claim_at' => null,
            'claim_status' => 'none',
            'grace_period_ends_at' => null,
            'dispute_raised' => false,
            'dispute_raised_at' => null,
            'dispute_reason' => null,
            'warning_email_sent_at' => null,
        ];

        $update = [];
        foreach ($fields as $field => $value) {
            if (in_array($field, $columns, true)) {
                $update[$field] = $value;
            }
        }

        if (!empty($update)) {
            $this->update($update);
        }
    }

    /**
     * Mark warning email as sent.
     */
    public function markWarningEmailSent(): void
    {
        $this->update([
            'warning_email_sent_at' => now(),
        ]);
    }

    /**
     * Check if grace period has expired.
     */
    public function isGracePeriodExpired(): bool
    {
        if (!$this->grace_period_ends_at) {
            return true;
        }

        return now()->gte($this->grace_period_ends_at);
    }
}


