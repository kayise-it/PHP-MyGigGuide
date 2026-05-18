<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\Venue;
use App\Services\UserFirebaseLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class MeController extends Controller
{
    public function __construct(
        private readonly UserFirebaseLinkService $firebaseLink,
    ) {}

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
            'firebase_linked' => $user->firebase_uid !== null,
        ]);
    }

    public function linkFirebase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        try {
            $user = $this->firebaseLink->linkAuthenticatedUser(
                $request->user(),
                $validated['id_token'],
            );
        } catch (InvalidArgumentException|ValidationException $e) {
            $message = $e instanceof ValidationException
                ? collect($e->errors())->flatten()->first()
                : $e->getMessage();

            return response()->json(['message' => $message], 422);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        return response()->json([
            'message' => 'Firebase account linked to your website profile.',
            'firebase_linked' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'firebase_linked' => true,
            ],
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
