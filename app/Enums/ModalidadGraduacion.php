<?php

namespace App\Enums;

enum ModalidadGraduacion: string
{
    case TRABAJO_GRADUACION  = 'TRABAJO_GRADUACION';
    case PRE_ESPECIALIZACION = 'PRE_ESPECIALIZACION';

    public function label(): string
    {
        return match ($this) {
            self::TRABAJO_GRADUACION  => 'Trabajo de Graduación',
            self::PRE_ESPECIALIZACION => 'Curso de Pre-Especialización',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::TRABAJO_GRADUACION  => 'text-bg-primary',
            self::PRE_ESPECIALIZACION => 'text-bg-info',
        };
    }

    /**
     * Solo pregrado puede optar por PRE_ESPECIALIZACION.
     * Maestrías y posgrados solo pueden optar por TRABAJO_GRADUACION.
     */
    public function permitidaParaNivel(string $codigoNivel): bool
    {
        return match ($this) {
            self::TRABAJO_GRADUACION  => in_array($codigoNivel, ['TECNICO', 'PROFESORADO', 'LICENCIATURA', 'MAESTRIA'], true),
            self::PRE_ESPECIALIZACION => in_array($codigoNivel, ['TECNICO', 'PROFESORADO', 'LICENCIATURA'], true),
        };
    }
}