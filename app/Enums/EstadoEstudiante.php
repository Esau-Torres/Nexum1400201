<?php

namespace App\Enums;

enum EstadoEstudiante: string
{
    case ACTIVO              = 'ACTIVO';
    case INACTIVO            = 'INACTIVO';
    case GRADUADO            = 'GRADUADO';
    case CAMBIO_VOLUNTARIO   = 'CAMBIO_VOLUNTARIO';
    case CAMBIO_REPROBATORIO = 'CAMBIO_REPROBATORIO';

    public function label(): string
    {
        return match($this) {
            self::ACTIVO              => 'ACTIVO',
            self::INACTIVO            => 'INACTIVO',
            self::GRADUADO            => 'GRADUADO',
            self::CAMBIO_VOLUNTARIO   => 'CAMBIO_VOLUNTARIO',
            self::CAMBIO_REPROBATORIO => 'CAMBIO_REPROBATORIO',
        };
    }
}