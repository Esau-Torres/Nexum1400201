<?php

namespace App\Mail;

use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\DetalleInscripcion;
use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DarDeBajaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Alumnos $alumno,
        public DetalleInscripcion $detalle,
        public string $materia,
        public string $ciclo,
        public string $docente,
        public string $grupo,
        public array $horarios,
        public ?string $motivo = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Retiro de materia: ' . $this->materia . ' (' . $this->ciclo . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.student.retiro_materia',
            with: [
                'usuario'  => $this->user,
                'alumno'   => $this->alumno,
                'detalle'  => $this->detalle,
                'materia'  => $this->materia,
                'ciclo'    => $this->ciclo,
                'docente'  => $this->docente,
                'grupo'    => $this->grupo,
                'horarios' => $this->horarios,
                'motivo'   => $this->motivo,
                'fecha'    => now()->format('d/m/Y H:i'),
            ],
        );
    }
}