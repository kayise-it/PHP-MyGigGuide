<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Artist;
use Carbon\Carbon;

class PendingClaimNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $artist;
    public $gracePeriodEnds;

    /**
     * Create a new message instance.
     */
    public function __construct(Artist $artist, ?Carbon $gracePeriodEnds)
    {
        $this->artist = $artist;
        $this->gracePeriodEnds = $gracePeriodEnds;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Artist Profile Claim Pending - My Gig Guide';
        
        return $this->subject($subject)
                    ->view('emails.pending-claim-notice')
                    ->with([
                        'artist' => $this->artist,
                        'gracePeriodEnds' => $this->gracePeriodEnds,
                    ]);
    }
}
