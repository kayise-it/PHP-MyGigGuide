<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AppliesDirectorySort;
use App\Http\Controllers\Api\V1\Concerns\ResolvesUpcomingEvents;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArtistResource;
use App\Models\Artist;
use App\Services\CrowdSourceArtistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArtistController extends Controller
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

        $query = Artist::query()
            ->withCount(['ratings', 'events'])
            ->withAvg('ratings', 'rating');

        if (! empty($validated['search'])) {
            $term = $validated['search'];
            $query->where(function ($q) use ($term) {
                $q->where('stage_name', 'like', "%{$term}%")
                    ->orWhere('real_name', 'like', "%{$term}%")
                    ->orWhere('genre', 'like', "%{$term}%");
            });
        }

        $this->applyArtistDirectorySort($query, $validated['sort'] ?? 'name');

        $perPage = min((int) ($validated['per_page'] ?? $request->get('per_page', 30)), 100);

        return ArtistResource::collection($query->paginate($perPage));
    }

    public function show(Artist $artist): ArtistResource
    {
        $artist->load('genres');
        $artist->loadCount('ratings');
        $artist->loadAvg('ratings', 'rating');
        $artist->setRelation('upcomingEvents', $this->upcomingEventsForArtist($artist));

        return new ArtistResource($artist);
    }

    /**
     * Quick-create artist when posting an event (Sanctum + create-events).
     */
    public function store(Request $request, CrowdSourceArtistService $crowdSource): JsonResponse
    {
        $result = $crowdSource->createFromRequest($request, $request->user());

        return (new ArtistResource($result['artist']))
            ->additional([
                'message' => $result['existing']
                    ? 'Artist already exists — using existing profile.'
                    : 'Artist created successfully.',
                'existing' => $result['existing'],
            ])
            ->response()
            ->setStatusCode($result['existing'] ? 200 : 201);
    }
}
