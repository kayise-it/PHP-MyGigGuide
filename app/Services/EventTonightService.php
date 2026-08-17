<?php

namespace App\Services;

use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use App\Models\Event;
use App\Models\EventCheckIn;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Support\Collection;

class EventTonightService
{
    use ResolvesStorageUrl;

    public function __construct(
        private readonly LiveSessionService $liveSessions,
    ) {}

    public function assertEventEligibleForCheckIn(Event $event): void
    {
        abort_if($event->isCancelled(), 422, 'This event is cancelled.');

        if ($event->date === null) {
            return;
        }

        $start = $event->date->copy()->startOfDay();
        $end = $event->date->copy()->endOfDay()->addHours(6);

        abort_if(now()->lt($start), 422, 'Check-in opens on the day of the gig.');
        abort_if(now()->gt($end), 422, 'Check-in has closed for this gig.');
    }

    public function checkIn(User $user, Event $event): EventCheckIn
    {
        $this->assertEventEligibleForCheckIn($event);

        $existing = EventCheckIn::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return EventCheckIn::query()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'checked_in_at' => now(),
        ]);
    }

    public function removeCheckIn(User $user, Event $event): void
    {
        EventCheckIn::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    public function isCheckedIn(User $user, Event $event): bool
    {
        return EventCheckIn::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function checkInCount(Event $event): int
    {
        return EventCheckIn::query()
            ->where('event_id', $event->id)
            ->count();
    }

    /**
     * @return array<string, mixed>
     */
    public function boardForEvent(Event $event, ?User $user = null): array
    {
        $event->loadMissing(['venue', 'artists']);

        $liveSessions = $this->liveSessions->liveSessionsForEvent($event)
            ->keyBy('artist_id');

        $artists = $event->artists->map(function ($artist) use ($liveSessions) {
            $session = $liveSessions->get($artist->id);

            return [
                'id' => $artist->id,
                'stage_name' => $artist->stage_name,
                'genre' => $artist->genre,
                'profile_picture_url' => self::publicStorageUrl($artist->profile_picture),
                'live_session' => $session
                    ? $this->liveSessions->serializeSession($session)
                    : null,
            ];
        })->values()->all();

        return [
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'date' => $event->date?->toIso8601String(),
                'time' => $event->time ? $event->time->format('H:i:s') : null,
                'venue' => $event->venue ? [
                    'id' => $event->venue->id,
                    'name' => $event->venue->name,
                    'city' => $event->venue->city,
                ] : null,
            ],
            'check_in_count' => $this->checkInCount($event),
            'checked_in' => $user ? $this->isCheckedIn($user, $event) : false,
            'artists' => $artists,
            'live_count' => $liveSessions->count(),
        ];
    }

    /**
     * Events at a venue happening today (for venue page "tonight").
     *
     * @return Collection<int, Event>
     */
    public function tonightEventsAtVenue(Venue $venue): Collection
    {
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        return Event::query()
            ->where('venue_id', $venue->id)
            ->whereNotCancelled()
            ->whereDate('date', '>=', $start->toDateString())
            ->whereDate('date', '<=', $end->toDateString())
            ->with(['artists', 'venue'])
            ->orderBy('date')
            ->orderBy('time')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function tonightAtVenue(Venue $venue, ?User $user = null): array
    {
        $events = $this->tonightEventsAtVenue($venue);

        return [
            'venue' => [
                'id' => $venue->id,
                'name' => $venue->name,
                'city' => $venue->city,
            ],
            'events' => $events
                ->map(fn (Event $event) => $this->boardForEvent($event, $user))
                ->values()
                ->all(),
            'event_count' => $events->count(),
        ];
    }
}
