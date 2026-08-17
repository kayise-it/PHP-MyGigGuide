<?php

namespace App\Services\Quicket;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class QuicketApiClient
{
    public function isConfigured(): bool
    {
        $key = config('quicket.api_key');

        return is_string($key) && trim($key) !== '';
    }

    /**
     * Fetch one page of public events.
     *
     * @param  array{page?: int, page_size?: int, categories?: list<int>|null, last_modified?: string|null}  $options
     * @return array{results: list<array<string, mixed>>, pageSize: int, pages: int, records: int}
     */
    public function listEvents(array $options = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('QUICKET_API_KEY is not set in .env');
        }

        $page = max(1, (int) ($options['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($options['page_size'] ?? config('quicket.page_size', 25))));
        $categories = $options['categories'] ?? config('quicket.categories', [1]);
        if (! is_array($categories) || $categories === []) {
            $categories = [1];
        }

        $query = [
            'api_key' => (string) config('quicket.api_key'),
            'page' => $page,
            'pageSize' => $pageSize,
            'categories' => implode(',', array_map('intval', $categories)),
        ];

        if (! empty($options['last_modified'])) {
            $query['lastModified'] = (string) $options['last_modified'];
        }

        $url = (string) config('quicket.base_url');

        try {
            $response = Http::timeout((int) config('quicket.timeout_seconds', 60))
                ->acceptJson()
                ->get($url, $query)
                ->throw();
        } catch (RequestException $e) {
            $body = $e->response?->body() ?? '';
            throw new RuntimeException(
                'Quicket API request failed (HTTP '.($e->response?->status() ?? 0).'): '.mb_substr($body, 0, 300),
                previous: $e
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Quicket API returned non-JSON.');
        }

        $results = $json['results'] ?? [];
        if (! is_array($results)) {
            $results = [];
        }

        return [
            'results' => array_values(array_filter($results, 'is_array')),
            'pageSize' => (int) ($json['pageSize'] ?? $pageSize),
            'pages' => (int) ($json['pages'] ?? 1),
            'records' => (int) ($json['records'] ?? count($results)),
        ];
    }

    /**
     * Fetch a single event by Quicket numeric id.
     *
     * @return array<string, mixed>|null
     */
    public function getEvent(int|string $quicketId): ?array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('QUICKET_API_KEY is not set in .env');
        }

        $id = trim((string) $quicketId);
        if ($id === '' || ! ctype_digit($id)) {
            return null;
        }

        $url = rtrim((string) config('quicket.base_url'), '/').'/'.$id;

        try {
            $response = Http::timeout((int) config('quicket.timeout_seconds', 60))
                ->acceptJson()
                ->get($url, [
                    'api_key' => (string) config('quicket.api_key'),
                ])
                ->throw();
        } catch (RequestException $e) {
            $status = $e->response?->status();
            if ($status === 404) {
                return null;
            }
            $body = $e->response?->body() ?? '';
            throw new RuntimeException(
                'Quicket API request failed (HTTP '.($status ?? 0).'): '.mb_substr($body, 0, 300),
                previous: $e
            );
        }

        $json = $response->json();
        if (! is_array($json)) {
            return null;
        }

        if (isset($json['imageUrl']) || isset($json['name']) || isset($json['id'])) {
            return $json;
        }
        if (isset($json['results'][0]) && is_array($json['results'][0])) {
            return $json['results'][0];
        }

        return $json;
    }
}
