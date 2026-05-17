<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Collection;

trait ResolvesUpcomingEvents
{
    /**
     * Upcoming / ongoing events in the next [$days] calendar days (inclusive).
     *
     * @return Collection<int, Event>
     */
    protected function upcomingEventsForArtist(Artist $artist, int $days = 90): Collection
    {
        return $this->upcomingEventsQuery($days)
            ->whereHas('artists', fn ($q) => $q->whereKey($artist->id))
            ->limit(100)
            ->get();
    }

    /**
     * @return Collection<int, Event>
     */
    protected function upcomingEventsForVenue(Venue $venue, int $days = 90): Collection
    {
        return $this->upcomingEventsQuery($days)
            ->where('venue_id', $venue->id)
            ->limit(100)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Event>
     */
    protected function upcomingEventsQuery(int $days)
    {
        $from = now()->toDateString();
        $to = now()->addDays(max(0, $days - 1))->toDateString();

        return Event::query()
            ->with(['venue', 'artists', 'categories'])
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->orderBy('time');
    }
}
