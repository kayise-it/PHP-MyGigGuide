<?php

namespace App\Services;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ApiMeProfileService
{
    use ResolvesStorageUrl;

    public function __construct(
        private readonly ClaimService $claimService,
        private readonly ArtistPageService $artistPages,
        private readonly VenuePageService $venuePages,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function ownedPages(User $user): array
    {
        $pages = collect();

        $user->loadMissing(['artist', 'organiser', 'ownedVenues']);

        if ($user->artist && $this->artistPages->userCanEditPage($user, $user->artist)) {
            $pages->push($this->serializePage($user->artist));
        }

        if ($user->organiser && $user->organiser->claim_status === 'approved') {
            $pages->push($this->serializePage($user->organiser));
        }

        $candidateVenueIds = collect()
            ->merge(Venue::query()->where('user_id', $user->id)->pluck('id'))
            ->merge($user->ownedVenues()->pluck('venues.id'))
            ->merge(Venue::query()->where('pending_claim_user_id', $user->id)->pluck('id'))
            ->unique()
            ->filter()
            ->values();

        if ($candidateVenueIds->isNotEmpty()) {
            Venue::query()
                ->whereIn('id', $candidateVenueIds)
                ->orderBy('name')
                ->get()
                ->filter(fn (Venue $venue) => $this->venuePages->userCanEditPage($user, $venue))
                ->each(function (Venue $venue) use ($pages) {
                    $pages->push($this->serializePage($venue));
                });
        }

        return $pages
            ->unique(fn (array $row) => ($row['type'] ?? '').':'.($row['id'] ?? ''))
            ->values()
            ->all();
    }

    /**
     * Contributor counts for the Me account tab.
     *
     * @return array<string, mixed>
     */
    public function contributorStats(User $user): array
    {
        $today = now()->startOfDay();
        $base = $this->managedEventsQuery($user);

        return [
            'events_posted' => (clone $base)->count(),
            'events_upcoming' => (clone $base)
                ->whereNotNull('date')
                ->where('date', '>=', $today)
                ->count(),
            'events_past' => (clone $base)
                ->whereNotNull('date')
                ->where('date', '<', $today)
                ->count(),
            'pages_managed' => count($this->ownedPages($user)),
            'member_since' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * Events the user owns (direct user, artist, or organiser ownership).
     *
     * @return list<array<string, mixed>>
     */
    public function managedEvents(User $user): array
    {
        $today = now()->startOfDay();

        return $this->managedEventsQuery($user)
            ->with('venue:id,name,main_picture')
            ->orderByRaw('CASE WHEN date IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('date')
            ->limit(500)
            ->get()
            ->map(fn (Event $event) => [
                'id' => $event->id,
                'name' => $event->name,
                'date' => $event->date?->toIso8601String(),
                'image_url' => self::publicStorageUrl($event->poster)
                    ?? self::publicStorageUrl($event->venue?->main_picture),
                'venue_name' => $event->venue?->name,
                'user_can_edit' => true,
                'is_past' => $event->date !== null && $event->date->lt($today),
            ])
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

    private function managedEventsQuery(User $user): Builder
    {
        $user->loadMissing(['artist', 'organiser']);

        $query = Event::query();

        $query->where(function ($q) use ($user) {
            $q->where(function ($q) use ($user) {
                $q->where('owner_type', 'user')->where('owner_id', $user->id);
            })->orWhere(function ($q) use ($user) {
                $q->where('owner_type', User::class)->where('owner_id', $user->id);
            });

            if ($user->artist) {
                $artistId = $user->artist->id;
                $q->orWhere(function ($q) use ($artistId) {
                    $q->where('owner_type', 'artist')->where('owner_id', $artistId);
                })->orWhere(function ($q) use ($artistId) {
                    $q->where('owner_type', Artist::class)->where('owner_id', $artistId);
                });
            }

            if ($user->organiser) {
                $organiserId = $user->organiser->id;
                $q->orWhere(function ($q) use ($organiserId) {
                    $q->where('owner_type', 'organiser')->where('owner_id', $organiserId);
                })->orWhere(function ($q) use ($organiserId) {
                    $q->where('owner_type', Organiser::class)->where('owner_id', $organiserId);
                });
            }
        });

        return $query;
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
            'edit_url' => $this->editUrlFor($entity),
            'image_url' => $this->imageUrlFor($entity),
            'claim_url' => $claimUrl,
            'claimable' => $claimable,
        ];
    }

    private function editUrlFor(Model $entity): string
    {
        return match ($entity->getClaimableType()) {
            'artist' => route('profile.edit'),
            'venue' => route('venues.edit', $entity),
            'organiser' => route('organisers.edit', $entity),
            default => route('dashboard'),
        };
    }

    private function imageUrlFor(Model $entity): ?string
    {
        return match ($entity->getClaimableType()) {
            'artist' => self::publicStorageUrl($entity->profile_picture ?? null),
            'venue' => self::publicStorageUrl($entity->main_picture ?? null),
            'organiser' => self::publicStorageUrl($entity->logo ?? null),
            default => null,
        };
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
