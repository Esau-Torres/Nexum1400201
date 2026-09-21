<?php

namespace App\Mail;

use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssingRoleUsersMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $action 'assigned' | 'revoked'
     */
    public string $type;

    public function __construct(
        public User $user,
        public array $assignedRoles = [],
        public array $revokedRoles = [],
    ) {
         $this->type = match (true) {
            !empty($assignedRoles) && !empty($revokedRoles) => 'mixed',
            !empty($assignedRoles) => 'assigned',
            !empty($revokedRoles)  => 'revoked',
            default                => 'noop',
        };
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->type) {
            'mixed'    => 'Actualización de roles de usuario',
            'assigned' => 'Asignación de roles de usuario',
            'revoked'  => 'Revocación de roles de usuario',
            default    => 'Roles de usuario actualizados',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.users.asignacionroles',
            with: [
                'type'          => $this->type,
                'assignedRoles' => $this->assignedRoles,
                'revokedRoles'  => $this->revokedRoles,
            ]
        );
    }
}