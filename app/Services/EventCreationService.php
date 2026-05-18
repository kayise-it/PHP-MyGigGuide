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

    public function createFromRequest(Request $request, User $user): Event
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages(),
            $this->attributeNames()
        );

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
