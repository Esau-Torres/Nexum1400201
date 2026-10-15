<?php

namespace App\Enums;

enum DiaSemana: string
{
    case LUNES     = 'LUNES';
    case MARTES    = 'MARTES';
    case MIERCOLES = 'MIERCOLES';
    case JUEVES    = 'JUEVES';
    case VIERNES   = 'VIERNES';
    case SABADO    = 'SABADO';

    public function label(): string
    {
        return match ($this) {
            self::LUNES     => 'Lunes',
            self::MARTES    => 'Martes',
            self::MIERCOLES => 'Miércoles',
            self::JUEVES    => 'Jueves',
            self::VIERNES   => 'Viernes',
            self::SABADO    => 'Sábado',
        };
    }

    /** Número ISO: 1=Lunes ... 6=Sábado */
    public function isoNumber(): int
    {
        return match ($this) {
            self::LUNES     => 1,
            self::MARTES    => 2,
            self::MIERCOLES => 3,
            self::JUEVES    => 4,
            self::VIERNES   => 5,
            self::SABADO    => 6,
        };
    }

    /** Abreviatura de 3 letras para tablas compactas */
    public function short(): string
    {
        return match ($this) {
            self::LUNES     => 'Lun',
            self::MARTES    => 'Mar',
            self::MIERCOLES => 'Mié',
            self::JUEVES    => 'Jue',
            self::VIERNES   => 'Vie',
            self::SABADO    => 'Sáb',
        };
    }

    /** Para selects ordenados en formularios */
    public static function ordenados(): array
    {
        return [
            self::LUNES, self::MARTES, self::MIERCOLES,
            self::JUEVES, self::VIERNES, self::SABADO,
        ];
    }
}