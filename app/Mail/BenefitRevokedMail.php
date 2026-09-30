<?php

namespace App\Mail;

use App\Models\Academico\BeneficioEstudiante;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BenefitRevokedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BeneficioEstudiante $beneficio,
        public string $motivo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Beneficio estudiantil revocado — NEXUM UMA');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.benefits.revoked',
            with: [
                'usuario'   => $this->beneficio->alumno->usuario,
                'alumno'    => $this->beneficio->alumno,
                'beneficio' => $this->beneficio,
                'motivo'    => $this->motivo,
                'fecha'     => now()->format('d/m/Y H:i'),
            ],
        );
    }
}