<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $artistName,
        public string $email,
        public string $plainToken,
    ) {
        $baseUrl = config('app.frontend_url', env('FRONTEND_URL', 'https://assin.art'));
        $frontendUrl = rtrim((string) ($baseUrl ?: 'https://assin.art'), '/');

        $this->verificationUrl = $frontendUrl
            . '/confirmacao-email?email=' . urlencode($this->email)
            . '&token=' . urlencode($this->plainToken);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirme seu e-mail no Assin.art',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.verify-email',
        );
    }
}
