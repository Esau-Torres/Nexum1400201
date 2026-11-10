<?php

namespace App\Enums;

enum EstadoEgreso: string
{
    case EN_PROCESO = 'EN_PROCESO';
    case EGRESADO   = 'EGRESADO';

    public function label(): string
    {
        return match ($this) {
            self::EN_PROCESO => 'En Proceso',
            self::EGRESADO   => 'Egresado',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::EN_PROCESO => 'bg-warning text-dark',
            self::EGRESADO   => 'bg-success',
        };
    }
}