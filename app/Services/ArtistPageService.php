<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\User;
use App\Models\YoutubeVideo;
use App\Rules\UniqueNormalizedName;
use App\Rules\YoutubeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArtistPageService
{
    public function __construct(
        private readonly SnapScanService $snapscan,
    ) {}
    public function userOwnsArtist(User $user, Artist $artist): bool
    {
        $user->loadMissing('artist');

        if ($user->artist && (int) $user->artist->id === (int) $artist->id) {
            return true;
        }

        return $artist->user_id !== null && (int) $artist->user_id === (int) $user->id;
    }

    public function userCanEditPage(User $user, Artist $artist): bool
    {
        if ($user->hasRole(['superuser', 'admin'])) {
            return true;
        }

        if ($user->can('manage-users') || $user->can('moderate-content')) {
            return true;
        }

        if (! $this->userOwnsArtist($user, $artist)) {
            return false;
        }

        if ($artist->hasDisputedClaim()) {
            return false;
        }

        if ($artist->hasPendingClaim()
            && (int) $artist->pending_claim_user_id !== (int) $user->id) {
            return false;
        }

        // Approved claim, or legacy/admin link where user_id is already set.
        return $artist->claim_status === 'approved' || $artist->user_id !== null;
    }

    public function userCanEditVideos(User $user, Artist $artist): bool
    {
        return $this->userCanEditPage($user, $artist);
    }

    /**
     * Owner/admin update: profile fields and/or YouTube links.
     */
    public function updateFromRequest(Request $request, Artist $artist, User $user): Artist
    {
        abort_unless($this->userCanEditPage($user, $artist), 403, 'You cannot edit this artist page.');

        $request->merge([
            'instagram' => filled($request->input('instagram')) ? $request->input('instagram') : null,
            'facebook' => filled($request->input('facebook')) ? $request->input('facebook') : null,
            'twitter' => filled($request->input('twitter')) ? $request->input('twitter') : null,
            'tiktok' => filled($request->input('tiktok')) ? $request->input('tiktok') : null,
            'contact_email' => filled($request->input('contact_email')) ? $request->input('contact_email') : null,
        ]);

        $validated = $request->validate([
            'stage_name' => ['sometimes', 'required', 'string', 'max:255', UniqueNormalizedName::forArtist($artist->id)],
            'real_name' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:5000',
            'phone_number' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'instagram' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'snapscan_code' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'youtube_videos' => 'nullable|array|max:5',
            'youtube_videos.*' => ['nullable', new YoutubeUrl],
        ]);

        $updates = collect($validated)
            ->except(['youtube_videos', 'profile_picture', 'snapscan_code'])
            ->filter(fn ($value) => $value !== null)
            ->all();

        if ($request->exists('snapscan_code')) {
            $raw = trim((string) $request->input('snapscan_code', ''));
            $updates['snapscan_code'] = $raw === '' ? null : $this->snapscan->normalizeCode($raw);
        }

        if ($request->hasFile('profile_picture')) {
            if ($artist->profile_picture && Storage::disk('public')->exists($artist->profile_picture)) {
                Storage::disk('public')->delete($artist->profile_picture);
            }

            $updates['profile_picture'] = $request->file('profile_picture')
                ->store('artists/profile_pictures', 'public');
        }

        if ($updates !== []) {
            $artist->update($updates);
        }

        if ($request->exists('youtube_videos')) {
            $urls = collect($validated['youtube_videos'] ?? [])
                ->map(fn ($url) => is_string($url) ? trim($url) : '')
                ->filter(fn ($url) => $url !== '')
                ->values()
                ->all();

            $this->syncYoutubeVideos($artist, $urls);
        }

        return $artist->fresh(['genres', 'youtubeVideos']);
    }

    /**
     * @deprecated Use updateFromRequest — kept for clarity in call sites.
     */
    public function updateVideosFromRequest(Request $request, Artist $artist, User $user): Artist
    {
        return $this->updateFromRequest($request, $artist, $user);
    }

    /**
     * @param  list<string>  $urls
     */
    public function syncYoutubeVideos(Artist $artist, array $urls): void
    {
        $artist->youtubeVideos()->delete();

        foreach ($urls as $index => $url) {
            YoutubeVideo::createFromUrl($artist, $url, $index);
        }
    }
}
