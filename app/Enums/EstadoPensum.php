<?php

namespace App\Enums;

enum EstadoPensum: string
{
    case ACTIVO     = 'ACTIVO';
    case INACTIVO   = 'INACTIVO';
    case MODIFICADO = 'MODIFICADO';

    public function label(): string
    {
        return match($this) {
            self::ACTIVO     => 'Activo',
            self::INACTIVO   => 'Inactivo',
            self::MODIFICADO => 'Modificado',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::ACTIVO     => 'text-bg-success',
            self::INACTIVO   => 'text-bg-secondary',
            self::MODIFICADO => 'text-bg-warning',
        };
    }

    public function descripcion(): string
    {
        return match($this) {
            self::ACTIVO     => 'Pensum vigente. Es cursable y visible para los estudiantes.',
            self::INACTIVO   => 'Pensum archivado. No es visible ni cursable.',
            self::MODIFICADO => 'Pensum en edición. Se están asignando materias y prerequisitos.',
        };
    }
}