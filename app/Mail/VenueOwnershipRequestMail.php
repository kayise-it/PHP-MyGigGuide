<?php

namespace App\Mail;

use App\Models\Venue;
use App\Models\VenueOwnerRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VenueOwnershipRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $venue;
    public $request;
    public $requester;

    public function __construct(Venue $venue, VenueOwnerRequest $request, User $requester)
    {
        $this->venue = $venue;
        $this->request = $request;
        $this->requester = $requester;
    }

    public function build()
    {
        return $this->subject("New Venue Ownership Request - {$this->venue->name}")
                    ->view('emails.venue-ownership-request')
                    ->with([
                        'venue' => $this->venue,
                        'request' => $this->request,
                        'requester' => $this->requester,
                    ]);
    }
}

