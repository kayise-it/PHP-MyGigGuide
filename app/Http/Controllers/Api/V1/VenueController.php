<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AppliesDirectorySort;
use App\Http\Controllers\Api\V1\Concerns\ResolvesRecentEvents;
use App\Http\Controllers\Api\V1\Concerns\ResolvesUpcomingEvents;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\VenueMapResource;
use App\Http\Resources\Api\V1\VenueResource;
use App\Models\Venue;
use App\Services\CrowdSourceVenueService;
use App\Services\VenueDirectorySearch;
use App\Services\VenueMapQuery;
use App\Services\VenuePageService;
use App\Services\VenueResolveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VenueController extends Controller
{
    use AppliesDirectorySort;
    use ResolvesRecentEvents;
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
            VenueDirectorySearch::apply($query, $validated['search']);
        }

        $this->applyVenueDirectorySort($query, $validated['sort'] ?? 'name');

        $perPage = min((int) ($validated['per_page'] ?? $request->get('per_page', 30)), 100);

        return VenueResource::collection($query->paginate($perPage));
    }

    /**
     * Geocoded venues for the mobile map layer (`GET /api/v1/venues/map`).
     */
    public function map(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'radius_km' => ['nullable', 'numeric', 'min:1', 'max:200'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $lat = isset($validated['lat']) ? (float) $validated['lat'] : null;
        $lng = isset($validated['lng']) ? (float) $validated['lng'] : null;
        $radiusKm = (float) ($validated['radius_km'] ?? 50);
        $limit = (int) ($validated['limit'] ?? 200);

        $venues = VenueMapQuery::build($lat, $lng, $radiusKm, $limit)->get();

        return VenueMapResource::collection($venues);
    }

    public function show(Venue $venue): VenueResource
    {
        $venue->load(['youtubeVideos']);
        $venue->loadCount('ratings');
        $venue->loadAvg('ratings', 'rating');
        $venue->setRelation('upcomingEvents', $this->upcomingEventsForVenue($venue));
        $venue->setRelation('recentEvents', $this->recentEventsForVenue($venue));

        return new VenueResource($venue);
    }

    /**
     * Resolve a Google Place to an existing venue or create one (Sanctum + create-events).
     */
    public function resolve(Request $request, VenueResolveService $resolver): JsonResponse
    {
        try {
            $result = $resolver->resolveFromRequest($request, $request->user());
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 503);
        }

        $messages = [
            'google_place_id' => 'Venue matched by Google Place ID.',
            'proximity' => 'Existing venue matched nearby.',
            'name_address' => 'Existing venue matched by name and address.',
            'created' => 'Venue created from Google Place.',
        ];

        return $result['resource']
            ->additional([
                'message' => $messages[$result['match_method']] ?? 'Venue resolved.',
                'existing' => $result['existing'],
                'match_method' => $result['match_method'],
            ])
            ->response()
            ->setStatusCode($result['existing'] ? 200 : 201);
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

    /**
     * Update a venue page the user owns. Requires Sanctum.
     */
    public function update(Request $request, Venue $venue, VenuePageService $venuePages): JsonResponse
    {
        $updated = $venuePages->updateFromRequest($request, $venue, $request->user());

        return (new VenueResource($updated))
            ->additional(['message' => 'Venue updated successfully.'])
            ->response();
    }
}
