<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesUpcomingEvents;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\VenueResource;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VenueController extends Controller
{
    use ResolvesUpcomingEvents;
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Venue::query()->orderBy('name');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('address', 'like', "%{$term}%");
            });
        }

        $perPage = min((int) $request->get('per_page', 30), 100);

        return VenueResource::collection($query->paginate($perPage));
    }

    public function show(Venue $venue): VenueResource
    {
        $venue->setRelation('upcomingEvents', $this->upcomingEventsForVenue($venue));

        return new VenueResource($venue);
    }
}
