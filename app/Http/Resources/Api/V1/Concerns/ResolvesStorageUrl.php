<?php

namespace App\Http\Resources\Api\V1\Concerns;

use Illuminate\Support\Facades\Storage;

trait ResolvesStorageUrl
{
    protected static function publicStorageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $url = Storage::disk('public')->url($path);

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return rtrim(config('app.url'), '/').'/'.ltrim($url, '/');
    }

    /**
     * @param  array<int, string>|null  $paths
     * @return array<int, string>
     */
    protected static function publicStorageUrls(?array $paths): array
    {
        if ($paths === null || $paths === []) {
            return [];
        }

        return collect($paths)
            ->filter(fn ($p) => is_string($p) && $p !== '')
            ->map(fn (string $p) => self::publicStorageUrl($p))
            ->filter()
            ->values()
            ->all();
    }
}
