<?php

namespace App\Mail;

use App\Models\Estudiante\Alumnos;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
// utilizacion de colas por correo
use Illuminate\Contracts\Queue\ShouldQueue;

class NewAccountStudentCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Alumnos $alumno,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenido a UMA, Tu solicitud fue aprobada',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.student.new-account-student-credentials',
            with: [
                'alumno'      => $this->alumno,
                'usuario'     => $this->alumno->usuario,
                'carrera'     => $this->alumno->carrera,
                'facultad'    => $this->alumno->carrera?->facultad,
                'sede'        => $this->alumno->usuario->regionalactivo,
                'codigo'      => $this->alumno->codigo_estudiante,
                'emailAcceso' => $this->alumno->usuario->email,
                'password'    => $this->alumno->codigo_estudiante, // contraseña = código
                'loginUrl'    => route('login'),
            ],
        );
    }
}