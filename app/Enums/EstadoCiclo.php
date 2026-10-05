<?php

namespace App\Enums;

enum EstadoCiclo: string
{
    case CONFIGURACION = 'CONFIGURACION';
    case MATRICULA_ABIERTA = 'MATRICULA_ABIERTA';
    case ACTIVO = 'ACTIVO';
    case CERRADO = 'CERRADO';

    public function label(): string
    {
        return match($this) {
            self::CONFIGURACION => 'Configuración',
            self::MATRICULA_ABIERTA => 'Matrícula Abierta',
            self::ACTIVO => 'Activo',
            self::CERRADO => 'Cerrado',
        };
    }
}