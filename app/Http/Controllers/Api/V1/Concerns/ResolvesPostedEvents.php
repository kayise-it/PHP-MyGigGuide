<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use Illuminate\Database\Eloquent\Collection;

trait ResolvesPostedEvents
{
    /**
     * Upcoming / ongoing gigs this artist Page listed (event owner).
     *
     * @return Collection<int, Event>
     */
    protected function postedEventsForArtist(Artist $artist, int $days = 90): Collection
    {
        return $this->postedEventsQuery($days)
            ->where(function ($query) use ($artist) {
                $this->applyOwnerScope($query, 'artist', $artist->id);
                $this->applyLegacyUserIdOwnerScope($query, 'artist', $artist->user_id);
            })
            ->limit(100)
            ->get();
    }

    /**
     * @return Collection<int, Event>
     */
    protected function postedEventsForOrganiser(Organiser $organiser, int $days = 90): Collection
    {
        return $this->postedEventsQuery($days)
            ->where(function ($query) use ($organiser) {
                $this->applyOwnerScope($query, 'organiser', $organiser->id);
                $this->applyLegacyUserIdOwnerScope($query, 'organiser', $organiser->user_id);
            })
            ->limit(100)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Event>
     */
    protected function postedEventsQuery(int $days)
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

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Event>  $query
     */
    protected function applyOwnerScope($query, string $shortType, int $ownerId): void
    {
        $types = match ($shortType) {
            'artist' => ['artist', Artist::class, 'App\Models\Artist'],
            'organiser' => ['organiser', Organiser::class, 'App\Models\Organiser'],
            default => [$shortType],
        };

        $query->whereIn('owner_type', $types)
            ->where('owner_id', $ownerId);
    }

    /**
     * Events filed before May 2026 stored user id while owner_type was artist/organiser.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Event>  $query
     */
    protected function applyLegacyUserIdOwnerScope($query, string $shortType, ?int $userId): void
    {
        if ($userId === null || $userId <= 0) {
            return;
        }

        $types = match ($shortType) {
            'artist' => ['artist', Artist::class, 'App\Models\Artist'],
            'organiser' => ['organiser', Organiser::class, 'App\Models\Organiser'],
            default => [$shortType],
        };

        $query->orWhere(function ($legacy) use ($types, $userId) {
            $legacy->whereIn('owner_type', $types)
                ->where('owner_id', $userId);
        });
    }
}
