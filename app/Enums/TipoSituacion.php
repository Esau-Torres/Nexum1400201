<?php

namespace App\Enums;

enum TipoSituacion: string
{
    case ACADEMICA      = 'ACADEMICA';
    case DISCIPLINARIA  = 'DISCIPLINARIA';
    case FINANCIERA     = 'FINANCIERA';
    case ADMINISTRATIVA = 'ADMINISTRATIVA';
    case OTRO           = 'OTRO';

    public function label(): string
    {
        return match ($this) {
            self::ACADEMICA      => 'Académica',
            self::DISCIPLINARIA  => 'Disciplinaria',
            self::FINANCIERA     => 'Financiera',
            self::ADMINISTRATIVA => 'Administrativa',
            self::OTRO           => 'Otra',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::ACADEMICA      => 'text-bg-primary',
            self::DISCIPLINARIA  => 'text-bg-warning',
            self::FINANCIERA     => 'text-bg-success',
            self::ADMINISTRATIVA => 'text-bg-info',
            self::OTRO           => 'text-bg-secondary',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
            ->all();
    }
}