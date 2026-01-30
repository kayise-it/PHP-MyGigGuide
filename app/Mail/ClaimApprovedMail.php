<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Artist;
use App\Models\User;

class ClaimApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $entity;
    public $entityType;
    public $entityName;
    // Backward compatible properties
    public $artist;
    public $user;

    public function __construct(Model $entity, ?User $user = null)
    {
        $this->entity = $entity;
        $this->entityType = $entity->getClaimableType();
        $this->entityName = $entity->getDisplayName();
        
        // Backward compatibility for existing views
        if ($entity instanceof Artist) {
            $this->artist = $entity;
        }
        $this->user = $user;
    }

    public function build()
    {
        $typeLabel = ucfirst($this->entityType);
        
        return $this->subject("{$typeLabel} Profile Claim Approved - My Gig Guide")
                    ->view('emails.claim-approved')
                    ->with([
                        'entity' => $this->entity,
                        'entityType' => $this->entityType,
                        'entityName' => $this->entityName,
                        'artist' => $this->artist, // Backward compatibility
                        'user' => $this->user,
                    ]);
    }
}
