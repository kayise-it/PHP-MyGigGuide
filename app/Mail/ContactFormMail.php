<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;

    public string $email;

    /** User's subject line — not Mailable::$subject (name clash). */
    public string $contactSubject;

    public string $contactMessage;

    public bool $newsletter;

    public $submittedAt;

    public function __construct(
        string $name,
        string $email,
        string $contactSubject,
        string $contactMessage,
        bool $newsletter = false,
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->contactSubject = $contactSubject;
        $this->contactMessage = $contactMessage;
        $this->newsletter = $newsletter;
        $this->submittedAt = now();
    }

    public function build(): self
    {
        return $this->subject('[Contact] '.$this->contactSubject)
            ->replyTo($this->email, $this->name)
            ->view('emails.contact-form')
            ->with([
                'name' => $this->name,
                'email' => $this->email,
                'contactSubject' => $this->contactSubject,
                'contactMessage' => $this->contactMessage,
                'newsletter' => $this->newsletter,
                'submittedAt' => $this->submittedAt,
            ]);
    }
}
