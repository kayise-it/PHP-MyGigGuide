<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Carbon\Carbon;

/**
 * Generic claim warning email for all entity types (Venue, Event, Organiser).
 * Note: Artist uses its own ArtistClaimWarningMail for backward compatibility.
 */
class ClaimWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $entity;
    public $entityType;
    public $entityName;
    public $user;
    public $gracePeriodEnds;
    public $disputeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Model $entity, User $user, ?Carbon $gracePeriodEnds)
    {
        $this->entity = $entity;
        $this->entityType = $entity->getClaimableType();
        $this->entityName = $entity->getDisplayName();
        $this->user = $user;
        $this->gracePeriodEnds = $gracePeriodEnds;
        
        // Create dispute URL
        try {
            $this->disputeUrl = route('contact.index') . '?' . $this->entityType . '=' . $entity->id;
        } catch (\Exception $e) {
            $this->disputeUrl = url('/contact');
        }
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $typeLabel = ucfirst($this->entityType);
        $subject = "⚠️ Someone Registered with Your Email - {$typeLabel} Profile Claim Alert";
        
        return $this->subject($subject)
                    ->view('emails.claim-warning')
                    ->with([
                        'entity' => $this->entity,
                        'entityType' => $this->entityType,
                        'entityName' => $this->entityName,
                        'user' => $this->user,
                        'gracePeriodEnds' => $this->gracePeriodEnds,
                        'disputeUrl' => $this->disputeUrl,
                    ]);
    }
}


