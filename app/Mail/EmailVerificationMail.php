<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Artist;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $verificationUrl;
    public $unclaimedArtist;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?Artist $unclaimedArtist = null)
    {
        $this->user = $user;
        $this->unclaimedArtist = $unclaimedArtist;
        $this->verificationUrl = route('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification())
        ]);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->unclaimedArtist 
            ? 'Verify Email & Claim Your Artist Profile - My Gig Guide'
            : 'Verify Your Email Address - My Gig Guide';

        return $this->subject($subject)
                    ->view('emails.verify-email')
                    ->with([
                        'user' => $this->user,
                        'verificationUrl' => $this->verificationUrl,
                        'unclaimedArtist' => $this->unclaimedArtist,
                    ]);
    }
}



