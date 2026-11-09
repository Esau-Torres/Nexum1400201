<?php

namespace App\Enums;

enum TipoRepeticion: string
{
    case PRIMERA_REPROBACION = 'PRIMERA_REPROBACION';
    case SEGUNDA_REPROBACION = 'SEGUNDA_REPROBACION';

    public function label(): string
    {
        return match ($this) {
            self::PRIMERA_REPROBACION => 'Primera reprobación',
            self::SEGUNDA_REPROBACION => 'Segunda reprobación',
        };
    }
}