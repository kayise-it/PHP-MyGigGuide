<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MiggsBridgePosterService
{
    public function isConfigured(): bool
    {
        $url = config('miggs_bridge.url');
        $secret = config('miggs_bridge.poster_secret');

        return is_string($url) && $url !== '' && is_string($secret) && $secret !== '';
    }

    /**
     * Forward poster image to miggs-bridge `POST /app/parse-poster`.
     *
     * @return array<string, mixed>
     */
    public function parsePoster(UploadedFile $file, string $hint = '', ?string $attributionUser = null): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Poster reading is not configured on the server.');
        }

        $hint = mb_substr(trim((string) ($hint ?? '')), 0, 800);
        $url = config('miggs_bridge.url').'/app/parse-poster';
        $attribution = trim((string) ($attributionUser ?? ''));
        if ($attribution === '') {
            $attribution = 'MyGigGuideApp';
        }

        $response = Http::timeout((int) config('miggs_bridge.timeout_seconds', 120))
            ->withHeaders([
                'X-App-Poster-Secret' => (string) config('miggs_bridge.poster_secret'),
                'X-Miggie-App-User' => $attribution,
                'Accept' => 'application/json',
            ])
            ->attach(
                'file',
                fopen($file->getRealPath(), 'r'),
                $file->getClientOriginalName() ?: 'poster.jpg',
                ['Content-Type' => $file->getMimeType() ?: 'image/jpeg']
            )
            ->post($url, ['hint' => $hint]);

        if ($response->failed()) {
            $body = $response->json();
            $detail = is_array($body) ? ($body['detail'] ?? $body['message'] ?? null) : null;
            if (! is_string($detail) || $detail === '') {
                $detail = $response->body();
            }
            if ($response->status() === 404) {
                $detail = 'Poster scan endpoint not found on miggs-bridge — the bridge container may need updating.';
            }
            if (strlen($detail) > 280) {
                $detail = substr($detail, 0, 280).'…';
            }

            throw new RuntimeException(
                $detail !== '' ? $detail : 'Poster service unavailable (HTTP '.$response->status().').'
            );
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }
}
