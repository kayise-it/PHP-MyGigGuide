<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserSignupNotificationService
{
    /**
     * WhatsApp Dave when a new user row is created.
     *
     * Same Evolution send as new-gig alerts. Quiet when EVOLUTION_API_URL,
     * EVOLUTION_API_KEY, or EVOLUTION_ADMIN_NUMBER is blank. Never throws,
     * so a WhatsApp failure cannot block registration.
     */
    public function notifyAdmin(User $user, string $signedUpVia): void
    {
        $baseUrl  = config('services.evolution.url');
        $instance = config('services.evolution.instance');
        $apiKey   = config('services.evolution.api_key');
        $number   = config('services.evolution.admin_number');

        if (empty($baseUrl) || empty($apiKey) || empty($number)) {
            return;
        }

        $name     = filled($user->name) ? $user->name : 'Unknown';
        $username = filled($user->username) ? $user->username : 'Unknown';
        $email    = filled($user->email) ? $user->email : 'Unknown';

        $text = "New user: {$name}\n"
            ."Username: {$username}\n"
            ."Email: {$email}\n"
            ."Signed up: {$signedUpVia}";

        try {
            Http::withHeaders(['apikey' => $apiKey])
                ->timeout(10)
                ->post("{$baseUrl}/message/sendText/{$instance}", [
                    'number' => $number,
                    'text'   => $text,
                ]);
        } catch (\Exception $e) {
            Log::warning('UserSignupNotificationService: Evolution WhatsApp failed — '.$e->getMessage());
        }
    }
}
