<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use App\Models\EmailTemplate;
use Illuminate\Queue\SerializesModels;

/**
 * Admin-initiated claim invitation email.
 * Sent to unclaimed entity's contact email inviting them to register and claim.
 */
class ClaimInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $entity;
    public $entityType;
    public $entityName;
    public $registerUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Model $entity)
    {
        $this->entity = $entity;
        $this->entityType = $entity->getClaimableType();
        $this->entityName = $entity->getDisplayName();
        
        // Create registration URL with email pre-filled
        $email = $entity->getClaimEmail();
        $this->registerUrl = route('register') . '?email=' . urlencode($email ?? '');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $typeLabel = ucfirst($this->entityType);
        $subject = "🎵 Claim Your {$typeLabel} Profile on My Gig Guide";

        $template = EmailTemplate::forKey('claim_invitation');

        if ($template) {
            $html = $template->render([
                'entity' => $this->entity,
                'entityType' => $this->entityType,
                'entityName' => $this->entityName,
                'registerUrl' => $this->registerUrl,
            ]);

            $subject = $template->subject ?: $subject;

            return $this->subject($subject)
                ->view('emails.dynamic-template')
                ->with([
                    'html' => $html,
                    'subject' => $subject,
                ]);
        }

        // Fallback to static Blade view if no template has been configured.
        return $this->subject($subject)
            ->view('emails.claim-invitation')
            ->with([
                'entity' => $this->entity,
                'entityType' => $this->entityType,
                'entityName' => $this->entityName,
                'registerUrl' => $this->registerUrl,
            ]);
    }
}


