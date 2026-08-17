<?php

namespace App\Services\Spotify;

use App\Models\Artist;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SpotifyOAuthService
{
    public function isConfigured(): bool
    {
        return filled(config('spotify.client_id'))
            && filled(config('spotify.client_secret'))
            && filled(config('spotify.redirect_uri'));
    }

    public function authorizationUrl(int $userId, int $artistId): string
    {
        abort_unless($this->isConfigured(), 503, 'Spotify connect is not configured on this server.');

        $state = Crypt::encryptString(json_encode([
            'user_id' => $userId,
            'artist_id' => $artistId,
            'exp' => now()->addMinutes(15)->timestamp,
        ], JSON_THROW_ON_ERROR));

        $query = http_build_query([
            'client_id' => (string) config('spotify.client_id'),
            'response_type' => 'code',
            'redirect_uri' => (string) config('spotify.redirect_uri'),
            'scope' => (string) config('spotify.scopes'),
            'state' => $state,
            'show_dialog' => 'true',
        ]);

        return 'https://accounts.spotify.com/authorize?'.$query;
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    public function exchangeAuthorizationCode(string $code): array
    {
        return $this->tokenRequest([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => (string) config('spotify.redirect_uri'),
        ]);
    }

    public function connectArtistFromCallback(Artist $artist, string $code): void
    {
        $tokens = $this->exchangeAuthorizationCode($code);

        $artist->forceFill([
            'spotify_refresh_token' => $tokens['refresh_token'],
            'spotify_connected_at' => now(),
        ])->save();

        Cache::forget($this->accessTokenCacheKey($artist->id));
    }

    public function disconnectArtist(Artist $artist): void
    {
        Cache::forget($this->accessTokenCacheKey($artist->id));

        $artist->forceFill([
            'spotify_refresh_token' => null,
            'spotify_connected_at' => null,
        ])->save();
    }

    public function artistIsConnected(Artist $artist): bool
    {
        return filled($artist->spotify_refresh_token);
    }

    public function accessTokenForArtist(Artist $artist): ?string
    {
        if (! filled($artist->spotify_refresh_token)) {
            return null;
        }

        return Cache::remember($this->accessTokenCacheKey($artist->id), now()->addMinutes(50), function () use ($artist) {
            $tokens = $this->refreshAccessToken((string) $artist->spotify_refresh_token);

            if (($tokens['refresh_token'] ?? '') !== '') {
                $artist->forceFill([
                    'spotify_refresh_token' => $tokens['refresh_token'],
                ])->save();
            }

            return $tokens['access_token'];
        });
    }

    public function userIdFromState(string $state): ?int
    {
        return $this->oauthContextFromState($state)['user_id'] ?? null;
    }

    /**
     * @return array{user_id: int, artist_id: int}|null
     */
    public function oauthContextFromState(string $state): ?array
    {
        try {
            $payload = json_decode(Crypt::decryptString($state), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException) {
            return null;
        }

        if (! is_array($payload)) {
            return null;
        }

        $userId = (int) ($payload['user_id'] ?? 0);
        $artistId = (int) ($payload['artist_id'] ?? 0);
        $exp = (int) ($payload['exp'] ?? 0);
        if ($userId <= 0 || $artistId <= 0 || $exp < now()->timestamp) {
            return null;
        }

        return [
            'user_id' => $userId,
            'artist_id' => $artistId,
        ];
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    private function refreshAccessToken(string $refreshToken): array
    {
        return $this->tokenRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * @param  array<string, string>  $fields
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    private function tokenRequest(array $fields): array
    {
        abort_unless($this->isConfigured(), 503, 'Spotify connect is not configured on this server.');

        $clientId = (string) config('spotify.client_id');
        $clientSecret = (string) config('spotify.client_secret');

        try {
            $response = Http::timeout((int) config('spotify.timeout_seconds', 20))
                ->asForm()
                ->withBasicAuth($clientId, $clientSecret)
                ->post((string) config('spotify.token_url'), $fields)
                ->throw();
        } catch (RequestException $e) {
            throw new RuntimeException('Spotify token exchange failed.', previous: $e);
        }

        $accessToken = trim((string) $response->json('access_token'));
        $refreshToken = trim((string) $response->json('refresh_token'));
        $expiresIn = (int) $response->json('expires_in');

        if ($accessToken === '') {
            throw new RuntimeException('Spotify token response missing access_token.');
        }

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $expiresIn,
        ];
    }

    private function accessTokenCacheKey(int $artistId): string
    {
        return 'spotify_user_access_token_artist_'.$artistId;
    }
}
