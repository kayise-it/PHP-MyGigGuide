<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use App\Models\YoutubeVideo;
use App\Rules\YoutubeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\EventNotificationService;

class EventCreationService
{
    public function __construct(
        private readonly EventDuplicateService $duplicateService,
        private readonly EventPosterCardService $posterCardService,
        private readonly EventNotificationService $notificationService,
    ) {}

    /**
     * Validation rules shared by web and API create flows.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'price' => 'nullable|numeric|min:0',
            'ticket_url' => 'nullable|url',
            'tiktok' => 'nullable|url',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'gallery' => 'nullable|array|max:10',
            'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'category' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'venue_id' => 'required|exists:venues,id',
            'artists' => 'nullable|array',
            'artists.*' => 'integer|exists:artists,id',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'youtube_videos' => 'nullable|array',
            'youtube_videos.*' => ['nullable', new YoutubeUrl],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributeNames(): array
    {
        return [
            'venue_id' => 'venue',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'venue_id.required' => 'Please select a venue for your event.',
            'venue_id.exists' => 'The selected venue is invalid.',
        ];
    }

    /**
     * @return array{event: Event, existing: bool}
     */
    public function createFromRequest(Request $request, User $user): array
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages(),
            $this->attributeNames()
        );

        $duplicate = $this->duplicateService->findDuplicate($validated);
        if ($duplicate !== null) {
            return [
                'event' => $duplicate->loadMissing(['venue', 'artists', 'owner', 'categories', 'youtubeVideos']),
                'existing' => true,
            ];
        }

        $userFolder = $user->getFolderPath();
        $eventFolder = $this->createEventFolder($userFolder, $validated['name'], $validated['date']);

        if ($request->hasFile('poster')) {
            $storedPoster = $request->file('poster')->store($eventFolder.'/poster', 'public');
            $validated['poster'] = $storedPoster;
            $validated['poster_card'] = $this->posterCardService->portraitCardForPoster($storedPoster);
        } else {
            unset($validated['poster'], $validated['poster_card']);
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $galleryPaths[] = $image->store($eventFolder.'/gallery', 'public');
            }
            $validated['gallery'] = json_encode($galleryPaths);
        } else {
            unset($validated['gallery']);
        }

        unset($validated['artists'], $validated['categories'], $validated['youtube_videos']);

        if (empty($validated['poster']) && ! empty($validated['venue_id'])) {
            $venue = Venue::query()->find($validated['venue_id']);
            if ($venue?->main_picture) {
                $validated['poster'] = $venue->main_picture;
            }
        }

        $owner = $this->resolveOwner($user);
        $validated['owner_id'] = $owner['owner_id'];
        $validated['owner_type'] = $owner['owner_type'];
        $validated['status'] = 'upcoming';

        $event = Event::create($validated);

        if ($request->has('artists')) {
            $artistIds = collect($request->input('artists'))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();
            if ($artistIds !== []) {
                $event->artists()->attach($artistIds);
            }
        }

        if ($request->has('categories')) {
            $categoryIds = collect($request->input('categories'))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();
            if ($categoryIds !== []) {
                $event->categories()->attach($categoryIds);
            }
        }

        if ($request->has('youtube_videos') && is_array($request->youtube_videos)) {
            foreach ($request->youtube_videos as $index => $url) {
                if (! empty($url)) {
                    YoutubeVideo::createFromUrl($event, $url, $index);
                }
            }
        }

        $freshEvent = $event->fresh(['venue', 'artists', 'owner', 'categories', 'youtubeVideos']);

        $this->notificationService->notifyAdminNewEvent($freshEvent, $user);

        return [
            'event' => $freshEvent,
            'existing' => false,
        ];
    }

    /**
     * Whether this user posted / owns the event (matches crowd-source create ownership).
     */
    public function userOwnsEvent(User $user, Event $event): bool
    {
        if ($user->hasRole(['superuser', 'admin'])) {
            return true;
        }

        $user->loadMissing(['artist', 'organiser']);

        $ownerType = $this->normalizeOwnerType($event->owner_type);

        return match ($ownerType) {
            'user' => (int) $event->owner_id === (int) $user->id,
            'artist' => $user->artist && (
                (int) $event->owner_id === (int) $user->artist->id
                || (int) $event->owner_id === (int) $user->id
            ),
            'organiser' => $user->organiser && (
                (int) $event->owner_id === (int) $user->organiser->id
                || (int) $event->owner_id === (int) $user->id
            ),
            default => false,
        };
    }

    /**
     * Legacy imports used FQCN morph types; normalize to short keys used by create flow.
     */
    private function normalizeOwnerType(?string $ownerType): ?string
    {
        if ($ownerType === null || $ownerType === '') {
            return null;
        }

        return match ($ownerType) {
            'user', 'artist', 'organiser' => $ownerType,
            'App\Models\User', \App\Models\User::class => 'user',
            'App\Models\Artist', \App\Models\Artist::class => 'artist',
            'App\Models\Organiser', \App\Models\Organiser::class => 'organiser',
            default => class_basename($ownerType) === 'User' ? 'user'
                : (class_basename($ownerType) === 'Artist' ? 'artist'
                : (class_basename($ownerType) === 'Organiser' ? 'organiser' : $ownerType)),
        };
    }

    /**
     * Update an event the user owns (web edit parity, API + app).
     */
    public function updateFromRequest(Request $request, Event $event, User $user): Event
    {
        if (! $this->userOwnsEvent($user, $event)) {
            abort(403, 'You can only edit events you posted.');
        }

        $validated = $request->validate(
            $this->rules(),
            $this->messages(),
            $this->attributeNames()
        );

        $userFolder = $user->getFolderPath();
        $eventFolder = $this->createEventFolder($userFolder, $validated['name'], $validated['date']);

        $posterPath = $event->poster;
        $posterCardPath = $event->poster_card;
        if ($request->hasFile('poster')) {
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            $this->posterCardService->deletePortraitCard($event->poster_card);
            $posterPath = $request->file('poster')->store($eventFolder.'/poster', 'public');
            $posterCardPath = $this->posterCardService->portraitCardForPoster($posterPath);
        }
        $validated['poster'] = $posterPath;
        $validated['poster_card'] = $posterCardPath;

        $existingGallery = is_array($event->gallery)
            ? $event->gallery
            : (is_string($event->gallery) ? json_decode($event->gallery, true) : []);
        if (! is_array($existingGallery)) {
            $existingGallery = [];
        }

        if ($request->has('gallery_keep_urls')) {
            $existingGallery = $this->syncGalleryKeepUrls($existingGallery, $request->input('gallery_keep_urls'));
        }

        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $image) {
                $existingGallery[] = $image->store($eventFolder.'/gallery', 'public');
            }
        }
        $validated['gallery'] = json_encode(array_values($existingGallery));

        unset($validated['artists'], $validated['categories'], $validated['youtube_videos']);

        $event->update($validated);

        if ($request->has('artists')) {
            $artistIds = collect($request->input('artists'))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();
            $event->artists()->sync($artistIds);
        }

        if ($request->has('categories')) {
            $categoryIds = collect($request->input('categories'))
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();
            $event->categories()->sync($categoryIds);
        }

        if ($request->has('youtube_videos') && is_array($request->youtube_videos)) {
            $event->youtubeVideos()->delete();
            foreach ($request->youtube_videos as $index => $url) {
                if (! empty($url)) {
                    YoutubeVideo::createFromUrl($event, $url, $index);
                }
            }
        }

        return $event->fresh(['venue', 'artists', 'owner', 'categories', 'youtubeVideos']);
    }

    /**
     * Delete an event the user owns (web + API). Removes poster/gallery files.
     */
    public function deleteForUser(User $user, Event $event): void
    {
        if (! $this->userOwnsEvent($user, $event)) {
            abort(403, 'You can only delete events you posted.');
        }

        $this->deleteEventFiles($event);
        $event->youtubeVideos()->delete();
        $event->delete();
    }

    private function deleteEventFiles(Event $event): void
    {
        if ($event->poster && Storage::disk('public')->exists($event->poster)) {
            Storage::disk('public')->delete($event->poster);
        }

        $this->posterCardService->deletePortraitCard($event->poster_card);

        $gallery = is_array($event->gallery)
            ? $event->gallery
            : (is_string($event->gallery) ? json_decode($event->gallery, true) : []);

        if (is_array($gallery)) {
            foreach ($gallery as $image) {
                if ($image && Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }
    }

    /**
     * Keep only gallery files whose public URL is listed; delete the rest from storage.
     *
     * @param  array<int, string>  $existingPaths
     * @return array<int, string>
     */
    private function syncGalleryKeepUrls(array $existingPaths, mixed $keepUrlsRaw): array
    {
        $keepUrls = $keepUrlsRaw;
        if (is_string($keepUrls)) {
            $decoded = json_decode($keepUrls, true);
            $keepUrls = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($keepUrls)) {
            $keepUrls = [];
        }

        $keepPaths = [];
        foreach ($keepUrls as $url) {
            if (! is_string($url) || trim($url) === '') {
                continue;
            }
            $path = $this->storagePathFromPublicUrl($url);
            if ($path !== null) {
                $keepPaths[] = $path;
            }
        }

        $keepSet = array_flip($keepPaths);
        foreach ($existingPaths as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }
            if (isset($keepSet[$path])) {
                continue;
            }
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        return array_values(array_filter(
            $existingPaths,
            fn ($path) => is_string($path) && $path !== '' && isset($keepSet[$path])
        ));
    }

    private function storagePathFromPublicUrl(string $url): ?string
    {
        $path = parse_url(trim($url), PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (preg_match('#/storage/(.+)$#', $path, $matches) !== 1) {
            return null;
        }

        return ltrim($matches[1], '/');
    }

    /**
     * @return array{owner_id: int, owner_type: string}
     */
    public function resolveOwner(User $user): array
    {
        $user->loadMissing(['artist', 'organiser']);

        if ($user->artist) {
            return [
                'owner_id' => (int) $user->artist->id,
                'owner_type' => 'artist',
            ];
        }

        if ($user->organiser) {
            return [
                'owner_id' => (int) $user->organiser->id,
                'owner_type' => 'organiser',
            ];
        }

        return [
            'owner_id' => (int) $user->id,
            'owner_type' => 'user',
        ];
    }

    public function resolveOwnerType(User $user): string
    {
        return $this->resolveOwner($user)['owner_type'];
    }

    public function createEventFolder(string $userFolder, string $eventName, string $eventDate): string
    {
        $safeName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $eventName));
        $date = \Carbon\Carbon::parse($eventDate)->format('Y-m-d');
        $randomSuffix = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);

        $folderName = "event_{$randomSuffix}_{$safeName}_{$date}";
        $eventFolder = $userFolder.'/events/'.$folderName;

        Storage::disk('public')->makeDirectory($eventFolder.'/poster');
        Storage::disk('public')->makeDirectory($eventFolder.'/gallery');
        Storage::disk('public')->makeDirectory($eventFolder.'/documents');

        return $eventFolder;
    }
}
