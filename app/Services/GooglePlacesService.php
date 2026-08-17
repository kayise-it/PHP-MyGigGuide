<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Fetch normalized venue fields from Google Place Details (legacy Places API).
 */
class GooglePlacesService
{
    /**
     * @return array{name: string, address: string, city: string|null, latitude: float, longitude: float}
     */
    public function placeDetails(string $placeId): array
    {
        $apiKey = config('services.google_maps.api_key');
        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('Google Maps API key is not configured.');
        }

        $response = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'name,formatted_address,geometry,address_components,photos',
            'key' => $apiKey,
        ]);

        $response->throw();

        $payload = $response->json();
        $status = $payload['status'] ?? 'UNKNOWN';
        if ($status !== 'OK') {
            $message = $payload['error_message'] ?? $status;
            throw new RuntimeException('Google Place Details failed: '.$message);
        }

        $result = $payload['result'] ?? null;
        if (! is_array($result)) {
            throw new RuntimeException('Google Place Details returned an empty result.');
        }

        $name = trim((string) ($result['name'] ?? ''));
        $address = trim((string) ($result['formatted_address'] ?? ''));
        $lat = data_get($result, 'geometry.location.lat');
        $lng = data_get($result, 'geometry.location.lng');

        if ($name === '' || $address === '' || ! is_numeric($lat) || ! is_numeric($lng)) {
            throw new RuntimeException('Google Place Details is missing required venue fields.');
        }

        return [
            'name' => $name,
            'address' => $address,
            'city' => $this->extractCity($result['address_components'] ?? []),
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
            'photo_reference' => $this->firstPhotoReference($result['photos'] ?? []),
        ];
    }

    /**
     * First Google Place photo reference, if any.
     *
     * @param  array<int, array<string, mixed>>  $photos
     */
    public function firstPhotoReference(array $photos): ?string
    {
        foreach ($photos as $photo) {
            $ref = trim((string) ($photo['photo_reference'] ?? ''));
            if ($ref !== '') {
                return $ref;
            }
        }

        return null;
    }

    /**
     * Fetch the first place photo for a place id (Place Details).
     */
    public function firstPhotoReferenceForPlace(string $placeId): ?string
    {
        $apiKey = config('services.google_maps.api_key');
        if (! is_string($apiKey) || trim($apiKey) === '') {
            return null;
        }

        $response = Http::timeout(15)->get('https://maps.googleapis.com/maps/api/place/details/json', [
            'place_id' => $placeId,
            'fields' => 'photos',
            'key' => $apiKey,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();
        if (($payload['status'] ?? '') !== 'OK') {
            return null;
        }

        return $this->firstPhotoReference($payload['result']['photos'] ?? []);
    }

    /**
     * Download a Google Place photo into public storage. Returns stored path or null.
     */
    public function downloadPhoto(string $photoReference, string $directory = 'venues/main_pictures'): ?string
    {
        $apiKey = config('services.google_maps.api_key');
        if (! is_string($apiKey) || trim($apiKey) === '') {
            return null;
        }

        $ref = trim($photoReference);
        if ($ref === '') {
            return null;
        }

        $response = Http::timeout(25)->get('https://maps.googleapis.com/maps/api/place/photo', [
            'maxwidth' => 1200,
            'photo_reference' => $ref,
            'key' => $apiKey,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $body = $response->body();
        if ($body === '') {
            return null;
        }

        $folder = trim($directory, '/');
        Storage::disk('public')->makeDirectory($folder);
        $path = $folder.'/'.Str::random(40).'.jpg';
        Storage::disk('public')->put($path, $body);

        return $path;
    }

    /**
     * Find a nearby place by name and return place_id + first photo reference.
     *
     * @return array{place_id: string, photo_reference: string|null}|null
     */
    public function findPlaceNear(string $name, ?float $latitude = null, ?float $longitude = null): ?array
    {
        $apiKey = config('services.google_maps.api_key');
        if (! is_string($apiKey) || trim($apiKey) === '') {
            return null;
        }

        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $query = [
            'input' => $name,
            'inputtype' => 'textquery',
            'fields' => 'place_id,photos,geometry,name',
            'key' => $apiKey,
        ];
        if ($latitude !== null && $longitude !== null) {
            $query['locationbias'] = 'point:'.$latitude.','.$longitude;
        }

        $response = Http::timeout(15)->get(
            'https://maps.googleapis.com/maps/api/place/findplacefromtext/json',
            $query
        );

        if (! $response->successful()) {
            return null;
        }

        $payload = $response->json();
        if (($payload['status'] ?? '') !== 'OK') {
            return null;
        }

        $candidate = $payload['candidates'][0] ?? null;
        if (! is_array($candidate)) {
            return null;
        }

        $placeId = trim((string) ($candidate['place_id'] ?? ''));
        if ($placeId === '') {
            return null;
        }

        return [
            'place_id' => $placeId,
            'photo_reference' => $this->firstPhotoReference($candidate['photos'] ?? []),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $components
     */
    private function extractCity(array $components): ?string
    {
        foreach (['locality', 'administrative_area_level_2', 'administrative_area_level_1'] as $type) {
            foreach ($components as $component) {
                $types = $component['types'] ?? [];
                if (in_array($type, $types, true)) {
                    $name = trim((string) ($component['long_name'] ?? ''));
                    if ($name !== '') {
                        return $name;
                    }
                }
            }
        }

        return null;
    }
}
