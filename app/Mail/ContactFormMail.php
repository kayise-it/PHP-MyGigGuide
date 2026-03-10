<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $subject;
    public $message;
    public $newsletter;
    public $submittedAt;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $email, $subject, $message, $newsletter = false)
    {
        $this->name = $name;
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        $this->newsletter = $newsletter;
        $this->submittedAt = now();
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('[Contact] ' . $this->subject)
                    ->replyTo($this->email, $this->name)
                    ->view('emails.contact-form')
                    ->with([
                        'name' => $this->name,
                        'email' => $this->email,
                        'subject' => $this->subject,
                        'message' => $this->message,
                        'newsletter' => $this->newsletter,
                        'submittedAt' => $this->submittedAt,
                    ]);
    }
}
