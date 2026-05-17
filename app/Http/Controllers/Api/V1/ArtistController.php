<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\ResolvesUpcomingEvents;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArtistResource;
use App\Models\Artist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArtistController extends Controller
{
    use ResolvesUpcomingEvents;
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Artist::query()->orderBy('stage_name');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('stage_name', 'like', "%{$term}%")
                    ->orWhere('real_name', 'like', "%{$term}%")
                    ->orWhere('genre', 'like', "%{$term}%");
            });
        }

        $perPage = min((int) $request->get('per_page', 30), 100);

        return ArtistResource::collection($query->paginate($perPage));
    }

    public function show(Artist $artist): ArtistResource
    {
        $artist->load('genres');
        $artist->setRelation('upcomingEvents', $this->upcomingEventsForArtist($artist));

        return new ArtistResource($artist);
    }
}
