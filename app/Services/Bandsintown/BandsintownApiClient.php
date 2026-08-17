<?php

namespace App\Services\Bandsintown;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BandsintownApiClient
{
    public function isConfigured(): bool
    {
        $id = config('bandsintown.app_id');

        return is_string($id) && trim($id) !== '';
    }

    /**
     * Upcoming events for one artist (by name).
     *
     * @return list<array<string, mixed>>
     */
    public function artistEvents(string $artistName, string $date = 'upcoming'): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('BANDSINTOWN_APP_ID is not set in .env');
        }

        $artistName = trim($artistName);
        if ($artistName === '') {
            throw new RuntimeException('Artist name is required.');
        }

        // Bandsintown path encoding for special characters.
        $encoded = rawurlencode($artistName);
        $encoded = str_replace(
            ['%2F', '%3F', '%2A', '%22'],
            ['%252F', '%253F', '%252A', '%27C'],
            $encoded
        );

        $url = config('bandsintown.base_url').'/artists/'.$encoded.'/events';

        try {
            $response = Http::timeout((int) config('bandsintown.timeout_seconds', 45))
                ->acceptJson()
                ->get($url, [
                    'app_id' => (string) config('bandsintown.app_id'),
                    'date' => $date,
                ])
                ->throw();
        } catch (RequestException $e) {
            $body = $e->response?->body() ?? '';
            throw new RuntimeException(
                'Bandsintown API failed (HTTP '.($e->response?->status() ?? 0).'): '.mb_substr($body, 0, 300),
                previous: $e
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            return [];
        }

        // Error payloads are sometimes objects with "errorMessage".
        if (isset($json['errorMessage']) || isset($json['Message'])) {
            $msg = (string) ($json['errorMessage'] ?? $json['Message'] ?? 'unknown error');
            throw new RuntimeException('Bandsintown API error: '.$msg);
        }

        return array_values(array_filter($json, 'is_array'));
    }
}
