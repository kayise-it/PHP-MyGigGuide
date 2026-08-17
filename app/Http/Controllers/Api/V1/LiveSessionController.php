<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\Event;
use App\Models\LiveSession;
use App\Models\SongRequest;
use App\Services\LiveSessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveSessionController extends Controller
{
    public function __construct(
        private readonly LiveSessionService $liveSessions,
    ) {}

    public function forEvent(Event $event): JsonResponse
    {
        $sessions = $this->liveSessions->liveSessionsForEvent($event);

        return response()->json([
            'data' => $sessions
                ->map(fn (LiveSession $session) => $this->liveSessions->serializeSession($session))
                ->values()
                ->all(),
            'meta' => [
                'event_id' => $event->id,
                'live_count' => $sessions->count(),
            ],
        ]);
    }

    public function forArtist(Artist $artist): JsonResponse
    {
        $session = $this->liveSessions->activeSessionForArtist($artist);

        return response()->json([
            'data' => $session
                ? $this->liveSessions->serializeSession($session)
                : null,
        ]);
    }

    public function show(LiveSession $liveSession): JsonResponse
    {
        return response()->json([
            'data' => $this->liveSessions->serializeSession($liveSession, includeCounts: true),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'artist_id' => ['required', 'integer', 'exists:artists,id'],
        ]);

        $event = Event::query()->findOrFail($validated['event_id']);
        $artist = Artist::query()->findOrFail($validated['artist_id']);

        $session = $this->liveSessions->startSession($request->user(), $event, $artist);

        return response()->json([
            'data' => $this->liveSessions->serializeSession($session->load(['artist', 'event']), includeCounts: true),
        ], 201);
    }

    public function end(Request $request, LiveSession $liveSession): JsonResponse
    {
        $session = $this->liveSessions->endSession($request->user(), $liveSession);

        return response()->json([
            'data' => $this->liveSessions->serializeSession($session, includeCounts: true),
        ]);
    }

    public function queue(Request $request, LiveSession $liveSession): JsonResponse
    {
        abort_unless($this->liveSessions->canManageSession($request->user(), $liveSession), 403);

        $requests = $this->liveSessions->queueForSession($liveSession);

        return response()->json([
            'data' => $requests
                ->map(fn (SongRequest $row) => $this->liveSessions->serializeRequest($row, forArtist: true))
                ->values()
                ->all(),
            'meta' => [
                'session_id' => $liveSession->id,
                'total' => $requests->count(),
            ],
        ]);
    }

    public function submitRequest(Request $request, LiveSession $liveSession): JsonResponse
    {
        $validated = $request->validate([
            'artist_song_id' => ['nullable', 'integer', 'exists:artist_songs,id'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $songRequest = $this->liveSessions->submitRequest($request->user(), $liveSession, $validated);
        $songRequest->loadMissing('liveSession.artist');

        $tipReference = app(\App\Services\SnapScanService::class)->requestReference($songRequest->id);
        $songRequest->update(['tip_reference' => $tipReference]);
        $songRequest->refresh();

        $tips = app(\App\Services\SnapScanService::class)
            ->tipsPayload($songRequest->liveSession?->artist?->snapscan_code);

        return response()->json([
            'data' => $this->liveSessions->serializeRequest($songRequest->fresh(['artistSong'])),
            'tip' => array_merge($tips, [
                'reference' => $tipReference,
            ]),
        ], 201);
    }

    public function updateRequest(Request $request, LiveSession $liveSession, SongRequest $songRequest): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,accepted,played,skipped,declined'],
        ]);

        $updated = $this->liveSessions->updateRequestStatus(
            $request->user(),
            $liveSession,
            $songRequest,
            $validated['status'],
        );

        return response()->json([
            'data' => $this->liveSessions->serializeRequest($updated, forArtist: true),
        ]);
    }

    public function recordTipIntent(Request $request, LiveSession $liveSession, SongRequest $songRequest): JsonResponse
    {
        $validated = $request->validate([
            'amount_zar' => ['required', 'integer', 'min:1'],
        ]);

        $updated = $this->liveSessions->recordTipIntent(
            $request->user(),
            $liveSession,
            $songRequest,
            (int) $validated['amount_zar'],
        );

        return response()->json([
            'data' => $this->liveSessions->serializeRequest($updated),
        ]);
    }

    public function myRequests(Request $request, LiveSession $liveSession): JsonResponse
    {
        $requests = $liveSession->requests()
            ->where('user_id', $request->user()->id)
            ->with('artistSong')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $requests
                ->map(fn (SongRequest $row) => $this->liveSessions->serializeRequest($row))
                ->values()
                ->all(),
        ]);
    }
}
