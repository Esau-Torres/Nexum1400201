<?php

namespace App\Enums;

enum EstadoSituacion: string
{
    case PENDIENTE      = 'PENDIENTE';
    case EN_OBSERVACION = 'EN_OBSERVACION';
    case CERRADO        = 'CERRADO';

    public function label(): string
    {
        return match ($this) {
            self::PENDIENTE      => 'Pendiente',
            self::EN_OBSERVACION => 'En observación',
            self::CERRADO        => 'Cerrado',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::PENDIENTE      => 'text-bg-warning',
            self::EN_OBSERVACION => 'text-bg-info',
            self::CERRADO        => 'text-bg-success',
        };
    }

    public function puedeEliminar(): bool
    {
        return $this === self::PENDIENTE;
    }
}