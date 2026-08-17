<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaimNotificationService
{
    public function notifyPendingClaim(Model $entity, string $type, int $userId, ?string $message = null): void
    {
        if (! config('services.evolution.enabled')) {
            return;
        }

        $adminNumber = config('services.evolution.admin_number');
        if (! $adminNumber) {
            return;
        }

        $name = method_exists($entity, 'getDisplayName')
            ? $entity->getDisplayName()
            : (string) ($entity->name ?? $entity->id);

        $text = "MGG claim request\n"
            ."Type: {$type}\n"
            ."Page: {$name}\n"
            ."User ID: {$userId}\n"
            .($message ? "Message: {$message}\n" : '')
            .'Review: '.config('app.url').'/admin/unclaimed';

        $this->sendWhatsApp($adminNumber, $text);
    }

    private function sendWhatsApp(string $number, string $text): void
    {
        $baseUrl = rtrim((string) config('services.evolution.url'), '/');
        $instance = config('services.evolution.instance');

        if (! $baseUrl || ! $instance) {
            return;
        }

        try {
            $response = Http::timeout(10)->post("{$baseUrl}/message/sendText/{$instance}", [
                'number' => $number,
                'text' => $text,
            ]);

            if (! $response->successful()) {
                Log::warning('Evolution WhatsApp claim alert failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Evolution WhatsApp claim alert error: '.$e->getMessage());
        }
    }
}
