<?php

namespace App\Services\Quicket;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Download Quicket listing images into public storage for event posters.
 *
 * Quicket uploads two assets per event:
 * - Listing art (API imageUrl, square e.g. *_360_360) — used in browse / mobile cards
 * - Banner art (separate asset id, *_0 landscape) — used on the event detail page
 *
 * MGG uses the listing art (upgraded to *_0 on the same asset) so app tiles match Quicket browse.
 */
class QuicketPosterService
{
    /**
     * Prefer Quicket browse/listing art over the event-page landscape banner.
     */
    public function resolveBestImageUrl(?string $apiImageUrl, ?string $eventPageUrl = null): ?string
    {
        $listing = $this->resolveListingImageUrl($apiImageUrl);
        if ($listing !== null) {
            return $listing;
        }

        $fromPage = $this->listingUrlFromEventPage($eventPageUrl);
        if ($fromPage !== null) {
            return $fromPage;
        }

        return null;
    }

    /**
     * @return string|null Relative path on the public disk, or null on failure
     */
    public function storeFromUrl(string $imageUrl, string $directory = 'imports/quicket/posters', ?string $eventPageUrl = null): ?string
    {
        $url = $this->resolveBestImageUrl($imageUrl, $eventPageUrl);
        if ($url === null || $url === '') {
            return null;
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'MyGigGuideQuicketImport/1.0',
                    'Accept' => 'image/*,*/*',
                ])
                ->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $body = $response->body();
        if ($body === '' || strlen($body) < 100) {
            return null;
        }

        $ext = $this->guessExtension($response->header('Content-Type'), $url);
        $folder = trim($directory, '/');
        Storage::disk('public')->makeDirectory($folder);
        $path = $folder.'/'.Str::random(32).'.'.$ext;
        $written = Storage::disk('public')->put($path, $body);
        if ($written !== true || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return $path;
    }

    /**
     * Whether the stored poster is wide (banner-like).
     * Landscape Quicket banners must not get a invented 2:3 poster_card crop.
     */
    public function isLandscapePoster(string $storagePath): bool
    {
        $full = Storage::disk('public')->path($storagePath);
        $info = @getimagesize($full);
        if ($info === false) {
            return false;
        }

        [$w, $h] = $info;

        return $h > 0 && ($w / $h) >= 1.25;
    }

    /**
     * API imageUrl → full-size listing asset (*_0 on the same id).
     */
    private function resolveListingImageUrl(?string $apiImageUrl): ?string
    {
        $api = $this->normalizeUrl((string) ($apiImageUrl ?? ''));
        if ($api === '') {
            return null;
        }

        $upgraded = $this->upgradeCropToFullSize($api);
        if ($upgraded !== null && $this->urlLooksReachable($upgraded)) {
            return $upgraded;
        }

        if ($this->urlLooksReachable($api)) {
            return $api;
        }

        return null;
    }

    /**
     * og:image on the event page is listing art (not the landscape banner).
     */
    private function listingUrlFromEventPage(?string $eventPageUrl): ?string
    {
        $page = $this->normalizeUrl((string) ($eventPageUrl ?? ''));
        if ($page === '' || ! str_contains($page, 'quicket.co.za')) {
            return null;
        }

        try {
            $response = Http::timeout(25)
                ->withHeaders([
                    'User-Agent' => 'MyGigGuideQuicketImport/1.0',
                    'Accept' => 'text/html,application/xhtml+xml',
                ])
                ->get($page);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $html = $response->body();
        if ($html === '') {
            return null;
        }

        if (preg_match('#property="og:image"\s+content="([^"]+)"#i', $html, $m) !== 1) {
            return null;
        }

        $og = $this->normalizeUrl(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5));
        if ($og === '') {
            return null;
        }

        $upgraded = $this->upgradeCropToFullSize($og);

        return ($upgraded !== null && $this->urlLooksReachable($upgraded))
            ? $upgraded
            : ($this->urlLooksReachable($og) ? $og : null);
    }

    /**
     * 0848847_360_360.jpeg → 0848847_0.jpeg (same asset, full size).
     */
    private function upgradeCropToFullSize(string $url): ?string
    {
        $upgraded = preg_replace('#_(\d{2,4})_(\d{2,4})\.(png|jpe?g|webp)$#i', '_0.$3', $url);
        if (! is_string($upgraded) || $upgraded === $url) {
            return null;
        }

        return $upgraded;
    }

    private function urlLooksReachable(string $url): bool
    {
        try {
            $response = Http::timeout(12)
                ->withHeaders(['User-Agent' => 'MyGigGuideQuicketImport/1.0'])
                ->head($url);
            if ($response->successful()) {
                return true;
            }

            // Some CDNs dislike HEAD — try a tiny GET.
            $get = Http::timeout(12)
                ->withHeaders(['User-Agent' => 'MyGigGuideQuicketImport/1.0', 'Range' => 'bytes=0-64'])
                ->get($url);

            return $get->successful() || $get->status() === 206;
        } catch (\Throwable) {
            return false;
        }
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (Str::startsWith($url, '//')) {
            $url = 'https:'.$url;
        }
        if (! Str::startsWith($url, ['http://', 'https://'])) {
            $url = 'https://'.ltrim($url, '/');
        }

        return $url;
    }

    private function guessExtension(?string $contentType, string $url): string
    {
        $ct = strtolower((string) $contentType);
        if (str_contains($ct, 'png')) {
            return 'png';
        }
        if (str_contains($ct, 'webp')) {
            return 'webp';
        }
        if (str_contains($ct, 'gif')) {
            return 'gif';
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $fromUrl = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($fromUrl, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return $fromUrl === 'jpeg' ? 'jpg' : $fromUrl;
        }

        return 'jpg';
    }
}
