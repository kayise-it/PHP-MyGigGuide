<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Artist;
use App\Models\User;

class ClaimRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $entity;
    public $entityType;
    public $entityName;
    // Backward compatible properties
    public $artist;
    public $user;
    public $reason;

    public function __construct(Model $entity, $reason = null)
    {
        $this->entity = $entity;
        $this->entityType = $entity->getClaimableType();
        $this->entityName = $entity->getDisplayName();
        $this->reason = $reason;
        
        // Backward compatibility for existing views
        if ($entity instanceof Artist) {
            $this->artist = $entity;
        }
        $this->user = null;
    }

    public function build()
    {
        $typeLabel = ucfirst($this->entityType);
        
        return $this->subject("{$typeLabel} Profile Claim Rejected - My Gig Guide")
                    ->view('emails.claim-rejected')
                    ->with([
                        'entity' => $this->entity,
                        'entityType' => $this->entityType,
                        'entityName' => $this->entityName,
                        'artist' => $this->artist, // Backward compatibility
                        'user' => $this->user,
                        'reason' => $this->reason,
                    ]);
    }
}
