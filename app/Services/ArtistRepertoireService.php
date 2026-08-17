<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\ArtistSong;
use App\Models\User;
use App\Models\YoutubeVideo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class ArtistRepertoireService
{
    public function __construct(
        private readonly ArtistPageService $artistPages,
        private readonly YoutubeVideoService $youtube,
        private readonly \App\Services\Spotify\SpotifyApiClient $spotify,
        private readonly \App\Services\Spotify\SpotifyOAuthService $spotifyOAuth,
    ) {}

    /**
     * @return Collection<int, ArtistSong>
     */
    public function songsForArtist(Artist $artist): Collection
    {
        return $artist->songs()->orderBy('sort_order')->orderBy('title')->get();
    }

    public function assertCanEdit(User $user, Artist $artist): void
    {
        abort_unless($this->artistPages->userCanEditPage($user, $artist), 403, 'You cannot edit this artist page.');
    }

    public function ownedArtistForUser(User $user): Artist
    {
        $user->loadMissing('artist');

        if (! $user->artist) {
            abort(404, 'No artist page linked to your account.');
        }

        $this->assertCanEdit($user, $user->artist);

        return $user->artist;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSong(Artist $artist, array $data): ArtistSong
    {
        $title = trim((string) ($data['title'] ?? ''));
        $youtubeUrl = trim((string) ($data['youtube_url'] ?? ''));
        $spotifyUrl = trim((string) ($data['spotify_url'] ?? ''));

        if ($title === '' && $spotifyUrl !== '') {
            abort_unless($this->spotify->isConfigured(), 503, 'Spotify is not configured on this server.');

            if ($this->spotify->extractPlaylistId($spotifyUrl) !== null) {
                abort(422, 'Use Import link for Spotify playlists (not Add song).');
            }

            try {
                $meta = $this->spotify->trackFromUrl($spotifyUrl);
            } catch (RuntimeException $e) {
                abort(502, 'Spotify lookup failed. Check server credentials or use a public track link.');
            }
            abort_unless($meta, 422, 'Invalid Spotify track URL. Use a track link, or Import link for playlists.');
            $title = $meta['title'];
            $data['original_artist'] = $data['original_artist'] ?? $meta['original_artist'];
            $data['reference_spotify_id'] = $meta['spotify_id'];
        }

        if ($title === '' && $youtubeUrl !== '') {
            $videoId = YoutubeVideo::extractVideoId($youtubeUrl);
            if ($videoId) {
                $title = $this->youtube->fetchTitle($videoId) ?? '';
                $data['reference_youtube_id'] = $videoId;
            }
        }

        abort_if($title === '', 422, 'Song title is required.');

        $nextOrder = ((int) $artist->songs()->max('sort_order')) + 1;

        return $artist->songs()->create([
            'title' => $title,
            'original_artist' => $this->nullableString($data['original_artist'] ?? null),
            'is_original' => (bool) ($data['is_original'] ?? false),
            'sort_order' => $nextOrder,
            'reference_youtube_id' => $this->nullableString($data['reference_youtube_id'] ?? null),
            'reference_spotify_id' => $this->nullableString($data['reference_spotify_id'] ?? null),
            'notes' => $this->nullableString($data['notes'] ?? null),
        ]);
    }

    /**
     * @return array{created: int, skipped: int, songs: list<ArtistSong>}
     */
    public function bulkCreateFromText(Artist $artist, string $text): array
    {
        $parsed = $this->parseBulkLines($text);
        $created = 0;
        $skipped = 0;
        $songs = [];
        $nextOrder = ((int) $artist->songs()->max('sort_order')) + 1;

        $existing = $artist->songs()
            ->get(['title', 'original_artist'])
            ->map(fn (ArtistSong $song) => $this->songKey($song->title, $song->original_artist))
            ->flip();

        foreach ($parsed as $row) {
            $key = $this->songKey($row['title'], $row['original_artist']);
            if ($existing->has($key)) {
                $skipped++;

                continue;
            }

            $song = $artist->songs()->create([
                'title' => $row['title'],
                'original_artist' => $row['original_artist'],
                'is_original' => $row['is_original'] ?? false,
                'sort_order' => $nextOrder++,
            ]);
            $existing->put($key, true);
            $songs[] = $song;
            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'songs' => $songs,
        ];
    }

    /**
     * @return list<array{title: string, original_artist: ?string, spotify_id: string}>
     */
    public function searchSpotifyTracks(string $query, int $limit = 10): array
    {
        abort_unless($this->spotify->isConfigured(), 503, 'Spotify is not configured on this server.');

        try {
            return $this->spotify->searchTracks($query, $limit);
        } catch (RuntimeException) {
            abort(502, 'Spotify search failed. Check server credentials.');
        }
    }

    /**
     * @return array{created: int, skipped: int, songs: list<ArtistSong>}
     */
    public function importSpotifyPlaylist(Artist $artist, string $playlistUrl): array
    {
        abort_unless($this->spotify->isConfigured(), 503, 'Spotify is not configured on this server.');
        abort_unless($this->spotifyOAuth->artistIsConnected($artist), 422, 'Connect your Spotify account first to import playlists. Track links and Search Spotify still work without connecting.');

        $accessToken = $this->spotifyOAuth->accessTokenForArtist($artist);
        abort_unless($accessToken, 422, 'Spotify connection expired. Tap Connect Spotify and try again.');

        try {
            $tracks = $this->spotify->playlistTracks($playlistUrl, $accessToken);
        } catch (RuntimeException) {
            abort(502, 'Spotify playlist import failed. Check your connection and playlist link.');
        }
        abort_if($tracks === [], 422, 'No tracks found in that Spotify playlist.');

        $created = 0;
        $skipped = 0;
        $songs = [];
        $nextOrder = ((int) $artist->songs()->max('sort_order')) + 1;

        $existing = $artist->songs()
            ->get(['title', 'original_artist', 'reference_spotify_id'])
            ->map(fn (ArtistSong $song) => $song->reference_spotify_id
                ?: $this->songKey($song->title, $song->original_artist))
            ->flip();

        foreach ($tracks as $track) {
            $key = $track['spotify_id'] ?: $this->songKey($track['title'], $track['original_artist']);
            if ($existing->has($key)) {
                $skipped++;

                continue;
            }

            $song = $artist->songs()->create([
                'title' => $track['title'],
                'original_artist' => $track['original_artist'],
                'sort_order' => $nextOrder++,
                'reference_spotify_id' => $track['spotify_id'],
            ]);
            $existing->put($key, true);
            $songs[] = $song;
            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'songs' => $songs,
        ];
    }

    /**
     * @param  array{title: string, original_artist: ?string, spotify_id: string}  $meta
     */
    public function createSongFromSpotifyMeta(Artist $artist, array $meta): ArtistSong
    {
        $existing = $artist->songs()
            ->where('reference_spotify_id', $meta['spotify_id'])
            ->first();
        abort_if($existing, 422, 'That song is already in your repertoire.');

        $nextOrder = ((int) $artist->songs()->max('sort_order')) + 1;

        return $artist->songs()->create([
            'title' => $meta['title'],
            'original_artist' => $meta['original_artist'],
            'sort_order' => $nextOrder,
            'reference_spotify_id' => $meta['spotify_id'],
        ]);
    }

    public function spotifyConfigured(): bool
    {
        return $this->spotify->isConfigured();
    }

    /**
     * @return array{connected: bool, connected_at: ?string}
     */
    public function spotifyConnectionForArtist(Artist $artist): array
    {
        return [
            'connected' => $this->spotifyOAuth->artistIsConnected($artist),
            'connected_at' => $artist->spotify_connected_at?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSong(ArtistSong $song, array $data): ArtistSong
    {
        if (array_key_exists('title', $data)) {
            $title = trim((string) $data['title']);
            abort_if($title === '', 422, 'Song title is required.');
            $song->title = $title;
        }

        if (array_key_exists('original_artist', $data)) {
            $song->original_artist = $this->nullableString($data['original_artist']);
        }

        if (array_key_exists('is_original', $data)) {
            $song->is_original = (bool) $data['is_original'];
        }

        if (array_key_exists('notes', $data)) {
            $song->notes = $this->nullableString($data['notes']);
        }

        if (array_key_exists('youtube_url', $data)) {
            $url = trim((string) $data['youtube_url']);
            if ($url === '') {
                $song->reference_youtube_id = null;
            } else {
                $videoId = YoutubeVideo::extractVideoId($url);
                abort_unless($videoId, 422, 'Invalid YouTube URL.');
                $song->reference_youtube_id = $videoId;
                if (! array_key_exists('title', $data)) {
                    $fetched = $this->youtube->fetchTitle($videoId);
                    if ($fetched) {
                        $song->title = $fetched;
                    }
                }
            }
        }

        $song->save();

        return $song->fresh();
    }

    public function deleteSong(ArtistSong $song): void
    {
        $song->delete();
    }

    /**
     * @param  list<int>  $orderedIds
     */
    public function reorderSongs(Artist $artist, array $orderedIds): void
    {
        $ids = collect($orderedIds)->map(fn ($id) => (int) $id)->filter()->values();
        $ownedIds = $artist->songs()->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id);

        abort_unless($ownedIds->count() === $ids->count(), 422, 'Invalid song ids for this artist.');

        foreach ($ids as $index => $id) {
            ArtistSong::query()->where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * @return list<array{title: string, original_artist: ?string, is_original: bool}>
     */
    public function parseBulkLines(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $out = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/^(.+?)\s+-\s+(.+)$/', $line, $matches) === 1) {
                $out[] = [
                    'title' => trim($matches[2]),
                    'original_artist' => trim($matches[1]),
                    'is_original' => false,
                ];

                continue;
            }

            $out[] = [
                'title' => $line,
                'original_artist' => null,
                'is_original' => false,
            ];
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public function serializePublic(ArtistSong $song): array
    {
        return [
            'id' => $song->id,
            'title' => $song->title,
            'original_artist' => $song->original_artist,
            'is_original' => (bool) $song->is_original,
            'sort_order' => (int) $song->sort_order,
            'reference_youtube_id' => $song->reference_youtube_id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeOwner(ArtistSong $song): array
    {
        return [
            ...$this->serializePublic($song),
            'notes' => $song->notes,
            'reference_spotify_id' => $song->reference_spotify_id,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function songKey(string $title, ?string $originalArtist): string
    {
        return Str::lower(trim($title)).'|'.Str::lower(trim($originalArtist ?? ''));
    }
}
