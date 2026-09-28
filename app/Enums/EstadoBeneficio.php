<?php

namespace App\Enums;

enum EstadoBeneficio: string
{
    case ACTIVO     = 'ACTIVO';
    case REVOCADO   = 'REVOCADO';
    case SUSPENDIDO = 'SUSPENDIDO';
    case VENCIDO    = 'VENCIDO';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVO     => 'Activo',
            self::REVOCADO   => 'Revocado',
            self::SUSPENDIDO => 'Suspendido',
            self::VENCIDO    => 'Vencido',
        };
    }
}