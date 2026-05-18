<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\EventResource;
use App\Models\Category;
use App\Models\Event;
use App\Services\EventCreationService;
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
        $query->whereBetween('date', [$startDateDefault, $endDateDefault]);

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

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $dateFrom = $request->get('date_from', $startDateDefault);
            $dateTo = $request->get('date_to', $endDateDefault);
            $query->whereBetween('date', [$dateFrom, $dateTo]);
        }

        $perPage = min((int) $request->get('per_page', 30), 100);

        $events = $query->orderBy('date', 'asc')->orderBy('time', 'asc')->paginate($perPage);

        return EventResource::collection($events);
    }

    public function show(Event $event): EventResource
    {
        abort_unless(in_array($event->status, ['upcoming', 'ongoing'], true), 404);

        $event->load(['venue', 'artists', 'owner', 'categories', 'youtubeVideos']);

        return new EventResource($event);
    }

    /**
     * Create an event (mobile app). Requires Sanctum token + create-events permission.
     * Multipart when uploading poster/gallery; JSON acceptable without files.
     */
    public function store(Request $request, EventCreationService $eventCreation): JsonResponse
    {
        $event = $eventCreation->createFromRequest($request, $request->user());

        return (new EventResource($event))
            ->additional(['message' => 'Event created successfully.'])
            ->response()
            ->setStatusCode(201);
    }
}
