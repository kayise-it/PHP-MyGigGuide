<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Venue;
use App\Models\VenueOwnerRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VenueOwnershipRequestNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Venue $venue,
        public VenueOwnerRequest $venueRequest,
        public User $requester
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'venue_ownership_request',
            'venue_id' => $this->venue->id,
            'venue_name' => $this->venue->name,
            'venue_slug' => null,
            'request_id' => $this->venueRequest->id,
            'requester_id' => $this->requester->id,
            'requester_name' => $this->requester->name,
            'requester_email' => $this->requester->email,
            'reason' => $this->venueRequest->reason,
            'requested_at' => $this->venueRequest->requested_at?->toIso8601String(),
            'message' => "{$this->requester->name} requested to co-own the venue \"{$this->venue->name}\".",
            'url' => route('venues.show', ['venue' => $this->venue->id]),
        ];
    }
}
