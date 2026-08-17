<?php

namespace App\Services\Fixr;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * FIXR organiser events feed client.
 *
 * Official API is account-scoped (your organiser listings), not a public
 * local-events directory. Set FIXR_EVENTS_URL + FIXR_API_TOKEN once FIXR
 * provides them.
 */
class FixrApiClient
{
    public function isConfigured(): bool
    {
        $token = config('fixr.api_token');
        $url = config('fixr.events_url');

        return is_string($token) && trim($token) !== ''
            && is_string($url) && trim($url) !== '';
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listEvents(): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'FIXR is not configured. Set FIXR_API_TOKEN and FIXR_EVENTS_URL (from FIXR — organiser feed only).'
            );
        }

        try {
            $response = Http::timeout((int) config('fixr.timeout_seconds', 45))
                ->withToken((string) config('fixr.api_token'))
                ->acceptJson()
                ->get((string) config('fixr.events_url'))
                ->throw();
        } catch (RequestException $e) {
            $body = $e->response?->body() ?? '';
            throw new RuntimeException(
                'FIXR API failed (HTTP '.($e->response?->status() ?? 0).'): '.mb_substr($body, 0, 300),
                previous: $e
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            return [];
        }

        // Tolerate either a bare list or { "events": [...] } / { "results": [...] }.
        if (array_is_list($json)) {
            return array_values(array_filter($json, 'is_array'));
        }

        foreach (['events', 'results', 'data'] as $key) {
            if (isset($json[$key]) && is_array($json[$key])) {
                return array_values(array_filter($json[$key], 'is_array'));
            }
        }

        return [];
    }
}
