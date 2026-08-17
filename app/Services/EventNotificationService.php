<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventNotificationService
{
    /**
     * Send the admin a WhatsApp alert when a crowd-sourced event is created.
     *
     * Silently skipped when:
     *   - the creator's ID is in EVENT_NOTIFY_EXCLUDE_USER_IDS (Dave, Quicket system user, etc.)
     *   - Evolution API env vars are not set
     */
    public function notifyAdminNewEvent(Event $event, User $creator): void
    {
        if ($this->isExcluded($creator)) {
            return;
        }

        $this->sendWhatsApp($event, $creator);
    }

    private function isExcluded(User $creator): bool
    {
        $excludedIds = config('services.event_notifications.exclude_user_ids', []);
        if (! is_array($excludedIds)) {
            return false;
        }

        return in_array((int) $creator->id, array_map('intval', $excludedIds), true);
    }

    private function sendWhatsApp(Event $event, User $creator): void
    {
        $baseUrl  = config('services.evolution.url');
        $instance = config('services.evolution.instance');
        $apiKey   = config('services.evolution.api_key');
        $number   = config('services.evolution.admin_number');

        if (empty($baseUrl) || empty($apiKey) || empty($number)) {
            return;
        }

        $event->loadMissing(['venue', 'artists']);

        $creatorLabel = filled($creator->name) ? "{$creator->name} ({$creator->email})" : $creator->email;
        $venueName    = $event->venue?->name ?? 'Unknown venue';
        $eventDate    = $event->date ? Carbon::parse($event->date)->format('D d M Y') : '?';
        $artistNames  = $event->artists->pluck('stage_name')->filter()->implode(', ');
        $editUrl      = 'https://www.mygigguide.co.za/admin/events/'.$event->id.'/edit';

        $text = "New event posted: {$event->name}\n"
            ."Date: {$eventDate}\n"
            ."Venue: {$venueName}\n"
            .($artistNames !== '' ? "Artists: {$artistNames}\n" : '')
            ."Posted by: {$creatorLabel}\n"
            ."Edit: {$editUrl}";

        try {
            Http::withHeaders(['apikey' => $apiKey])
                ->timeout(10)
                ->post("{$baseUrl}/message/sendText/{$instance}", [
                    'number' => $number,
                    'text'   => $text,
                ]);
        } catch (\Exception $e) {
            Log::warning('EventNotificationService: Evolution WhatsApp failed — '.$e->getMessage());
        }
    }
}
