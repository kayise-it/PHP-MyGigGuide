<?php

namespace App\Services;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;

class VenueMapQuery
{
    /**
     * Venues with coordinates for map pins. Optional great-circle filter when lat/lng supplied.
     */
    public static function build(?float $lat, ?float $lng, float $radiusKm, int $limit): Builder
    {
        $query = Venue::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount('events');

        if ($lat !== null && $lng !== null) {
            $query
                ->select('venues.*')
                ->selectRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance_km',
                    [$lat, $lng, $lat]
                )
                ->having('distance_km', '<=', $radiusKm)
                ->orderBy('distance_km');
        } else {
            $query->orderBy('name');
        }

        return $query->limit($limit);
    }
}
