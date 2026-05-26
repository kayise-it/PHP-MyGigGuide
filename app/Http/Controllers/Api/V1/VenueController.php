<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AppliesDirectorySort;
use App\Http\Controllers\Api\V1\Concerns\ResolvesUpcomingEvents;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\VenueResource;
use App\Models\Venue;
use App\Services\CrowdSourceVenueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VenueController extends Controller
{
    use AppliesDirectorySort;
    use ResolvesUpcomingEvents;

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', 'in:name,rating,events,newest'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Venue::query()
            ->withCount(['ratings', 'events'])
            ->withAvg('ratings', 'rating');

        if (! empty($validated['search'])) {
            $term = $validated['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('address', 'like', "%{$term}%");
            });
        }

        $this->applyVenueDirectorySort($query, $validated['sort'] ?? 'name');

        $perPage = min((int) ($validated['per_page'] ?? $request->get('per_page', 30)), 100);

        return VenueResource::collection($query->paginate($perPage));
    }

    public function show(Venue $venue): VenueResource
    {
        $venue->loadCount('ratings');
        $venue->loadAvg('ratings', 'rating');
        $venue->setRelation('upcomingEvents', $this->upcomingEventsForVenue($venue));

        return new VenueResource($venue);
    }

    /**
     * Quick-create venue when posting an event (Sanctum + create-events).
     */
    public function store(Request $request, CrowdSourceVenueService $crowdSource): JsonResponse
    {
        $result = $crowdSource->createFromRequest($request, $request->user());

        return (new VenueResource($result['venue']))
            ->additional([
                'message' => $result['existing']
                    ? 'Venue already exists — using existing listing.'
                    : 'Venue created successfully.',
                'existing' => $result['existing'],
            ])
            ->response()
            ->setStatusCode($result['existing'] ? 200 : 201);
    }
}
