<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * One-time tokens so a Sanctum-authenticated app user can open the website logged in.
 */
class AppWebSessionService
{
    private const CACHE_PREFIX = 'app_web_session:';

    public function ttlSeconds(): int
    {
        return (int) config('app_web_session.ttl_seconds', 300);
    }

    /**
     * @return array{token: string, expires_at: int}
     */
    public function issue(User $user): array
    {
        $plainToken = Str::random(64);
        $ttl = $this->ttlSeconds();

        Cache::put(
            self::CACHE_PREFIX.hash('sha256', $plainToken),
            $user->id,
            $ttl,
        );

        return [
            'token' => $plainToken,
            'expires_at' => now()->addSeconds($ttl)->getTimestamp(),
        ];
    }

    public function consume(string $plainToken): ?User
    {
        $plainToken = trim($plainToken);
        if ($plainToken === '') {
            return null;
        }

        $cacheKey = self::CACHE_PREFIX.hash('sha256', $plainToken);
        $userId = Cache::pull($cacheKey);

        if ($userId === null) {
            return null;
        }

        $user = User::query()->find($userId);
        if (! $user || ! $user->is_active) {
            return null;
        }

        return $user;
    }

    public function sanitizeRedirect(?string $redirect): string
    {
        $fallback = route('dashboard');
        $redirect = trim((string) $redirect);

        if ($redirect === '') {
            return $fallback;
        }

        if (str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            return $redirect;
        }

        $parsed = parse_url($redirect);
        if (! is_array($parsed) || empty($parsed['host'])) {
            return $fallback;
        }

        $allowedHosts = array_filter(array_unique([
            parse_url(config('app.url'), PHP_URL_HOST),
            parse_url(url('/'), PHP_URL_HOST),
            request()->getHost(),
        ]));

        if (! in_array($parsed['host'], $allowedHosts, true)) {
            return $fallback;
        }

        $path = $parsed['path'] ?? '/';
        $query = isset($parsed['query']) ? '?'.$parsed['query'] : '';

        return $path.$query;
    }

    public function buildLoginUrl(string $plainToken, string $redirect): string
    {
        $params = ['token' => $plainToken];
        if ($redirect !== route('dashboard')) {
            $params['redirect'] = $redirect;
        }

        return route('auth.app-session', $params);
    }
}
