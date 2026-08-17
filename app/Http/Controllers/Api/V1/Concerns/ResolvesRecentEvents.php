<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Collection;

trait ResolvesRecentEvents
{
    /**
     * Most recent past gigs for an artist (performing or page owner), newest first.
     *
     * @return Collection<int, Event>
     */
    protected function recentEventsForArtist(Artist $artist, int $limit = 5): Collection
    {
        return $this->recentEventsQuery($limit)
            ->where(function ($query) use ($artist) {
                $query->whereHas('artists', fn ($q) => $q->whereKey($artist->id))
                    ->orWhere(function ($own) use ($artist) {
                        $own->where('owner_type', 'artist')->where('owner_id', $artist->id);
                    })
                    ->orWhere(function ($own) use ($artist) {
                        $own->where('owner_type', Artist::class)->where('owner_id', $artist->id);
                    });
            })
            ->get();
    }

    /**
     * Most recent past gigs at a venue, newest first.
     *
     * @return Collection<int, Event>
     */
    protected function recentEventsForVenue(Venue $venue, int $limit = 5): Collection
    {
        return $this->recentEventsQuery($limit)
            ->where('venue_id', $venue->id)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Event>
     */
    protected function recentEventsQuery(int $limit)
    {
        return Event::query()
            ->with(['venue', 'artists', 'categories'])
            ->whereTakenPlace()
            ->whereNotCancelled()
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(max(1, $limit));
    }
}
