<?php

namespace App\Mail;

use App\Models\Academico\BeneficioEstudiante;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssingBenefitStudentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public BeneficioEstudiante $beneficio,
        public string $motivo,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Beneficio estudiantil asignado — NEXUM UMA',
        );
    }

    public function content(): Content
    {
        // Aseguramos relaciones por si el Mailable se serializa sin ellas
        $this->beneficio->loadMissing([
            'alumno.usuario',
            'alumno.carrera.facultad',
            'cicloLectivo',
        ]);

        return new Content(
            view: 'mails.benefits.assingbenefit',
            with: [
                'usuario'   => $this->beneficio->alumno->usuario,
                'alumno'    => $this->beneficio->alumno,
                'carrera'   => $this->beneficio->alumno->carrera,
                'facultad'  => $this->beneficio->alumno->carrera?->facultad,
                'ciclo'     => $this->beneficio->cicloLectivo,
                'beneficio' => $this->beneficio,
                'motivo'    => $this->motivo,
                'fecha'     => now()->format('d/m/Y H:i'),
            ],
        );
    }
}