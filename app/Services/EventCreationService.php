<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\YoutubeVideo;
use App\Rules\YoutubeUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventCreationService
{
    public function __construct(
        private readonly EventDuplicateService $duplicateService,
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
            'youtube_videos.*' => ['nullable', new YoutubeUrl()],
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
            $validated['poster'] = $request->file('poster')->store($eventFolder.'/poster', 'public');
        } else {
            unset($validated['poster']);
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

        $validated['owner_id'] = $user->id;
        $validated['owner_type'] = $this->resolveOwnerType($user);
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
                    $videoId = YoutubeVideo::extractVideoId($url);
                    if ($videoId) {
                        YoutubeVideo::create([
                            'videoable_type' => Event::class,
                            'videoable_id' => $event->id,
                            'youtube_url' => $url,
                            'youtube_video_id' => $videoId,
                            'order' => $index,
                        ]);
                    }
                }
            }
        }

        return [
            'event' => $event->fresh(['venue', 'artists', 'owner', 'categories', 'youtubeVideos']),
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

        return match ($event->owner_type) {
            'user' => (int) $event->owner_id === (int) $user->id,
            'artist' => $user->artist && (int) $event->owner_id === (int) $user->artist->id,
            'organiser' => $user->organiser && (int) $event->owner_id === (int) $user->organiser->id,
            default => false,
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
        if ($request->hasFile('poster')) {
            if ($event->poster) {
                Storage::disk('public')->delete($event->poster);
            }
            $posterPath = $request->file('poster')->store($eventFolder.'/poster', 'public');
        }
        $validated['poster'] = $posterPath;

        $existingGallery = is_array($event->gallery)
            ? $event->gallery
            : (is_string($event->gallery) ? json_decode($event->gallery, true) : []);
        if (! is_array($existingGallery)) {
            $existingGallery = [];
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
                    $videoId = YoutubeVideo::extractVideoId($url);
                    if ($videoId) {
                        YoutubeVideo::create([
                            'videoable_type' => Event::class,
                            'videoable_id' => $event->id,
                            'youtube_url' => $url,
                            'youtube_video_id' => $videoId,
                            'order' => $index,
                        ]);
                    }
                }
            }
        }

        return $event->fresh(['venue', 'artists', 'owner', 'categories', 'youtubeVideos']);
    }

    public function resolveOwnerType(User $user): string
    {
        if ($user->hasRole('artist')) {
            return 'artist';
        }
        if ($user->hasRole('organiser')) {
            return 'organiser';
        }

        return 'user';
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
