<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Concerns\ResolvesStorageUrl;
use App\Models\Artist;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\User;
use App\Models\Venue;
use App\Services\ApiMeProfileService;
use App\Services\AppWebSessionService;
use App\Services\ClaimService;
use App\Services\UserFirebaseLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class MeController extends Controller
{
    use ResolvesStorageUrl;

    public function __construct(
        private readonly UserFirebaseLinkService $firebaseLink,
        private readonly ApiMeProfileService $meProfile,
        private readonly ClaimService $claimService,
        private readonly AppWebSessionService $webSession,
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
            'email_verified' => $user->email_verified_at !== null,
            'firebase_linked' => $user->firebase_uid !== null,
            'owned_pages' => $this->meProfile->ownedPages($user),
            'claimable_pages' => $this->meProfile->claimablePages($user),
            'website_claim_url' => route('register'),
        ]);
    }

    public function createWebSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'redirect' => ['nullable', 'string', 'max:2048'],
        ]);

        $user = $request->user();
        if (! $user->is_active) {
            return response()->json(['message' => 'Account is inactive.'], 403);
        }

        $issued = $this->webSession->issue($user);
        $redirect = $this->webSession->sanitizeRedirect($validated['redirect'] ?? null);

        return response()->json([
            'url' => $this->webSession->buildLoginUrl($issued['token'], $redirect),
            'expires_in' => $this->webSession->ttlSeconds(),
            'redirect' => $redirect,
        ]);
    }

    public function initiateClaims(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:artist,venue,organiser'],
            'id' => ['nullable', 'integer', 'required_with:type'],
        ]);

        $user = $request->user();
        $type = $validated['type'] ?? null;
        $id = isset($validated['id']) ? (int) $validated['id'] : null;

        $result = $this->claimService->initiateClaimsForVerifiedUser($user, $type, $id);

        $user->refresh()->loadMissing('roles');

        $approved = $result['approved'];
        $pending = $result['pending'];
        $errors = $result['errors'];

        if (empty($approved) && empty($pending) && ! empty($errors)) {
            return response()->json([
                'message' => $errors[0]['message'] ?? 'Could not start claim.',
                'approved' => [],
                'pending' => [],
                'skipped' => $result['skipped'],
                'errors' => $errors,
                'owned_pages' => $this->meProfile->ownedPages($user),
                'claimable_pages' => $this->meProfile->claimablePages($user),
            ], 422);
        }

        $message = match (true) {
            count($approved) > 0 => count($approved) === 1
                ? 'Page claimed: '.$approved[0]['name']
                : count($approved).' pages claimed.',
            count($pending) > 0 => 'Claim started — pending verification or grace period.',
            default => 'Claim request recorded.',
        };

        return response()->json([
            'message' => $message,
            'approved' => $approved,
            'pending' => $pending,
            'skipped' => $result['skipped'],
            'errors' => $errors,
            'owned_pages' => $this->meProfile->ownedPages($user),
            'claimable_pages' => $this->meProfile->claimablePages($user),
            'roles' => $user->roles->pluck('name')->values(),
        ]);
    }

    public function requestClaim(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:artist,venue,organiser'],
            'id' => ['required', 'integer'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $type = $validated['type'];
        $id = (int) $validated['id'];

        $result = $this->claimService->requestManualClaimForUser(
            $user,
            $type,
            $id,
            $validated['message'] ?? null,
        );

        $user->refresh()->loadMissing('roles');

        if (empty($result['pending']) && ! empty($result['errors'])) {
            return response()->json([
                'message' => $result['errors'][0]['message'] ?? 'Could not submit claim request.',
                'pending' => [],
                'errors' => $result['errors'],
                'owned_pages' => $this->meProfile->ownedPages($user),
                'claimable_pages' => $this->meProfile->claimablePages($user),
            ], 422);
        }

        $message = count($result['pending']) === 1
            ? 'Claim request submitted for '.$result['pending'][0]['name'].' — our team will review it.'
            : 'Claim request submitted — our team will review it.';

        return response()->json([
            'message' => $message,
            'pending' => $result['pending'],
            'errors' => $result['errors'],
            'owned_pages' => $this->meProfile->ownedPages($user),
            'claimable_pages' => $this->meProfile->claimablePages($user),
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
            'events' => $user->favoriteEvents()
                ->select('events.id', 'events.name', 'events.poster')
                ->orderBy('events.name')
                ->get()
                ->map(fn (Event $e) => [
                    'id' => $e->id,
                    'name' => $e->name,
                    'image_url' => self::publicStorageUrl($e->poster),
                ])
                ->values(),
            'venues' => $user->favoriteVenues()
                ->select('venues.id', 'venues.name', 'venues.main_picture')
                ->orderBy('venues.name')
                ->get()
                ->map(fn (Venue $v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'image_url' => self::publicStorageUrl($v->main_picture),
                ])
                ->values(),
            'artists' => $user->favoriteArtists()
                ->select('artists.id', 'artists.stage_name', 'artists.profile_picture')
                ->orderBy('artists.stage_name')
                ->get()
                ->map(fn (Artist $a) => [
                    'id' => $a->id,
                    'stage_name' => $a->stage_name,
                    'image_url' => self::publicStorageUrl($a->profile_picture),
                ])
                ->values(),
            'organisers' => $user->favoriteOrganisers()
                ->select('organisers.id', 'organisers.organisation_name')
                ->orderBy('organisers.organisation_name')
                ->get(),
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
