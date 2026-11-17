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
    public string $expiresInLabel;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $artistName,
        public string $email,
        public string $plainToken,
        public int $ttlMinutes = 1440,
    ) {
        $baseUrl = config('app.frontend_url', env('FRONTEND_URL', 'https://assin.art'));
        $frontendUrl = rtrim((string) ($baseUrl ?: 'https://assin.art'), '/');

        $this->verificationUrl = $frontendUrl
            . '/confirmacao-email?email=' . urlencode($this->email)
            . '&token=' . urlencode($this->plainToken);

        if ($this->ttlMinutes % 60 === 0) {
            $hours = intdiv($this->ttlMinutes, 60);
            $this->expiresInLabel = $hours === 1 ? '1 hora' : "{$hours} horas";
        } else {
            $this->expiresInLabel = "{$this->ttlMinutes} minutos";
        }
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
