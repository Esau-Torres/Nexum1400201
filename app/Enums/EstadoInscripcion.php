<?php

namespace App\Enums;

enum EstadoInscripcion: string
{
    case ACTIVA    = 'ACTIVA';
    case RETIRADA  = 'RETIRADA';
    case COMPLETADA = 'COMPLETADA';

    public function label(): string
    {
        return match($this) {
            self::ACTIVA     => 'Activa',
            self::RETIRADA   => 'Retirada',
            self::COMPLETADA => 'Completada',
        };
    }
}