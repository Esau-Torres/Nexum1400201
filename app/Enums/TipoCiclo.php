<?php

namespace App\Enums;

enum TipoCiclo: string
{
    case CICLO_01 = '01';
    case CICLO_02 = '02';

    public function label(): string
    {
        return match($this) {
            self::CICLO_01 => 'Ciclo 01',
            self::CICLO_02 => 'Ciclo 02',
        };
    }
}