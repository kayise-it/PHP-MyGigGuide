<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AppliesDirectorySort
{
    protected function applyArtistDirectorySort(Builder $query, ?string $sort): void
    {
        $sort = $sort ?: 'name';

        match ($sort) {
            'rating' => $query->orderByDesc('ratings_avg_rating'),
            'events' => $query->orderByDesc('events_count'),
            'newest' => $query->orderByDesc('created_at'),
            default => $query->orderBy('stage_name'),
        };
    }

    protected function applyVenueDirectorySort(Builder $query, ?string $sort): void
    {
        $sort = $sort ?: 'name';

        match ($sort) {
            'rating' => $query->orderByDesc('ratings_avg_rating'),
            'events' => $query->orderByDesc('events_count'),
            'newest' => $query->orderByDesc('created_at'),
            default => $query->orderBy('name'),
        };
    }
}
