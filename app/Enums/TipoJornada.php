<?php

namespace App\Enums;

enum TipoJornada: string
{
    case MATUTINO   = 'MATUTINO';
    case VESPERTINO = 'VESPERTINO';
    case NOCTURNO   = 'NOCTURNO';
    case SABATINO   = 'SABATINO';
    case MIXTO      = 'MIXTO';

    public function label(): string
    {
        return match ($this) {
            self::MATUTINO   => 'Matutino',
            self::VESPERTINO => 'Vespertino',
            self::NOCTURNO   => 'Nocturno',
            self::SABATINO   => 'Sabatino',
            self::MIXTO      => 'Mixto',
        };
    }

    /** Rango horario sugerido (referencial, no restrictivo) */
    public function rangoSugerido(): string
    {
        return match ($this) {
            self::MATUTINO   => '06:00 — 12:00',
            self::VESPERTINO => '12:00 — 18:00',
            self::NOCTURNO   => '18:00 — 20:30',
            self::SABATINO   => '06:00 — 20:30',
            self::MIXTO      => 'Combinado',
        };
    }

    /** Determina la jornada automáticamente según la hora de inicio */
    public static function desdeHora(string $horaInicio): self
    {
        $hora = (int) explode(':', $horaInicio)[0];

        return match (true) {
            $hora < 12 => self::MATUTINO,
            $hora < 18 => self::VESPERTINO,
            default    => self::NOCTURNO,
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::MATUTINO   => 'text-bg-warning',
            self::VESPERTINO => 'text-bg-info',
            self::NOCTURNO   => 'text-bg-dark',
            self::SABATINO   => 'text-bg-success',
            self::MIXTO      => 'text-bg-secondary',
        };
    }
}