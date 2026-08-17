<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Artist;
use App\Models\ArtistSong;
use App\Services\ArtistRepertoireService;
use App\Services\Spotify\SpotifyApiClient;
use App\Services\Spotify\SpotifyOAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArtistRepertoireController extends Controller
{
    public function __construct(
        private readonly ArtistRepertoireService $repertoire,
        private readonly SpotifyApiClient $spotify,
        private readonly SpotifyOAuthService $spotifyOAuth,
    ) {}

    public function index(Artist $artist): JsonResponse
    {
        $songs = $this->repertoire->songsForArtist($artist);

        return response()->json([
            'data' => $songs->map(fn (ArtistSong $song) => $this->repertoire->serializePublic($song))->values()->all(),
            'meta' => [
                'total' => $songs->count(),
                'artist_id' => $artist->id,
            ],
        ]);
    }

    public function meIndex(Request $request): JsonResponse
    {
        return $this->manageIndex($request, $this->repertoire->ownedArtistForUser($request->user()));
    }

    public function manageIndex(Request $request, Artist $artist): JsonResponse
    {
        $this->repertoire->assertCanEdit($request->user(), $artist);
        $songs = $this->repertoire->songsForArtist($artist);

        return response()->json([
            'data' => $songs->map(fn (ArtistSong $song) => $this->repertoire->serializeOwner($song))->values()->all(),
            'meta' => [
                'total' => $songs->count(),
                'artist_id' => $artist->id,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->manageStore($request, $this->repertoire->ownedArtistForUser($request->user()));
    }

    public function manageStore(Request $request, Artist $artist): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'original_artist' => ['nullable', 'string', 'max:255'],
            'is_original' => ['nullable', 'boolean'],
            'youtube_url' => ['nullable', 'string', 'max:500'],
            'spotify_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->repertoire->assertCanEdit($request->user(), $artist);
        $song = $this->repertoire->createSong($artist, $validated);

        return response()->json([
            'data' => $this->repertoire->serializeOwner($song),
        ], 201);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        return $this->manageBulkStore($request, $this->repertoire->ownedArtistForUser($request->user()));
    }

    public function manageBulkStore(Request $request, Artist $artist): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:50000'],
        ]);

        $this->repertoire->assertCanEdit($request->user(), $artist);
        $result = $this->repertoire->bulkCreateFromText($artist, $validated['text']);

        return response()->json([
            'data' => collect($result['songs'])
                ->map(fn (ArtistSong $song) => $this->repertoire->serializeOwner($song))
                ->values()
                ->all(),
            'summary' => [
                'created' => $result['created'],
                'skipped' => $result['skipped'],
            ],
        ], 201);
    }

    public function update(Request $request, ArtistSong $song): JsonResponse
    {
        $artist = $this->repertoire->ownedArtistForUser($request->user());

        return $this->manageUpdate($request, $artist, $song);
    }

    public function manageUpdate(Request $request, Artist $artist, ArtistSong $song): JsonResponse
    {
        $this->repertoire->assertCanEdit($request->user(), $artist);
        abort_unless((int) $song->artist_id === (int) $artist->id, 404);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'original_artist' => ['nullable', 'string', 'max:255'],
            'is_original' => ['sometimes', 'boolean'],
            'youtube_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $updated = $this->repertoire->updateSong($song, $validated);

        return response()->json([
            'data' => $this->repertoire->serializeOwner($updated),
        ]);
    }

    public function destroy(Request $request, ArtistSong $song): JsonResponse
    {
        $artist = $this->repertoire->ownedArtistForUser($request->user());

        return $this->manageDestroy($request, $artist, $song);
    }

    public function manageDestroy(Request $request, Artist $artist, ArtistSong $song): JsonResponse
    {
        $this->repertoire->assertCanEdit($request->user(), $artist);
        abort_unless((int) $song->artist_id === (int) $artist->id, 404);

        $this->repertoire->deleteSong($song);

        return response()->json(['message' => 'Song removed.']);
    }

    public function reorder(Request $request): JsonResponse
    {
        return $this->manageReorder($request, $this->repertoire->ownedArtistForUser($request->user()));
    }

    public function manageReorder(Request $request, Artist $artist): JsonResponse
    {
        $validated = $request->validate([
            'song_ids' => ['required', 'array', 'min:1'],
            'song_ids.*' => ['integer'],
        ]);

        $this->repertoire->assertCanEdit($request->user(), $artist);
        $this->repertoire->reorderSongs($artist, $validated['song_ids']);

        return response()->json(['message' => 'Order saved.']);
    }

    public function spotifySearch(Request $request): JsonResponse
    {
        abort_unless($this->repertoire->spotifyConfigured(), 503, 'Spotify search is not configured on this server.');

        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:120'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $results = $this->repertoire->searchSpotifyTracks(
            $validated['q'],
            (int) ($validated['limit'] ?? 10),
        );

        return response()->json(['data' => $results]);
    }

    public function spotifyStatus(Request $request): JsonResponse
    {
        return $this->manageSpotifyStatus($request, $this->repertoire->ownedArtistForUser($request->user()));
    }

    public function manageSpotifyStatus(Request $request, Artist $artist): JsonResponse
    {
        abort_unless($this->repertoire->spotifyConfigured(), 503, 'Spotify is not configured on this server.');

        $this->repertoire->assertCanEdit($request->user(), $artist);

        return response()->json([
            'data' => $this->repertoire->spotifyConnectionForArtist($artist),
        ]);
    }

    public function spotifyAuthUrl(Request $request): JsonResponse
    {
        return $this->manageSpotifyAuthUrl($request, $this->repertoire->ownedArtistForUser($request->user()));
    }

    public function manageSpotifyAuthUrl(Request $request, Artist $artist): JsonResponse
    {
        abort_unless($this->repertoire->spotifyConfigured(), 503, 'Spotify is not configured on this server.');
        abort_unless($this->spotifyOAuth->isConfigured(), 503, 'Spotify connect is not configured on this server.');

        $this->repertoire->assertCanEdit($request->user(), $artist);

        return response()->json([
            'data' => [
                'url' => $this->spotifyOAuth->authorizationUrl((int) $request->user()->id, (int) $artist->id),
            ],
        ]);
    }

    public function spotifyDisconnect(Request $request): JsonResponse
    {
        return $this->manageSpotifyDisconnect($request, $this->repertoire->ownedArtistForUser($request->user()));
    }

    public function manageSpotifyDisconnect(Request $request, Artist $artist): JsonResponse
    {
        abort_unless($this->repertoire->spotifyConfigured(), 503, 'Spotify is not configured on this server.');

        $this->repertoire->assertCanEdit($request->user(), $artist);
        $this->spotifyOAuth->disconnectArtist($artist);

        return response()->json([
            'data' => $this->repertoire->spotifyConnectionForArtist($artist->fresh()),
        ]);
    }

    public function importSpotify(Request $request): JsonResponse
    {
        return $this->manageImportSpotify($request, $this->repertoire->ownedArtistForUser($request->user()));
    }

    public function manageImportSpotify(Request $request, Artist $artist): JsonResponse
    {
        abort_unless($this->repertoire->spotifyConfigured(), 503, 'Spotify import is not configured on this server.');

        $validated = $request->validate([
            'spotify_url' => ['required', 'string', 'max:500'],
        ]);

        $this->repertoire->assertCanEdit($request->user(), $artist);
        $url = trim($validated['spotify_url']);

        try {
            if ($this->spotify->extractPlaylistId($url) !== null) {
                $result = $this->repertoire->importSpotifyPlaylist($artist, $url);

                return response()->json([
                    'data' => collect($result['songs'])
                        ->map(fn (ArtistSong $song) => $this->repertoire->serializeOwner($song))
                        ->values()
                        ->all(),
                    'summary' => [
                        'created' => $result['created'],
                        'skipped' => $result['skipped'],
                    ],
                ], 201);
            }

            $meta = $this->spotify->trackFromUrl($url);
            abort_unless($meta, 422, 'Paste a valid Spotify track or playlist URL.');

            $song = $this->repertoire->createSongFromSpotifyMeta($artist, $meta);

            return response()->json([
                'data' => $this->repertoire->serializeOwner($song),
            ], 201);
        } catch (\RuntimeException) {
            abort(502, 'Spotify lookup failed. Check server credentials or use a public link.');
        }
    }

    public function addSpotifyTrack(Request $request): JsonResponse
    {
        return $this->manageAddSpotifyTrack($request, $this->repertoire->ownedArtistForUser($request->user()));
    }

    public function manageAddSpotifyTrack(Request $request, Artist $artist): JsonResponse
    {
        abort_unless($this->repertoire->spotifyConfigured(), 503, 'Spotify import is not configured on this server.');

        $validated = $request->validate([
            'spotify_id' => ['required', 'string', 'max:64'],
        ]);

        $this->repertoire->assertCanEdit($request->user(), $artist);

        try {
            $meta = $this->spotify->trackById($validated['spotify_id']);
        } catch (\RuntimeException) {
            abort(502, 'Spotify lookup failed. Check server credentials.');
        }
        abort_unless($meta, 422, 'Spotify track not found.');

        $song = $this->repertoire->createSongFromSpotifyMeta($artist, $meta);

        return response()->json([
            'data' => $this->repertoire->serializeOwner($song),
        ], 201);
    }
}
