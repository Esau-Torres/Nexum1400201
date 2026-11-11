<?php

namespace App\Enums;

enum TamanoGrupo: int
{
    case INDIVIDUAL = 1;
    case PAREJA     = 2;
    case GRUPO_TRES = 3;

    public function label(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Individual',
            self::PAREJA     => 'En pareja (2)',
            self::GRUPO_TRES => 'Grupo de 3',
        };
    }

    public static function desdeIntegrantes(int $cantidad): self
    {
        return match (true) {
            $cantidad <= 1 => self::INDIVIDUAL,
            $cantidad === 2 => self::PAREJA,
            default => self::GRUPO_TRES,
        };
    }
}