<?php

namespace App\Enums;

enum TipoBeneficio: string
{
    case BECA_COMPLETA  = 'BECA_COMPLETA';
    case BECA_PARCIAL   = 'BECA_PARCIAL';
    case FRANJA_BECARIA = 'FRANJA_BECARIA';
    case CUOTA_ESPECIAL = 'CUOTA_ESPECIAL';

    public function label(): string
    {
        return match ($this) {
            self::BECA_COMPLETA  => 'Beca Completa',
            self::BECA_PARCIAL   => 'Beca Parcial',
            self::FRANJA_BECARIA => 'Franja Becaria',
            self::CUOTA_ESPECIAL => 'Cuota Especial',
        };
    }

    /** Porcentaje por defecto que paga el estudiante según el tipo. */
    public function porcentajeEstudianteDefault(): float
    {
        return match ($this) {
            self::BECA_COMPLETA  => 0.00,
            self::BECA_PARCIAL   => 50.00,   // configurable
            self::FRANJA_BECARIA => 100.00,  // arancel diferenciado (monto fijo $53)
            self::CUOTA_ESPECIAL => 88.33,   // ejemplo institucional
        };
    }

    /** true si aplica monto fijo (Franja Becaria). */
    public function usaMontoFijo(): bool
    {
        return $this === self::FRANJA_BECARIA;
    }
}