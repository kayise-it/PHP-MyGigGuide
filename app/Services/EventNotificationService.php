<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventNotificationService
{
    public function notifyNewCrowdSourceEvent(Event $event, User $user): void
    {
        if (! config('services.evolution.enabled')) {
            return;
        }

        $excludeIds = collect(explode(',', (string) env('EVENT_NOTIFY_EXCLUDE_USER_IDS', '')))
            ->map(fn ($id) => (int) trim($id))
            ->filter(fn ($id) => $id > 0)
            ->all();

        if (in_array((int) $user->id, $excludeIds, true)) {
            return;
        }

        $adminNumber = config('services.evolution.admin_number');
        if (! $adminNumber) {
            return;
        }

        $venueName = $event->venue?->name ?? 'Unknown venue';
        $text = "MGG new event\n"
            ."Name: {$event->name}\n"
            ."Date: {$event->date} {$event->time}\n"
            ."Venue: {$venueName}\n"
            ."Posted by: {$user->name} ({$user->email})\n"
            .'Review: '.config('app.url').'/admin/events/'.$event->id;

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
                Log::warning('Evolution WhatsApp event alert failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Evolution WhatsApp event alert error: '.$e->getMessage());
        }
    }
}
