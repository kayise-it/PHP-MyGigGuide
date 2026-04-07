<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use App\Mail\ArtistClaimWarningMail;
use App\Mail\ClaimWarningMail;
use App\Mail\PendingClaimNoticeMail;
use App\Mail\ClaimApprovedMail;
use App\Mail\ClaimRejectedMail;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClaimService
{
    /**
     * Get all claimable model classes.
     */
    public function getClaimableModels(): array
    {
        return [
            'artist' => Artist::class,
            'venue' => Venue::class,
            'event' => Event::class,
            'organiser' => Organiser::class,
        ];
    }

    /**
     * Get a model class by type string.
     */
    public function getModelClass(string $type): ?string
    {
        return $this->getClaimableModels()[$type] ?? null;
    }

    /**
     * Find all unclaimed entities matching an email address.
     * 
     * @return Collection<Model>
     */
    public function findUnclaimedByEmail(string $email): Collection
    {
        $email = strtolower($email);
        $results = collect();

        // Artists
        $artists = Artist::whereNull('user_id')
            ->whereRaw('LOWER(contact_email) = ?', [$email])
            ->get();
        $results = $results->merge($artists);

        // Venues (unclaimed = no user_id AND no owner_id)
        $venues = Venue::whereNull('user_id')
            ->whereNull('owner_id')
            ->whereRaw('LOWER(contact_email) = ?', [$email])
            ->get();
        $results = $results->merge($venues);

        // Organisers
        $organisers = Organiser::whereNull('user_id')
            ->whereRaw('LOWER(contact_email) = ?', [$email])
            ->get();
        $results = $results->merge($organisers);

        // Events don't have a direct email field, so we skip email matching for them
        // They can be claimed via the admin panel

        return $results;
    }

    /**
     * Initiate claims on all matching unclaimed entities for a user.
     */
    public function initiateClaimsForUser(User $user): array
    {
        $gracePeriodEnabled = config('artist_claims.enable_grace_period', false);
        $gracePeriodHours = config('artist_claims.grace_period_hours', 48);
        $gracePeriodEndsAt = $gracePeriodEnabled ? Carbon::now()->addHours($gracePeriodHours) : null;

        $unclaimedEntities = $this->findUnclaimedByEmail($user->email);
        $results = [
            'claimed' => [],
            'pending' => [],
            'errors' => [],
        ];

        foreach ($unclaimedEntities as $entity) {
            try {
                $entity->initiateClaim($user, $gracePeriodEndsAt);

                // Send warning email to the entity's contact email
                if (!$entity->warning_email_sent_at) {
                    $this->sendClaimWarningEmail($entity, $user, $gracePeriodEndsAt);
                    $entity->markWarningEmailSent();
                }

                $results['pending'][] = [
                    'type' => $entity->getClaimableType(),
                    'id' => $entity->id,
                    'name' => $entity->getDisplayName(),
                ];
            } catch (\Exception $e) {
                Log::error("Failed to initiate claim for {$entity->getClaimableType()} #{$entity->id}: " . $e->getMessage());
                $results['errors'][] = [
                    'type' => $entity->getClaimableType(),
                    'id' => $entity->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Send notice to the user about pending claims
        if (!empty($results['pending'])) {
            try {
                Mail::to($user->email)->send(
                    new PendingClaimNoticeMail($unclaimedEntities->first(), $gracePeriodEndsAt)
                );
            } catch (\Exception $e) {
                Log::error('Failed to send pending claim notice email: ' . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Auto-claim all matching entities when user verifies email.
     */
    public function autoClaimOnVerification(User $user): array
    {
        $results = [
            'approved' => [],
            'pending' => [],
            'disputed' => [],
        ];

        $unclaimedEntities = $this->findUnclaimedByEmail($user->email);

        foreach ($unclaimedEntities as $entity) {
            // Check if this user has a pending claim
            if ($entity->pending_claim_user_id !== $user->id) {
                continue;
            }

            // Check for disputes
            if ($entity->hasDisputedClaim()) {
                $results['disputed'][] = [
                    'type' => $entity->getClaimableType(),
                    'id' => $entity->id,
                    'name' => $entity->getDisplayName(),
                ];
                continue;
            }

            // Check grace period
            if (!$entity->isGracePeriodExpired()) {
                $results['pending'][] = [
                    'type' => $entity->getClaimableType(),
                    'id' => $entity->id,
                    'name' => $entity->getDisplayName(),
                    'grace_period_ends' => $entity->grace_period_ends_at,
                ];
                continue;
            }

            // Approve the claim
            $entity->approveClaim();

            // Assign role if needed
            $this->assignRoleForEntity($user, $entity);

            $results['approved'][] = [
                'type' => $entity->getClaimableType(),
                'id' => $entity->id,
                'name' => $entity->getDisplayName(),
            ];
        }

        return $results;
    }

    /**
     * Manually approve a claim (admin action).
     */
    public function approveClaim(Model $entity): bool
    {
        if (!$entity->hasPendingClaim()) {
            return false;
        }

        $user = $entity->pendingClaimUser;
        if (!$user) {
            return false;
        }

        $entity->approveClaim();
        $this->assignRoleForEntity($user, $entity);

        // Send approval email
        try {
            Mail::to($user->email)->send(new ClaimApprovedMail($entity));
        } catch (\Exception $e) {
            Log::error("Failed to send claim approved email: " . $e->getMessage());
        }

        return true;
    }

    /**
     * Reject a claim (admin action).
     */
    public function rejectClaim(Model $entity, ?string $reason = null): bool
    {
        if (!$entity->hasPendingClaim() && !$entity->hasDisputedClaim()) {
            return false;
        }

        $user = $entity->pendingClaimUser;
        
        $entity->rejectClaim($reason);

        // Send rejection email
        if ($user) {
            try {
                Mail::to($user->email)->send(new ClaimRejectedMail($entity, $reason));
            } catch (\Exception $e) {
                Log::error("Failed to send claim rejected email: " . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Link an entity to a user (admin manual linking).
     */
    public function linkToUser(Model $entity, User $user, bool $forceReplace = false): bool
    {
        $type = $entity->getClaimableType();
// For hasOne relationships (artist, organiser), check for existing
        if (in_array($type, ['artist', 'organiser']) && !$forceReplace) {
            $existing = $this->getUserExistingEntity($user, $type);
if ($existing && $existing->id !== $entity->id) {
throw new \App\Exceptions\UserAlreadyHasEntityException(
                    $type,
                    $existing,
                    $user
                );
            }
        }
        
        // If forcing replacement, unlink the existing entity first (make it unclaimed)
        if ($forceReplace && in_array($type, ['artist', 'organiser'])) {
            $existing = $this->getUserExistingEntity($user, $type);
            if ($existing && $existing->id !== $entity->id) {
                // Unlink the existing entity - this sets user_id to null, making it unclaimed
                $this->unlinkFromUser($existing);
            }
        }
        
        $ownerField = $entity->getOwnerUserIdField();
$entity->update([
            $ownerField => $user->id,
        ]);
$entity->clearClaimData();

$this->assignRoleForEntity($user, $entity);
return true;
    }

    /**
     * Get user's existing entity of a given type (for hasOne relationships).
     */
    public function getUserExistingEntity(User $user, string $type): ?Model
    {
        return match($type) {
            'artist' => $user->artist,
            'organiser' => $user->organiser,
            default => null,
        };
    }

    /**
     * Check if linking would conflict with existing entity.
     */
    public function checkLinkConflict(User $user, string $type, int $entityId): ?array
    {
        if (!in_array($type, ['artist', 'organiser'])) {
            return null; // No conflict possible for hasMany relationships
        }
        
        $existing = $this->getUserExistingEntity($user, $type);
        
        if ($existing && $existing->id !== $entityId) {
            return [
                'has_conflict' => true,
                'existing_id' => $existing->id,
                'existing_name' => $existing->getDisplayName() ?: 'Unknown',
                'type' => $type,
                'user_name' => $user->name ?: ($user->email ?: 'Unknown User'),
                'user_id' => $user->id,
            ];
        }
        
        return null;
    }

    /**
     * Unlink an entity from its owner (make it unclaimed again).
     */
    public function unlinkFromUser(Model $entity): bool
    {
        $ownerField = $entity->getOwnerUserIdField();
        
        $entity->update([
            $ownerField => null,
        ]);
        
        $entity->clearClaimData();

        return true;
    }

    /**
     * Send a claim invitation email to an unclaimed entity.
     */
    public function sendClaimInvitation(Model $entity): bool
    {
        $email = $entity->getClaimEmail();
        
        if (!$email) {
            return false;
        }

        try {
            Mail::to($email)->send(new \App\Mail\ClaimInvitationMail($entity));
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send claim invitation email: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send claim warning email to entity's contact email.
     */
    protected function sendClaimWarningEmail(Model $entity, User $claimant, ?Carbon $gracePeriodEndsAt): void
    {
        $email = $entity->getClaimEmail();
        
        if (!$email) {
            return;
        }

        try {
            // Use type-specific mail for artists (existing), generic for others
            if ($entity instanceof Artist) {
                Mail::to($email)->send(
                    new ArtistClaimWarningMail($entity, $claimant, $gracePeriodEndsAt)
                );
            } else {
                Mail::to($email)->send(
                    new ClaimWarningMail($entity, $claimant, $gracePeriodEndsAt)
                );
            }
        } catch (\Exception $e) {
            Log::error("Failed to send claim warning email to {$email}: " . $e->getMessage());
        }
    }

    /**
     * Assign appropriate role to user based on entity type.
     */
    protected function assignRoleForEntity(User $user, Model $entity): void
    {
        $type = $entity->getClaimableType();

        $roleMap = [
            'artist' => 'artist',
            'venue' => 'venue_owner',
            'organiser' => 'organiser',
            'event' => 'organiser',
        ];

        $role = $roleMap[$type] ?? null;

        if ($role && !$user->hasRole($role)) {
            $user->addRole($role);
        }
    }

    /**
     * Get counts of unclaimed entities by type.
     */
    public function getUnclaimedCounts(): array
    {
        return [
            'artist' => Artist::whereNull('user_id')->count(),
            'venue' => Venue::whereNull('user_id')->whereNull('owner_id')->count(),
            'event' => Event::whereNull('owner_id')->count(),
            'organiser' => Organiser::whereNull('user_id')->count(),
        ];
    }

    /**
     * Get total count of all unclaimed entities.
     */
    public function getTotalUnclaimedCount(): int
    {
        return array_sum($this->getUnclaimedCounts());
    }

    /**
     * Get pending claim counts by type.
     */
    public function getPendingClaimCounts(): array
    {
        return [
            'artist' => Artist::withPendingClaims()->count(),
            'venue' => Venue::withPendingClaims()->count(),
            'event' => Event::withPendingClaims()->count(),
            'organiser' => Organiser::withPendingClaims()->count(),
        ];
    }

    /**
     * Get dispute counts by type.
     */
    public function getDisputeCounts(): array
    {
        return [
            'artist' => Artist::withDisputes()->count(),
            'venue' => Venue::withDisputes()->count(),
            'event' => Event::withDisputes()->count(),
            'organiser' => Organiser::withDisputes()->count(),
        ];
    }
}

