<?php

namespace App\Services;

use App\Helpers\NameNormalizer;
use App\Http\Resources\Api\V1\VenueResource;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Resolve a Google Place to an existing venue row or create one (Phase A).
 */
class VenueResolveService
{
    private const PROXIMITY_METERS = 200;

    public function __construct(
        private readonly GooglePlacesService $googlePlaces,
    ) {}

    /**
     * @return array{venue: Venue, resource: VenueResource, existing: bool, match_method: string}
     */
    public function resolveFromRequest(Request $request, User $user): array
    {
        $validated = $request->validate([
            'google_place_id' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $placeId = trim($validated['google_place_id']);

        $byPlaceId = Venue::query()->where('google_place_id', $placeId)->first();
        if ($byPlaceId) {
            $this->attachGooglePlacePhoto($byPlaceId, $placeId, null);

            return $this->result($byPlaceId->fresh(), true, 'google_place_id');
        }

        $place = $this->normalizePlacePayload($validated, $placeId);

        $nearby = $this->findNearbyByName($place['name'], $place['latitude'], $place['longitude']);
        if ($nearby) {
            if ($nearby->google_place_id === null) {
                $nearby->update(['google_place_id' => $placeId]);
                $nearby->refresh();
            }

            $this->attachGooglePlacePhoto($nearby, $placeId, $place['photo_reference'] ?? null);

            return $this->result($nearby->fresh(), true, 'proximity');
        }

        $byNameAddress = $this->findByNameAndAddress($place['name'], $place['address']);
        if ($byNameAddress) {
            if ($byNameAddress->google_place_id === null) {
                $byNameAddress->update(['google_place_id' => $placeId]);
                $byNameAddress->refresh();
            }

            $this->attachGooglePlacePhoto($byNameAddress, $placeId, $place['photo_reference'] ?? null);

            return $this->result($byNameAddress->fresh(), true, 'name_address');
        }

        $venue = $this->createVenue($place, $placeId, $user);
        $this->attachGooglePlacePhoto($venue, $placeId, $place['photo_reference'] ?? null);

        return $this->result($venue->fresh(), false, 'created');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{name: string, address: string, city: string|null, latitude: float, longitude: float, photo_reference?: string|null}
     */
    private function normalizePlacePayload(array $validated, string $placeId): array
    {
        $name = isset($validated['name']) ? trim((string) $validated['name']) : '';
        $address = isset($validated['address']) ? trim((string) $validated['address']) : '';
        $city = isset($validated['city']) ? trim((string) $validated['city']) : null;
        $latitude = $validated['latitude'] ?? null;
        $longitude = $validated['longitude'] ?? null;

        $hasCoords = is_numeric($latitude) && is_numeric($longitude);

        if ($name !== '' && $address !== '' && $hasCoords) {
            $photoReference = $this->googlePlaces->firstPhotoReferenceForPlace($placeId);

            return [
                'name' => $name,
                'address' => $address,
                'city' => $city !== '' ? $city : null,
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'photo_reference' => $photoReference,
            ];
        }

        try {
            return $this->googlePlaces->placeDetails($placeId);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                'Place details are incomplete. Send name, address, latitude, and longitude from the client, or configure GOOGLE_MAPS_API_KEY on the server. '.$e->getMessage()
            );
        }
    }

    private function findNearbyByName(string $name, float $latitude, float $longitude): ?Venue
    {
        $latDelta = self::PROXIMITY_METERS / 111_320;
        $lngDelta = self::PROXIMITY_METERS / (111_320 * max(cos(deg2rad($latitude)), 0.01));

        $candidates = Venue::query()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween('longitude', [$longitude - $lngDelta, $longitude + $lngDelta])
            ->limit(50)
            ->get();

        $best = null;
        $bestDistance = PHP_FLOAT_MAX;

        foreach ($candidates as $candidate) {
            if (! $this->namesLikelySame($name, (string) $candidate->name)) {
                continue;
            }

            $distance = $this->distanceMeters(
                $latitude,
                $longitude,
                (float) $candidate->latitude,
                (float) $candidate->longitude,
            );

            if ($distance <= self::PROXIMITY_METERS && $distance < $bestDistance) {
                $best = $candidate;
                $bestDistance = $distance;
            }
        }

        return $best;
    }

    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6_371_000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function findByNameAndAddress(string $name, string $address): ?Venue
    {
        return Venue::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower(trim($name))])
            ->whereRaw('LOWER(TRIM(COALESCE(address, ""))) = ?', [Str::lower(trim($address))])
            ->first();
    }

    /**
     * @param  array{name: string, address: string, city: string|null, latitude: float, longitude: float}  $place
     */
    private function createVenue(array $place, string $placeId, User $user): Venue
    {
        $base = Str::slug($place['name'] ?: 'venue');

        return Venue::create([
            'name' => $place['name'],
            'address' => $place['address'],
            'city' => $place['city'],
            'latitude' => $place['latitude'],
            'longitude' => $place['longitude'],
            'google_place_id' => $placeId,
            'contact_email' => $base.'+'.($user->id ?? 'api').'-'.time().'@example.local',
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'owner_type' => $user->hasRole('artist') ? 'artist' : 'organiser',
        ]);
    }

    private function namesLikelySame(string $a, string $b): bool
    {
        if (NameNormalizer::areDuplicates($a, $b)) {
            return true;
        }

        $na = NameNormalizer::normalize($a);
        $nb = NameNormalizer::normalize($b);
        if ($na === '' || $nb === '') {
            return false;
        }

        if (Str::contains($na, $nb) || Str::contains($nb, $na)) {
            $shorter = min(strlen($na), strlen($nb));
            if ($shorter >= 8) {
                return true;
            }
        }

        similar_text($na, $nb, $percent);

        return $percent >= 85.0;
    }

    /**
     * @return array{venue: Venue, resource: VenueResource, existing: bool, match_method: string}
     */
    private function result(Venue $venue, bool $existing, string $matchMethod): array
    {
        return [
            'venue' => $venue,
            'resource' => new VenueResource($venue),
            'existing' => $existing,
            'match_method' => $matchMethod,
        ];
    }

    private function attachGooglePlacePhoto(Venue $venue, string $placeId, ?string $photoReference): void
    {
        if ($venue->main_picture) {
            return;
        }

        $ref = $photoReference ?: $this->googlePlaces->firstPhotoReferenceForPlace($placeId);
        if ($ref === null) {
            return;
        }

        $path = $this->googlePlaces->downloadPhoto($ref);
        if ($path === null) {
            return;
        }

        $venue->update(['main_picture' => $path]);
    }
}
