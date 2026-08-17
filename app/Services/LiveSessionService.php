<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\ArtistSong;
use App\Models\Event;
use App\Models\LiveSession;
use App\Models\SongRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class LiveSessionService
{
    public function __construct(
        private readonly ArtistPageService $artistPages,
        private readonly EventCreationService $events,
        private readonly SnapScanService $snapscan,
    ) {}

    public function canManageSession(User $user, LiveSession $session): bool
    {
        if ($user->hasRole(['superuser', 'admin'])) {
            return true;
        }

        return $this->canStartLive($user, $session->event, $session->artist);
    }

    public function canStartLive(User $user, Event $event, Artist $artist): bool
    {
        if ($user->hasRole(['superuser', 'admin'])) {
            return true;
        }

        if ($this->artistPages->userCanEditPage($user, $artist)) {
            return $this->artistOnEvent($event, $artist);
        }

        if ($this->events->userOwnsEvent($user, $event)) {
            return $this->artistOnEvent($event, $artist);
        }

        return false;
    }

    public function startSession(User $user, Event $event, Artist $artist): LiveSession
    {
        abort_unless($this->canStartLive($user, $event, $artist), 403, 'You cannot go live for this artist at this event.');
        $this->assertEventEligible($event);
        abort_unless($this->artistOnEvent($event, $artist), 422, 'Artist is not listed on this event.');

        $existing = $this->activeSessionForArtist($artist);
        abort_if($existing !== null, 422, 'This artist already has an active live session.');

        return LiveSession::query()->create([
            'artist_id' => $artist->id,
            'event_id' => $event->id,
            'started_by_user_id' => $user->id,
            'status' => LiveSession::STATUS_LIVE,
            'started_at' => now(),
        ]);
    }

    public function endSession(User $user, LiveSession $session): LiveSession
    {
        abort_unless($this->canManageSession($user, $session), 403, 'You cannot end this live session.');
        abort_unless($session->isLive(), 422, 'This session is not live.');

        $session->update([
            'status' => LiveSession::STATUS_ENDED,
            'ended_at' => now(),
        ]);

        return $session->fresh(['artist', 'event']);
    }

    public function activeSessionForArtist(Artist $artist): ?LiveSession
    {
        return LiveSession::query()
            ->where('artist_id', $artist->id)
            ->where('status', LiveSession::STATUS_LIVE)
            ->latest('started_at')
            ->first();
    }

    /**
     * @return Collection<int, LiveSession>
     */
    public function liveSessionsForEvent(Event $event): Collection
    {
        return LiveSession::query()
            ->with('artist')
            ->where('event_id', $event->id)
            ->where('status', LiveSession::STATUS_LIVE)
            ->orderBy('started_at')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function submitRequest(User $user, LiveSession $session, array $data): SongRequest
    {
        abort_unless($session->isLive(), 422, 'This artist is not accepting requests right now.');

        $songId = isset($data['artist_song_id']) ? (int) $data['artist_song_id'] : null;
        $message = trim((string) ($data['message'] ?? ''));

        abort_if($songId === null && $message === '', 422, 'Pick a song or enter a request message.');

        $artistSong = null;
        if ($songId !== null) {
            $artistSong = ArtistSong::query()
                ->where('id', $songId)
                ->where('artist_id', $session->artist_id)
                ->first();
            abort_unless($artistSong, 422, 'Song not found in this artist\'s repertoire.');
        }

        if ($artistSong !== null) {
            $duplicate = SongRequest::query()
                ->where('live_session_id', $session->id)
                ->where('user_id', $user->id)
                ->where('artist_song_id', $artistSong->id)
                ->whereIn('status', [
                    SongRequest::STATUS_PENDING,
                    SongRequest::STATUS_ACCEPTED,
                ])
                ->exists();
            abort_if($duplicate, 422, 'You already requested this song.');
        }

        return SongRequest::query()->create([
            'live_session_id' => $session->id,
            'user_id' => $user->id,
            'artist_song_id' => $artistSong?->id,
            'message' => $message !== '' ? $message : null,
            'status' => SongRequest::STATUS_PENDING,
        ]);
    }

    public function recordTipIntent(
        User $user,
        LiveSession $session,
        SongRequest $request,
        int $amountZar,
    ): SongRequest {
        abort_unless((int) $request->live_session_id === (int) $session->id, 404);
        abort_unless((int) $request->user_id === (int) $user->id, 403, 'You can only set a tip on your own request.');

        $allowed = $this->snapscan->tipAmountsZar();
        abort_unless(in_array($amountZar, $allowed, true), 422, 'Invalid tip amount.');

        $request->update(['tip_amount_zar' => $amountZar]);

        return $request->fresh(['artistSong', 'user']);
    }

    public function updateRequestStatus(
        User $user,
        LiveSession $session,
        SongRequest $request,
        string $status,
    ): SongRequest {
        abort_unless($this->canManageSession($user, $session), 403, 'You cannot manage this queue.');
        abort_unless((int) $request->live_session_id === (int) $session->id, 404);

        $allowed = [
            SongRequest::STATUS_PENDING,
            SongRequest::STATUS_ACCEPTED,
            SongRequest::STATUS_PLAYED,
            SongRequest::STATUS_SKIPPED,
            SongRequest::STATUS_DECLINED,
        ];
        abort_unless(in_array($status, $allowed, true), 422, 'Invalid request status.');

        $request->update(['status' => $status]);

        return $request->fresh(['artistSong', 'user']);
    }

    /**
     * @return Collection<int, SongRequest>
     */
    public function queueForSession(LiveSession $session): Collection
    {
        return $session->requests()
            ->with(['artistSong', 'user'])
            ->orderByRaw("CASE status WHEN 'accepted' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END")
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeSession(LiveSession $session, bool $includeCounts = false): array
    {
        $session->loadMissing(['artist', 'event']);

        $payload = [
            'id' => $session->id,
            'status' => $session->status,
            'artist_id' => $session->artist_id,
            'artist_name' => $session->artist?->stage_name,
            'event_id' => $session->event_id,
            'event_name' => $session->event?->name,
            'started_at' => $session->started_at?->toIso8601String(),
            'ended_at' => $session->ended_at?->toIso8601String(),
            'is_live' => $session->isLive(),
            'tips' => $this->snapscan->tipsPayload($session->artist?->snapscan_code),
        ];

        if ($includeCounts) {
            $payload['pending_count'] = $session->requests()
                ->where('status', SongRequest::STATUS_PENDING)
                ->count();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeRequest(SongRequest $request, bool $forArtist = false): array
    {
        $request->loadMissing(['artistSong', 'user']);

        $label = $request->artistSong
            ? trim($request->artistSong->title.($request->artistSong->original_artist
                ? ' — '.$request->artistSong->original_artist
                : ''))
            : ($request->message ?? 'Request');

        $payload = [
            'id' => $request->id,
            'status' => $request->status,
            'label' => $label,
            'artist_song_id' => $request->artist_song_id,
            'message' => $request->message,
            'tip_reference' => $request->tip_reference,
            'created_at' => $request->created_at?->toIso8601String(),
        ];

        if ($forArtist) {
            $payload['fan_name'] = $request->user?->name ?: $request->user?->username;
            if ($request->tip_amount_zar !== null) {
                $payload['tip_amount_zar'] = (int) $request->tip_amount_zar;
            }
        }

        return $payload;
    }

    private function artistOnEvent(Event $event, Artist $artist): bool
    {
        $event->loadMissing('artists');

        return $event->artists->contains(fn (Artist $row) => (int) $row->id === (int) $artist->id);
    }

    private function assertEventEligible(Event $event): void
    {
        abort_if($event->isCancelled(), 422, 'This event is cancelled.');

        if ($event->date === null) {
            return;
        }

        $cutoff = now()->subDay()->startOfDay();
        abort_if($event->date->lt($cutoff), 422, 'This event is too far in the past to go live.');
    }
}
