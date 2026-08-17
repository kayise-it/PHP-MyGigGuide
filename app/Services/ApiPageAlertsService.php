<?php

namespace App\Services;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ApiPageAlertsService
{
    use ResolvesStorageUrl;

    public function __construct(
        private readonly ArtistPageService $artistPages,
        private readonly VenuePageService $venuePages,
        private readonly EventCreationService $eventCreation,
    ) {}

    /**
     * Upcoming user-posted gigs that tag a claimed artist, venue, or organiser page the user owns.
     *
     * @return array{data: list<array<string, mixed>>, summary: array<string, int>, window_days: int, generated_at: string}
     */
    public function alertsForUser(User $user, ?Carbon $since = null, int $days = 30): array
    {
        $days = max(1, min($days, 90));

        $ownedArtistIds = $this->ownedArtistIds($user);
        $ownedVenueIds = $this->ownedVenueIds($user);
        $ownedOrganiserIds = $this->ownedOrganiserIds($user);

        if ($ownedArtistIds->isEmpty() && $ownedVenueIds->isEmpty() && $ownedOrganiserIds->isEmpty()) {
            return $this->emptyPayload($days);
        }

        $from = now()->toDateString();
        $to = now()->addDays($days - 1)->toDateString();

        $query = Event::query()
            ->userPosted()
            ->with(['venue:id,name,main_picture', 'artists:id,stage_name', 'owner'])
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->whereBetween('date', [$from, $to])
            ->where(function ($q) use ($ownedVenueIds, $ownedArtistIds, $ownedOrganiserIds) {
                $started = false;

                if ($ownedVenueIds->isNotEmpty()) {
                    $q->whereIn('venue_id', $ownedVenueIds);
                    $started = true;
                }

                if ($ownedArtistIds->isNotEmpty()) {
                    $method = $started ? 'orWhereHas' : 'whereHas';
                    $q->{$method}('artists', fn ($artistQuery) => $artistQuery->whereIn('artists.id', $ownedArtistIds));
                    $started = true;
                }

                if ($ownedOrganiserIds->isNotEmpty()) {
                    $method = $started ? 'orWhere' : 'where';
                    $q->{$method}(function ($organiserQuery) use ($ownedOrganiserIds) {
                        $organiserQuery->whereIn('owner_id', $ownedOrganiserIds)
                            ->where(function ($typeQuery) {
                                $typeQuery->where('owner_type', 'organiser')
                                    ->orWhere('owner_type', Organiser::class)
                                    ->orWhere('owner_type', 'App\Models\Organiser');
                            });
                    });
                }
            });

        if ($since !== null) {
            $query->where('events.created_at', '>=', $since);
        }

        $events = $query
            ->orderBy('date')
            ->orderBy('time')
            ->limit(50)
            ->get()
            ->filter(fn (Event $event) => ! $this->eventCreation->userOwnsEvent($user, $event));

        $data = $events
            ->map(fn (Event $event) => $this->serializeEvent(
                $event,
                $ownedVenueIds,
                $ownedArtistIds,
                $ownedOrganiserIds,
            ))
            ->values()
            ->all();

        $newCount = collect($data)->count();

        return [
            'data' => $data,
            'summary' => [
                'total' => count($data),
                'new_count' => $newCount,
            ],
            'window_days' => $days,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return Collection<int, int>
     */
    private function ownedArtistIds(User $user): Collection
    {
        $user->loadMissing('artist');

        if (! $user->artist || ! $this->artistPages->userCanEditPage($user, $user->artist)) {
            return collect();
        }

        return collect([(int) $user->artist->id]);
    }

    /**
     * @return Collection<int, int>
     */
    private function ownedVenueIds(User $user): Collection
    {
        $user->loadMissing('ownedVenues');

        $candidateVenueIds = collect()
            ->merge(Venue::query()->where('user_id', $user->id)->pluck('id'))
            ->merge($user->ownedVenues()->pluck('venues.id'))
            ->unique()
            ->filter()
            ->values();

        if ($candidateVenueIds->isEmpty()) {
            return collect();
        }

        return Venue::query()
            ->whereIn('id', $candidateVenueIds)
            ->get()
            ->filter(fn (Venue $venue) => $this->venuePages->userCanEditPage($user, $venue))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values();
    }

    /**
     * @return Collection<int, int>
     */
    private function ownedOrganiserIds(User $user): Collection
    {
        $user->loadMissing('organiser');

        if (! $user->organiser || $user->organiser->claim_status !== 'approved') {
            return collect();
        }

        return collect([(int) $user->organiser->id]);
    }

    /**
     * @return array{data: list<array<string, mixed>>, summary: array<string, int>, window_days: int, generated_at: string}
     */
    private function emptyPayload(int $days): array
    {
        return [
            'data' => [],
            'summary' => [
                'total' => 0,
                'new_count' => 0,
            ],
            'window_days' => $days,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, int>  $ownedVenueIds
     * @param  Collection<int, int>  $ownedArtistIds
     * @param  Collection<int, int>  $ownedOrganiserIds
     * @return array<string, mixed>
     */
    private function serializeEvent(
        Event $event,
        Collection $ownedVenueIds,
        Collection $ownedArtistIds,
        Collection $ownedOrganiserIds,
    ): array {
        $reasons = [];
        $labels = [];

        if ($event->venue_id !== null && $ownedVenueIds->contains($event->venue_id) && $event->venue) {
            $reasons[] = 'venue';
            $labels[] = $event->venue->name;
        }

        foreach ($event->artists as $artist) {
            if (! $ownedArtistIds->contains($artist->id)) {
                continue;
            }
            $reasons[] = 'artist';
            $labels[] = $artist->stage_name;
        }

        if ($ownedOrganiserIds->contains((int) $event->owner_id)
            && in_array($event->owner_type, ['organiser', Organiser::class, 'App\Models\Organiser'], true)) {
            $reasons[] = 'organiser';
            $owner = $event->owner;
            if ($owner instanceof Organiser) {
                $labels[] = $owner->name;
            }
        }

        $reasons = array_values(array_unique($reasons));
        $labels = array_values(array_unique($labels));

        $posterUrl = self::publicStorageUrl($event->poster);
        if ($posterUrl === null && $event->venue) {
            $posterUrl = self::publicStorageUrl($event->venue->main_picture);
        }

        return [
            'id' => $event->id,
            'name' => $event->name,
            'date' => $event->date?->toIso8601String(),
            'time' => $event->time ? $event->time->format('H:i:s') : null,
            'poster_url' => $posterUrl,
            'poster_card_url' => self::publicStorageUrl($event->poster_card),
            'venue' => $event->venue ? [
                'id' => $event->venue->id,
                'name' => $event->venue->name,
            ] : null,
            'match_reasons' => $reasons,
            'match_labels' => $labels,
            'is_reminder' => false,
            'created_at' => $event->created_at?->toIso8601String(),
        ];
    }
}
