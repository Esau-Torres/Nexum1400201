<?php

namespace App\Mail;

use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewAccountCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;


    public function __construct(
        public User $user,
        public string $plainPassword,
        public array $roleNames
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Credenciales de Acceso Oficial - Plataforma NEXUM UMA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.users.credentials',
        );
    }
}