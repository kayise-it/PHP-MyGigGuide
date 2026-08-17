<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\AppliesDirectorySort;
use App\Http\Controllers\Api\V1\Concerns\ResolvesPostedEvents;
use App\Http\Controllers\Api\V1\Concerns\ResolvesRecentEvents;
use App\Http\Controllers\Api\V1\Concerns\ResolvesUpcomingEvents;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArtistResource;
use App\Models\Artist;
use App\Services\ArtistDirectorySearch;
use App\Services\ArtistPageService;
use App\Services\CrowdSourceArtistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArtistController extends Controller
{
    use AppliesDirectorySort;
    use ResolvesPostedEvents;
    use ResolvesRecentEvents;
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
            ArtistDirectorySearch::apply($query, $validated['search']);
        }

        $this->applyArtistDirectorySort($query, $validated['sort'] ?? 'name');

        $perPage = min((int) ($validated['per_page'] ?? $request->get('per_page', 30)), 100);

        return ArtistResource::collection($query->paginate($perPage));
    }

    public function show(Artist $artist): ArtistResource
    {
        $artist->load(['genres', 'youtubeVideos']);
        $artist->loadCount('ratings');
        $artist->loadAvg('ratings', 'rating');
        $artist->setRelation('upcomingEvents', $this->upcomingEventsForArtist($artist));
        $artist->setRelation('postedEvents', $this->postedEventsForArtist($artist));
        $artist->setRelation('recentEvents', $this->recentEventsForArtist($artist));

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

    /**
     * Update an artist page the user owns (profile fields and/or videos). Requires Sanctum.
     */
    public function update(Request $request, Artist $artist, ArtistPageService $artistPages): JsonResponse
    {
        $updated = $artistPages->updateFromRequest($request, $artist, $request->user());

        return (new ArtistResource($updated))
            ->additional(['message' => 'Artist updated successfully.'])
            ->response();
    }
}
