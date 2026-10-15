<?php

namespace App\Enums;

enum EstadoSeccion: string
{
    case ACTIVA    = 'ACTIVA';
    case CERRADA   = 'CERRADA';
    case CANCELADA = 'CANCELADA';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVA    => 'Activa',
            self::CERRADA   => 'Cerrada',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::ACTIVA    => 'text-bg-success',
            self::CERRADA   => 'text-bg-secondary',
            self::CANCELADA => 'text-bg-danger',
        };
    }

    public function descripcion(): string
    {
        return match ($this) {
            self::ACTIVA    => 'Sección abierta a inscripciones y en curso.',
            self::CERRADA   => 'Sección finalizada, sin nuevas inscripciones.',
            self::CANCELADA => 'Sección cancelada, no se dicta.',
        };
    }
}