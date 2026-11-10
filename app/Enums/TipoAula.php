<?php

namespace App\Enums;

enum TipoAula: string
{
    case AULA          = 'AULA';
    case LABORATORIO   = 'LABORATORIO';
    case AUDITORIO     = 'AUDITORIO';
    case TALLER        = 'TALLER';
    case SALA_COMPUTO  = 'SALA_COMPUTO';

    public function label(): string
    {
        return match ($this) {
            self::AULA         => 'Aula',
            self::LABORATORIO  => 'Laboratorio',
            self::AUDITORIO    => 'Auditorio',
            self::TALLER       => 'Taller',
            self::SALA_COMPUTO => 'Sala de cómputo',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AULA         => 'bi-door-open',
            self::LABORATORIO  => 'bi-eyedropper',
            self::AUDITORIO    => 'bi-mic',
            self::TALLER       => 'bi-tools',
            self::SALA_COMPUTO => 'bi-pc-display',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::AULA         => 'text-bg-secondary',
            self::LABORATORIO  => 'text-bg-info',
            self::AUDITORIO    => 'text-bg-warning',
            self::TALLER       => 'text-bg-dark',
            self::SALA_COMPUTO => 'text-bg-primary',
        };
    }
}