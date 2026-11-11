<?php

namespace App\Enums;

enum EstadoProcesoGraduacion: string
{
    case INSCRITO      = 'INSCRITO';
    case EN_DESARROLLO = 'EN_DESARROLLO';
    case EN_DEFENSA    = 'EN_DEFENSA';
    case APROBADO      = 'APROBADO';
    case REPROBADO     = 'REPROBADO';
    case GRADUADO      = 'GRADUADO';

    public function label(): string
    {
        return match ($this) {
            self::INSCRITO      => 'Inscrito',
            self::EN_DESARROLLO => 'En Desarrollo',
            self::EN_DEFENSA    => 'En Defensa',
            self::APROBADO      => 'Aprobado',
            self::REPROBADO     => 'Reprobado',
            self::GRADUADO      => 'Graduado',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::INSCRITO      => 'text-bg-secondary',
            self::EN_DESARROLLO => 'text-bg-info',
            self::EN_DEFENSA    => 'text-bg-warning',
            self::APROBADO      => 'text-bg-success',
            self::REPROBADO     => 'text-bg-danger',
            self::GRADUADO      => 'text-bg-primary',
        };
    }
}