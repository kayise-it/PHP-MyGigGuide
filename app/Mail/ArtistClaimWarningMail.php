<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Artist;
use App\Models\User;
use Carbon\Carbon;

class ArtistClaimWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $artist;
    public $user;
    public $gracePeriodEnds;
    public $disputeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Artist $artist, User $user, ?Carbon $gracePeriodEnds)
    {
        $this->artist = $artist;
        $this->user = $user;
        $this->gracePeriodEnds = $gracePeriodEnds;
        // Create dispute URL - if route doesn't exist, use contact page
        try {
            $this->disputeUrl = route('artist.dispute', ['artist' => $artist->id]);
        } catch (\Exception $e) {
            $this->disputeUrl = route('contact.index') . '?artist=' . $artist->id;
        }
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = '⚠️ Someone Registered with Your Email - Artist Profile Claim Alert';
        
        return $this->subject($subject)
                    ->view('emails.artist-claim-warning')
                    ->with([
                        'artist' => $this->artist,
                        'user' => $this->user,
                        'gracePeriodEnds' => $this->gracePeriodEnds,
                        'disputeUrl' => $this->disputeUrl,
                    ]);
    }
}
