<?php

namespace App\Services;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ApiFavoriteUpdatesService
{
    use ResolvesStorageUrl;

    /**
     * Upcoming gigs matching the user's saved artists, venues, or events.
     *
     * When [$since] is set, returns events created on/after that time, plus saved-event
     * reminders in the next seven days (even if the listing is older).
     *
     * @return array{data: list<array<string, mixed>>, summary: array<string, int>, window_days: int, generated_at: string}
     */
    public function updatesForUser(User $user, ?Carbon $since = null, int $days = 30): array
    {
        $days = max(1, min($days, 90));

        $venueIds = $user->favoriteVenues()->pluck('venues.id');
        $artistIds = $user->favoriteArtists()->pluck('artists.id');
        $favoriteEventIds = $user->favoriteEvents()->pluck('events.id');

        if ($venueIds->isEmpty() && $artistIds->isEmpty() && $favoriteEventIds->isEmpty()) {
            return $this->emptyPayload($days);
        }

        $from = now()->toDateString();
        $to = now()->addDays($days - 1)->toDateString();

        $query = Event::query()
            ->with(['venue:id,name,main_picture', 'artists:id,stage_name'])
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->whereBetween('date', [$from, $to])
            ->where(function ($q) use ($venueIds, $artistIds, $favoriteEventIds) {
                $started = false;

                if ($venueIds->isNotEmpty()) {
                    $q->whereIn('venue_id', $venueIds);
                    $started = true;
                }

                if ($artistIds->isNotEmpty()) {
                    $method = $started ? 'orWhereHas' : 'whereHas';
                    $q->{$method}('artists', fn ($artistQuery) => $artistQuery->whereIn('artists.id', $artistIds));
                    $started = true;
                }

                if ($favoriteEventIds->isNotEmpty()) {
                    $started ? $q->orWhereIn('id', $favoriteEventIds) : $q->whereIn('id', $favoriteEventIds);
                }
            });

        if ($since !== null) {
            $reminderEnd = now()->addDays(7)->toDateString();
            $query->where(function ($q) use ($since, $favoriteEventIds, $reminderEnd) {
                $q->where('events.created_at', '>=', $since);

                if ($favoriteEventIds->isNotEmpty()) {
                    $q->orWhere(function ($reminderQuery) use ($favoriteEventIds, $reminderEnd) {
                        $reminderQuery->whereIn('events.id', $favoriteEventIds)
                            ->whereDate('events.date', '<=', $reminderEnd);
                    });
                }
            });
        }

        $events = $query
            ->orderBy('date')
            ->orderBy('time')
            ->limit(50)
            ->get();

        $data = $events
            ->map(fn (Event $event) => $this->serializeEvent(
                $event,
                $venueIds,
                $artistIds,
                $favoriteEventIds,
                $since,
            ))
            ->values()
            ->all();

        $newCount = collect($data)->where('is_reminder', false)->count();
        $reminderCount = collect($data)->where('is_reminder', true)->count();

        return [
            'data' => $data,
            'summary' => [
                'total' => count($data),
                'new_count' => $newCount,
                'reminder_count' => $reminderCount,
            ],
            'window_days' => $days,
            'generated_at' => now()->toIso8601String(),
        ];
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
                'reminder_count' => 0,
            ],
            'window_days' => $days,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @param  Collection<int, int|string>  $venueIds
     * @param  Collection<int, int|string>  $artistIds
     * @param  Collection<int, int|string>  $favoriteEventIds
     * @return array<string, mixed>
     */
    private function serializeEvent(
        Event $event,
        Collection $venueIds,
        Collection $artistIds,
        Collection $favoriteEventIds,
        ?Carbon $since,
    ): array {
        $reasons = [];
        $labels = [];

        if ($event->venue_id !== null && $venueIds->contains($event->venue_id) && $event->venue) {
            $reasons[] = 'venue';
            $labels[] = $event->venue->name;
        }

        foreach ($event->artists as $artist) {
            if (! $artistIds->contains($artist->id)) {
                continue;
            }
            $reasons[] = 'artist';
            $labels[] = $artist->stage_name;
        }

        $isReminder = false;
        if ($favoriteEventIds->contains($event->id)) {
            $reasons[] = 'saved_event';
            if ($since !== null && $event->created_at !== null && $event->created_at->lt($since)) {
                $isReminder = true;
                $labels[] = 'Saved gig reminder';
            } else {
                $labels[] = 'Saved gig';
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
            'is_reminder' => $isReminder,
            'created_at' => $event->created_at?->toIso8601String(),
        ];
    }
}
