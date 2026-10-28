<?php

namespace App\Enums;

enum EstadoMateria: string
{
    case ACTIVO     = 'ACTIVO';
    case INACTIVO   = 'INACTIVO';
    case MODIFICADO = 'MODIFICADO';

    public function label(): string
    {
        return match($this) {
            self::ACTIVO     => 'Activo',
            self::INACTIVO   => 'Inactivo',
            self::MODIFICADO => 'Modificado',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::ACTIVO     => 'text-bg-success',
            self::INACTIVO   => 'text-bg-secondary',
            self::MODIFICADO => 'text-bg-warning',
        };
    }
}