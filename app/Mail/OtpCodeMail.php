<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otpCode // Der Code wird hier gespeichert
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dein Bestätigungscode', // Der Betreff der E-Mail
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp', // Hier liegt das Blade-Template
        );
    }
}
