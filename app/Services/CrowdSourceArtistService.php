<?php

namespace App\Services;

use App\Http\Resources\Api\V1\ArtistResource;
use App\Models\Artist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CrowdSourceArtistService
{
    /**
     * Minimal artist row for event listing (unclaimed; same idea as web quick-store).
     *
     * @return array{artist: Artist, resource: ArtistResource}
     */
    public function createFromRequest(Request $request, User $user): array
    {
        $validated = $request->validate([
            'stage_name' => ['required', 'string', 'max:255'],
            'genre' => ['nullable', 'string', 'max:255'],
            'real_name' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $stageName = trim($validated['stage_name']);

        $duplicate = Artist::query()
            ->whereRaw('LOWER(TRIM(stage_name)) = ?', [Str::lower($stageName)])
            ->first();

        if ($duplicate) {
            return [
                'artist' => $duplicate,
                'resource' => new ArtistResource($duplicate),
                'existing' => true,
            ];
        }

        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            $profilePicturePath = $request->file('profile_picture')
                ->store('artists/profile_pictures', 'public');
        }

        $artist = Artist::create([
            'stage_name' => $stageName,
            'real_name' => $validated['real_name'] ?? null,
            'genre' => $validated['genre'] ?? 'Unknown',
            'profile_picture' => $profilePicturePath,
            'user_id' => null,
            'contact_email' => null,
        ]);

        return [
            'artist' => $artist,
            'resource' => new ArtistResource($artist),
            'existing' => false,
        ];
    }
}
