<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnrollmentInterestMail extends Mailable
{
    use SerializesModels;

    public function __construct(public array $data)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Novo Pedido de Inscrição — ' . ($this->data['first_name'] ?? '') . ' ' . ($this->data['last_name'] ?? ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.enrollment-interest',
            with: ['data' => $this->data],
        );
    }
}
