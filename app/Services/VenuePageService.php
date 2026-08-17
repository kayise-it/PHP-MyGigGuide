<?php

namespace App\Services;

use App\Models\User;
use App\Models\Venue;
use App\Models\YoutubeVideo;
use App\Rules\UniqueNormalizedName;
use App\Rules\YoutubeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VenuePageService
{
    public function userOwnsVenue(User $user, Venue $venue): bool
    {
        return $venue->isOwnedBy((int) $user->id);
    }

    public function userCanEditPage(User $user, Venue $venue): bool
    {
        if ($user->hasRole(['superuser', 'admin'])) {
            return true;
        }

        if ($user->can('manage-users') || $user->can('moderate-content')) {
            return true;
        }

        if (! $this->userOwnsVenue($user, $venue)) {
            return false;
        }

        if ($venue->hasDisputedClaim()) {
            return false;
        }

        if ($venue->hasPendingClaim()
            && (int) $venue->pending_claim_user_id !== (int) $user->id) {
            return false;
        }

        // Approved claim, or legacy/admin link where user_id is already set.
        return $venue->claim_status === 'approved' || $venue->user_id !== null;
    }

    public function userCanEditVideos(User $user, Venue $venue): bool
    {
        return $this->userCanEditPage($user, $venue);
    }

    /**
     * Owner/admin update: profile fields and/or YouTube links.
     */
    public function updateFromRequest(Request $request, Venue $venue, User $user): Venue
    {
        abort_unless($this->userCanEditPage($user, $venue), 403, 'You cannot edit this venue page.');

        $request->merge([
            'website' => filled($request->input('website')) ? $request->input('website') : null,
            'contact_email' => filled($request->input('contact_email')) ? $request->input('contact_email') : null,
        ]);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255', UniqueNormalizedName::forVenue($venue->id)],
            'description' => 'nullable|string|max:5000',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'capacity' => 'nullable|integer|min:1',
            'phone_number' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'main_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'youtube_videos' => 'nullable|array|max:5',
            'youtube_videos.*' => ['nullable', new YoutubeUrl],
        ]);

        $updates = collect($validated)
            ->except(['youtube_videos', 'main_picture'])
            ->filter(fn ($value) => $value !== null)
            ->all();

        if ($request->hasFile('main_picture')) {
            if ($venue->main_picture && Storage::disk('public')->exists($venue->main_picture)) {
                Storage::disk('public')->delete($venue->main_picture);
            }

            $updates['main_picture'] = $request->file('main_picture')
                ->store('venues/main_pictures', 'public');
        }

        if ($updates !== []) {
            $venue->update($updates);
        }

        if ($request->exists('youtube_videos')) {
            $urls = collect($validated['youtube_videos'] ?? [])
                ->map(fn ($url) => is_string($url) ? trim($url) : '')
                ->filter(fn ($url) => $url !== '')
                ->values()
                ->all();

            $this->syncYoutubeVideos($venue, $urls);
        }

        return $venue->fresh(['youtubeVideos']);
    }

    /**
     * @deprecated Use updateFromRequest — kept for clarity in call sites.
     */
    public function updateVideosFromRequest(Request $request, Venue $venue, User $user): Venue
    {
        return $this->updateFromRequest($request, $venue, $user);
    }

    /**
     * @param  list<string>  $urls
     */
    public function syncYoutubeVideos(Venue $venue, array $urls): void
    {
        $venue->youtubeVideos()->delete();

        foreach ($urls as $index => $url) {
            YoutubeVideo::createFromUrl($venue, $url, $index);
        }
    }
}
