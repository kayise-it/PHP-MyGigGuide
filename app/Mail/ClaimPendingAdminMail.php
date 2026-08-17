<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimPendingAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public Model $entity;

    public User $claimant;

    public string $entityType;

    public string $entityName;

    public ?string $claimMessage;

    public string $reviewUrl;

    public string $pendingListUrl;

    public function __construct(Model $entity, User $claimant, ?string $claimMessage = null)
    {
        $this->entity = $entity;
        $this->claimant = $claimant;
        $this->entityType = $entity->getClaimableType();
        $this->entityName = $entity->getDisplayName();
        $this->claimMessage = filled($claimMessage) ? trim($claimMessage) : null;
        $this->reviewUrl = route('admin.unclaimed.edit', [
            'type' => $this->entityType,
            'id' => $entity->id,
        ]);
        $this->pendingListUrl = route('admin.unclaimed.index', [
            'type' => $this->entityType,
            'status' => 'pending',
        ]);
    }

    public function build()
    {
        $typeLabel = ucfirst($this->entityType);

        return $this->subject("New {$typeLabel} claim to review — {$this->entityName}")
            ->view('emails.claim-pending-admin');
    }
}
