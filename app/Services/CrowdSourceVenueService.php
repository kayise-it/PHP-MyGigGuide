<?php

namespace App\Services;

use App\Http\Resources\Api\V1\VenueResource;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CrowdSourceVenueService
{
    /**
     * Minimal venue row for event listing (aligned with web quick-store).
     *
     * @return array{venue: Venue, resource: VenueResource, existing: bool}
     */
    public function createFromRequest(Request $request, User $user): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $name = trim($validated['name']);
        $address = trim($validated['address']);

        $duplicate = Venue::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower($name)])
            ->whereRaw('LOWER(TRIM(COALESCE(address, ""))) = ?', [Str::lower($address)])
            ->first();

        if ($duplicate) {
            return [
                'venue' => $duplicate,
                'resource' => new VenueResource($duplicate),
                'existing' => true,
            ];
        }

        $base = Str::slug($name ?: 'venue');
        $payload = [
            'name' => $name,
            'address' => $address,
            'city' => isset($validated['city']) ? trim($validated['city']) : null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'contact_email' => $base.'+'.($user->id ?? 'api').'-'.time().'@example.local',
            'user_id' => $user->id,
            'owner_id' => $user->id,
            'owner_type' => $user->hasRole('artist') ? 'artist' : 'organiser',
        ];

        $venue = Venue::create($payload);

        return [
            'venue' => $venue,
            'resource' => new VenueResource($venue),
            'existing' => false,
        ];
    }
}
