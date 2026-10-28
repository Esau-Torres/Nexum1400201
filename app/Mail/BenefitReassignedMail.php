<?php

namespace App\Mail;

use App\Models\Academico\BeneficioEstudiante;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BenefitReassignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public BeneficioEstudiante $nuevoBeneficio,
        public BeneficioEstudiante $beneficioAnterior,
        public string $motivo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Actualización de beneficio estudiantil — NEXUM UMA');
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.benefits.reassigned',
            with: [
                'usuario'  => $this->nuevoBeneficio->alumno->usuario,
                'alumno'   => $this->nuevoBeneficio->alumno,
                'anterior' => $this->beneficioAnterior,
                'nuevo'    => $this->nuevoBeneficio,
                'motivo'   => $this->motivo,
                'fecha'    => now()->format('d/m/Y H:i'),
            ],
        );
    }
}