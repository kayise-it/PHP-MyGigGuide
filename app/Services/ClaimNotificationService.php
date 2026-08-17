<?php

namespace App\Services;

use App\Mail\ClaimPendingAdminMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClaimNotificationService
{
    /**
     * Notify the admin about a new claim that needs review.
     *
     * Sends:
     *   1. Admin email (immediate — works once ARTIST_CLAIM_ADMIN_EMAIL is set in .env)
     *   2. WhatsApp message via Evolution API (requires EVOLUTION_API_URL, EVOLUTION_API_KEY,
     *      and EVOLUTION_ADMIN_NUMBER to be set — silently skipped otherwise)
     *
     * @param  string  $claimType  'manual' (needs admin review) or 'email_match' (auto-approved)
     */
    public function notifyAdmin(Model $entity, User $claimant, ?string $message = null, string $claimType = 'manual'): void
    {
        if (! config('artist_claims.notify_admins', true)) {
            return;
        }

        $this->sendAdminEmail($entity, $claimant, $message);
        $this->sendWhatsApp($entity, $claimant, $claimType);
    }

    protected function sendAdminEmail(Model $entity, User $claimant, ?string $message): void
    {
        $adminEmail = config('artist_claims.admin_email');

        if (blank($adminEmail)) {
            Log::warning('ClaimNotificationService: ARTIST_CLAIM_ADMIN_EMAIL is not set — admin email skipped.');

            return;
        }

        try {
            Mail::to($adminEmail)->send(new ClaimPendingAdminMail($entity, $claimant, $message));
        } catch (\Exception $e) {
            Log::error('Claim admin email failed: '.$e->getMessage());
        }
    }

    protected function sendWhatsApp(Model $entity, User $claimant, string $claimType): void
    {
        $baseUrl  = config('services.evolution.url');
        $instance = config('services.evolution.instance');
        $apiKey   = config('services.evolution.api_key');
        $number   = config('services.evolution.admin_number');

        if (empty($baseUrl) || empty($apiKey) || empty($number)) {
            return;
        }

        $typeLabel = ucfirst($entity->getClaimableType());
        $entityName = $entity->getDisplayName();
        $claimantName = filled($claimant->name) ? $claimant->name : $claimant->email;
        $claimTypeLabel = $claimType === 'manual' ? 'manual request (needs review)' : 'email-match (auto-approved)';

        $reviewUrl = route('admin.unclaimed.edit', [
            'type' => $entity->getClaimableType(),
            'id' => $entity->id,
        ]);

        $message = "New {$typeLabel} claim: {$entityName}\n"
            ."Claimant: {$claimantName} ({$claimant->email})\n"
            ."Type: {$claimTypeLabel}\n"
            ."Review: {$reviewUrl}";

        try {
            Http::withHeaders(['apikey' => $apiKey])
                ->timeout(10)
                ->post("{$baseUrl}/message/sendText/{$instance}", [
                    'number' => $number,
                    'text'   => $message,
                ]);
        } catch (\Exception $e) {
            Log::warning('ClaimNotificationService: Evolution WhatsApp failed — '.$e->getMessage());
        }
    }
}
