<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
// colas para correo
use Illuminate\Contracts\Queue\ShouldQueue;

class StudentApplicationRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $nombreCompleto,
        public string $motivo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Solicitud de ingreso a UMA — Rechazada',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.student.application-rejected',
            with: [
                'nombre'    => $this->nombreCompleto,
                'motivo'    => $this->motivo,
                'fecha'     => now()->format('d/m/Y H:i'),
                'contacto'  => config('mail.from.address'),
            ],
        );
    }
}