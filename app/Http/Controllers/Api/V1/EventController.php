<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Category;
use App\Models\Event;
use App\Services\EventCreationService;
use App\Services\MiggsBridgePosterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Query mirrors public {@see \App\Http\Controllers\EventController::index} filters
     * so the app and WhatsApp integrations see the same event set as `/events`.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Event::with(['venue', 'artists', 'owner', 'categories'])
            ->whereIn('status', ['upcoming', 'ongoing']);

        $startDateDefault = now()->toDateString();
        $endDateDefault = now()->addDays(30)->toDateString();

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->get('date_from', $startDateDefault);
            $dateTo = $request->get('date_to', $endDateDefault);
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        } else {
            $query->whereBetween('date', [$startDateDefault, $endDateDefault]);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('category', 'like', "%{$searchTerm}%")
                    ->orWhereHas('categories', function ($categoryQuery) use ($searchTerm) {
                        $categoryQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('slug', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('venue', function ($venueQuery) use ($searchTerm) {
                        $venueQuery->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('address', 'like', "%{$searchTerm}%")
                            ->orWhere('city', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('artists', function ($artistQuery) use ($searchTerm) {
                        $artistQuery->where('stage_name', 'like', "%{$searchTerm}%")
                            ->orWhere('real_name', 'like', "%{$searchTerm}%")
                            ->orWhere('genre', 'like', "%{$searchTerm}%");
                    });
            });
        }

        if ($request->filled('category')) {
            $categoryFilter = $request->category;

            $categoryRecord = Category::query()
                ->when(is_numeric($categoryFilter), function ($q) use ($categoryFilter) {
                    $q->where('id', (int) $categoryFilter);
                }, function ($q) use ($categoryFilter) {
                    $q->where('slug', $categoryFilter);
                })
                ->first();

            $resolvedSlug = $categoryRecord?->slug ?? Str::slug($categoryFilter);
            $resolvedName = $categoryRecord?->name ?? $categoryFilter;

            $query->where(function ($categoryScope) use ($categoryFilter, $resolvedSlug, $resolvedName) {
                $categoryScope->whereHas('categories', function ($categoryQuery) use ($categoryFilter, $resolvedSlug, $resolvedName) {
                    $categoryQuery->whereIn('slug', array_filter([$categoryFilter, $resolvedSlug]))
                        ->orWhere(function ($nameQuery) use ($categoryFilter, $resolvedName) {
                            $nameQuery->whereRaw('LOWER(name) = ?', [strtolower($categoryFilter)])
                                ->orWhereRaw('LOWER(name) = ?', [strtolower($resolvedName)]);
                        });
                })->orWhere(function ($legacyCategoryQuery) use ($categoryFilter, $resolvedName, $resolvedSlug) {
                    $legacyCategoryQuery->whereRaw('LOWER(category) = ?', [strtolower($categoryFilter)])
                        ->orWhereRaw('LOWER(category) = ?', [strtolower($resolvedName)])
                        ->orWhereRaw('REPLACE(LOWER(category), " ", "-") = ?', [strtolower($resolvedSlug)]);
                });
            });
        }

        $perPage = min((int) $request->get('per_page', 30), 100);

        $events = $query->orderBy('date', 'asc')->orderBy('time', 'asc')->paginate($perPage);

        return EventResource::collection($events);
    }

    public function show(Request $request, Event $event): EventResource
    {
        $user = $request->user('sanctum');
        $canView = in_array($event->status, ['upcoming', 'ongoing'], true)
            || ($user && app(EventCreationService::class)->userOwnsEvent($user, $event));

        abort_unless($canView, 404);

        $event->load(['venue', 'artists', 'owner', 'categories', 'youtubeVideos']);
        $event->loadCount('ratings');
        $event->loadAvg('ratings', 'rating');

        return new EventResource($event);
    }

    /**
     * Parse a poster image via internal miggs-bridge (Groq vision). Mobile app only.
     */
    public function parsePoster(Request $request, MiggsBridgePosterService $bridge): JsonResponse
    {
        if (! $bridge->isConfigured()) {
            return response()->json([
                'message' => 'Poster reading is not configured on the server.',
            ], 503);
        }

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,webp,gif',
                'max:'.(int) config('miggs_bridge.max_poster_kb', 8192),
            ],
            'hint' => ['nullable', 'string', 'max:800'],
        ]);

        try {
            $result = $bridge->parsePoster(
                $request->file('file'),
                (string) ($request->input('hint') ?? ''),
                $request->user()?->username,
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
        }

        return response()->json($result);
    }

    /**
     * Create an event (mobile app). Requires Sanctum token + create-events permission.
     * Multipart when uploading poster/gallery; JSON acceptable without files.
     */
    public function store(Request $request, EventCreationService $eventCreation): JsonResponse
    {
        $result = $eventCreation->createFromRequest($request, $request->user());

        return (new EventResource($result['event']))
            ->additional([
                'message' => $result['existing']
                    ? 'Event already exists — using existing listing.'
                    : 'Event created successfully.',
                'existing' => $result['existing'],
            ])
            ->response()
            ->setStatusCode($result['existing'] ? 200 : 201);
    }

    /**
     * Update an event the authenticated user posted. Multipart or JSON (no new files).
     */
    public function update(Request $request, Event $event, EventCreationService $eventCreation): JsonResponse
    {
        $updated = $eventCreation->updateFromRequest($request, $event, $request->user());

        return (new EventResource($updated))
            ->additional(['message' => 'Event updated successfully.'])
            ->response();
    }

    /**
     * Delete an event the authenticated user posted.
     */
    public function destroy(Request $request, Event $event, EventCreationService $eventCreation): JsonResponse
    {
        $eventCreation->deleteForUser($request->user(), $event);

        return response()->json(['message' => 'Event deleted successfully.']);
    }
}
