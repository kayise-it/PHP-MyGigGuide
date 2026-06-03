<?php

namespace App\Support;

class FirebaseWeb
{
    public static function isConfigured(): bool
    {
        $apiKey = config('services.firebase.web_api_key');

        return is_string($apiKey) && $apiKey !== '';
    }

    /**
     * @return array<string, string>|null
     */
    public static function clientConfig(): ?array
    {
        if (! self::isConfigured()) {
            return null;
        }

        return [
            'apiKey' => (string) config('services.firebase.web_api_key'),
            'authDomain' => (string) config('services.firebase.auth_domain'),
            'projectId' => (string) config('services.firebase.project_id'),
            'appId' => (string) config('services.firebase.web_app_id'),
        ];
    }
}
