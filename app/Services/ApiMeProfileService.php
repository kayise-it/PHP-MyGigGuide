<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Model;

class ApiMeProfileService
{
    public function __construct(
        private readonly ClaimService $claimService,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function ownedPages(User $user): array
    {
        $pages = collect();

        $user->loadMissing(['artist', 'organiser', 'ownedVenues']);

        if ($user->artist && $user->artist->claim_status === 'approved') {
            $pages->push($this->serializePage($user->artist));
        }

        if ($user->organiser && $user->organiser->claim_status === 'approved') {
            $pages->push($this->serializePage($user->organiser));
        }

        Venue::query()
            ->where('claim_status', 'approved')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
                $ownedIds = $user->ownedVenues()->pluck('venues.id');
                if ($ownedIds->isNotEmpty()) {
                    $query->orWhereIn('id', $ownedIds);
                }
            })
            ->orderBy('name')
            ->each(function (Venue $venue) use ($pages) {
                $pages->push($this->serializePage($venue));
            });

        return $pages
            ->unique(fn (array $row) => ($row['type'] ?? '').':'.($row['id'] ?? ''))
            ->values()
            ->all();
    }

    /**
     * Unclaimed listings whose contact email matches the user's account email.
     *
     * @return list<array<string, mixed>>
     */
    public function claimablePages(User $user): array
    {
        if (! $user->email) {
            return [];
        }

        return $this->claimService
            ->findUnclaimedByEmail($user->email)
            ->filter(fn (Model $entity) => $this->isClaimableListing($entity))
            ->map(fn (Model $entity) => $this->serializePage($entity, claimable: true))
            ->values()
            ->all();
    }

    private function isClaimableListing(Model $entity): bool
    {
        if (! method_exists($entity, 'getPublicOwnershipStatus')) {
            return false;
        }

        return $entity->getPublicOwnershipStatus() === 'unclaimed';
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePage(Model $entity, bool $claimable = false): array
    {
        $type = $entity->getClaimableType();
        $websiteUrl = $this->websiteUrlFor($entity);
        $claimEmail = $entity->getClaimEmail();
        $usableEmail = filled($claimEmail)
            && ! str_contains(strtolower((string) $claimEmail), '@example.local');

        $claimUrl = route('register', array_filter([
            'email' => $usableEmail ? $claimEmail : null,
            'continue' => $websiteUrl,
        ]));

        return [
            'type' => $type,
            'id' => $entity->id,
            'name' => $entity->getDisplayName(),
            'ownership_status' => $entity->getPublicOwnershipStatus(),
            'website_url' => $websiteUrl,
            'claim_url' => $claimUrl,
            'claimable' => $claimable,
        ];
    }

    private function websiteUrlFor(Model $entity): string
    {
        return match ($entity->getClaimableType()) {
            'artist' => route('artists.show', $entity),
            'venue' => route('venues.show', $entity),
            'organiser' => route('organisers.show', $entity),
            default => url('/'),
        };
    }
}
