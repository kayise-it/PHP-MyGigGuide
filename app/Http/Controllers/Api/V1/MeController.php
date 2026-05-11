<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('roles');

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name')->values(),
            'is_active' => (bool) $user->is_active,
        ]);
    }

    public function favorites(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'events' => $user->favoriteEvents()->select('events.id', 'events.name')->orderBy('events.name')->get(),
            'venues' => $user->favoriteVenues()->select('venues.id', 'venues.name')->orderBy('venues.name')->get(),
            'artists' => $user->favoriteArtists()->select('artists.id', 'artists.stage_name')->orderBy('artists.stage_name')->get(),
            'organisers' => $user->favoriteOrganisers()->select('organisers.id', 'organisers.organisation_name')->orderBy('organisers.organisation_name')->get(),
        ]);
    }

    public function addFavorite(Request $request, string $type, int $id): JsonResponse
    {
        [$relation, $modelClass] = $this->resolveFavoriteType($type);
        if (! $relation) {
            return response()->json(['message' => 'Unsupported favorite type.'], 422);
        }

        $model = $modelClass::find($id);
        if (! $model) {
            return response()->json(['message' => ucfirst($type) . ' not found.'], 404);
        }

        $request->user()->{$relation}()->syncWithoutDetaching([$id]);

        return response()->json([
            'message' => ucfirst($type) . ' added to favorites.',
            'favorited' => true,
            'type' => $type,
            'id' => $id,
        ]);
    }

    public function removeFavorite(Request $request, string $type, int $id): JsonResponse
    {
        [$relation] = $this->resolveFavoriteType($type);
        if (! $relation) {
            return response()->json(['message' => 'Unsupported favorite type.'], 422);
        }

        $request->user()->{$relation}()->detach($id);

        return response()->json([
            'message' => ucfirst($type) . ' removed from favorites.',
            'favorited' => false,
            'type' => $type,
            'id' => $id,
        ]);
    }

    private function resolveFavoriteType(string $type): array
    {
        return match ($type) {
            'events' => ['favoriteEvents', Event::class],
            'venues' => ['favoriteVenues', Venue::class],
            'artists' => ['favoriteArtists', Artist::class],
            'organisers' => ['favoriteOrganisers', Organiser::class],
            default => [null, null],
        };
    }
}
