<?php

namespace App\Services\Spotify;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SpotifyApiClient
{
    public function isConfigured(): bool
    {
        return filled(config('spotify.client_id')) && filled(config('spotify.client_secret'));
    }

    /**
     * @return array{title: string, original_artist: ?string, spotify_id: string}|null
     */
    public function trackFromUrl(string $url): ?array
    {
        $id = $this->extractTrackId($url);
        if ($id === null) {
            return null;
        }

        return $this->trackById($id);
    }

    /**
     * @return array{title: string, original_artist: ?string, spotify_id: string}|null
     */
    public function trackById(string $trackId): ?array
    {
        $json = $this->get('/tracks/'.urlencode($trackId), $this->marketQuery());

        return $this->normalizeTrack($json);
    }

    /**
     * @return list<array{title: string, original_artist: ?string, spotify_id: string}>
     */
    public function searchTracks(string $query, int $limit = 10): array
    {
        $query = trim($query);
        abort_if($query === '', 422, 'Search query is required.');

        $limit = max(1, min(10, $limit));

        $json = $this->get('/search', [
            'q' => $query,
            'type' => 'track',
            'limit' => $limit,
            ...$this->marketQuery(),
        ]);

        $items = $json['tracks']['items'] ?? [];
        if (! is_array($items)) {
            return [];
        }

        $out = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $normalized = $this->normalizeTrack($item);
            if ($normalized !== null) {
                $out[] = $normalized;
            }
        }

        return $out;
    }

    /**
     * @return list<array{title: string, original_artist: ?string, spotify_id: string}>
     */
    public function playlistTracks(string $playlistUrl, ?string $userAccessToken = null): array
    {
        abort_unless($userAccessToken, 422, 'Connect your Spotify account first to import playlists. Track links and Search Spotify still work without connecting.');

        $playlistId = $this->extractPlaylistId($playlistUrl);
        abort_unless($playlistId, 422, 'Invalid Spotify playlist URL.');

        $limit = (int) config('spotify.playlist_import_limit', 100);
        $out = [];
        $offset = 0;
        $pageSize = 50;
        $rawItemCount = 0;
        $nullTrackCount = 0;

        while (count($out) < $limit) {
            try {
                $json = $this->get('/playlists/'.urlencode($playlistId).'/items', [
                    'offset' => $offset,
                    'limit' => min($pageSize, $limit - count($out)),
                    'additional_types' => 'track',
                ], $userAccessToken);
            } catch (RuntimeException $e) {
                if (str_contains($e->getMessage(), 'HTTP 403')) {
                    abort(422, 'Spotify would not share this playlist. Connect the Spotify account that owns the playlist, or use Search Spotify, bulk paste, or individual track links.');
                }

                if (str_contains($e->getMessage(), 'HTTP 404')) {
                    abort(422, 'Spotify could not find that playlist. Check the link is correct and that your connected account owns it.');
                }

                throw $e;
            }

            $items = $this->playlistItemRows($json);
            if ($items === []) {
                break;
            }

            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $rawItemCount++;
                $normalized = $this->normalizePlaylistRow($row);
                if ($normalized === null) {
                    $nullTrackCount++;

                    continue;
                }
                $out[] = $normalized;
                if (count($out) >= $limit) {
                    break 2;
                }
            }

            if (empty($json['next'])) {
                break;
            }

            $offset += $pageSize;
        }

        if ($out === [] && $rawItemCount > 0) {
            abort(422, 'Spotify returned this playlist but no playable tracks are available to import. Try Search Spotify or paste individual track links.');
        }

        if ($out === [] && $nullTrackCount === 0 && $rawItemCount === 0) {
            abort(422, 'No tracks found in that Spotify playlist. It must be a playlist on the Spotify account you connected.');
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $json
     * @return list<array<string, mixed>>
     */
    private function playlistItemRows(array $json): array
    {
        $items = $json['items'] ?? null;

        if (is_array($items) && array_is_list($items)) {
            return array_values(array_filter($items, is_array(...)));
        }

        if (is_array($items) && is_array($items['items'] ?? null)) {
            return array_values(array_filter($items['items'], is_array(...)));
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{title: string, original_artist: ?string, spotify_id: string}|null
     */
    private function normalizePlaylistRow(array $row): ?array
    {
        $track = $row['track'] ?? null;
        if (is_array($track)) {
            return $this->normalizeTrack($track);
        }

        if (! array_key_exists('item', $row)) {
            return null;
        }

        $item = $row['item'];
        if ($item === null || ! is_array($item)) {
            return null;
        }

        $type = strtolower((string) ($item['type'] ?? ''));
        if ($type === '' && isset($item['id'], $item['name'])) {
            $type = 'track';
        }

        if ($type !== 'track') {
            return null;
        }

        return $this->normalizeTrack($item);
    }

    /**
     * @return array<string, string>
     */
    private function marketQuery(): array
    {
        $market = strtoupper(trim((string) config('spotify.market', 'ZA')));
        if ($market === '') {
            return [];
        }

        return ['market' => $market];
    }

    public function extractTrackId(string $url): ?string
    {
        $url = $this->normalizeSpotifyUrl($url);

        if (preg_match('~open\.spotify\.com/(?:intl-[a-z]{2}/)?track/([A-Za-z0-9]+)~', $url, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('~spotify:track:([A-Za-z0-9]+)~', $url, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    public function extractPlaylistId(string $url): ?string
    {
        $url = $this->normalizeSpotifyUrl($url);

        if (preg_match('~open\.spotify\.com/(?:intl-[a-z]{2}/)?playlist/([A-Za-z0-9]+)~', $url, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('~open\.spotify\.com/user/[^/]+/playlist/([A-Za-z0-9]+)~', $url, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('~spotify:playlist:([A-Za-z0-9]+)~', $url, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    private function normalizeSpotifyUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        // Drop query/fragment so shared links like ?si=... still parse.
        return preg_replace('~[?#].*$~', '', $url) ?? $url;
    }

    /**
     * @param  array<string, mixed>|null  $track
     * @return array{title: string, original_artist: ?string, spotify_id: string}|null
     */
    private function normalizeTrack(?array $track): ?array
    {
        if ($track === null) {
            return null;
        }

        $id = trim((string) ($track['id'] ?? ''));
        $title = trim((string) ($track['name'] ?? ''));
        if ($id === '' || $title === '') {
            return null;
        }

        $artists = $track['artists'] ?? [];
        $originalArtist = null;
        if (is_array($artists) && isset($artists[0]['name'])) {
            $originalArtist = trim((string) $artists[0]['name']);
            if ($originalArtist === '') {
                $originalArtist = null;
            }
        }

        return [
            'title' => $title,
            'original_artist' => $originalArtist,
            'spotify_id' => $id,
        ];
    }

    /**
     * @param  array<string, scalar|null>  $query
     * @return array<string, mixed>
     */
    private function get(string $path, array $query = [], ?string $accessToken = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Spotify API is not configured (SPOTIFY_CLIENT_ID / SPOTIFY_CLIENT_SECRET).');
        }

        $token = $accessToken ?? $this->accessToken();
        $url = rtrim((string) config('spotify.api_base_url'), '/').$path;

        try {
            $pending = Http::timeout((int) config('spotify.timeout_seconds', 20))
                ->acceptJson()
                ->withToken($token);

            $response = $query === []
                ? $pending->get($url)->throw()
                : $pending->get($url, $query)->throw();
        } catch (RequestException $e) {
            $body = $e->response?->body() ?? '';
            throw new RuntimeException(
                'Spotify API request failed (HTTP '.($e->response?->status() ?? 0).'): '.mb_substr($body, 0, 300),
                previous: $e
            );
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    private function accessToken(): string
    {
        return Cache::remember('spotify_client_credentials_token', now()->addMinutes(50), function () {
            $clientId = (string) config('spotify.client_id');
            $clientSecret = (string) config('spotify.client_secret');

            try {
                $response = Http::timeout((int) config('spotify.timeout_seconds', 20))
                    ->asForm()
                    ->withBasicAuth($clientId, $clientSecret)
                    ->post((string) config('spotify.token_url'), [
                        'grant_type' => 'client_credentials',
                    ])
                    ->throw();
            } catch (RequestException $e) {
                throw new RuntimeException('Spotify token request failed.', previous: $e);
            }

            $token = $response->json('access_token');
            if (! is_string($token) || $token === '') {
                throw new RuntimeException('Spotify token response missing access_token.');
            }

            return $token;
        });
    }
}
