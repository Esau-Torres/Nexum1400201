<?php

namespace App\Rules\Graduacion;

use App\Models\Academico\ProcesoGraduacion;
use App\Enums\ArancelesGraduacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CuotasPreEspecializacionCompletasRule implements ValidationRule
{
    private const CONCEPTO            = 'PRE-002';
    private const CUOTAS_REQUERIDAS   = 12;

    public function __construct(private ProcesoGraduacion $proceso) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->proceso->modalidad->value !== 'PRE_ESPECIALIZACION') {
            return;
        }

        $pagadas = ArancelesGraduacion::queryPagosDesde($this->proceso)
            ->where('p.id_alumno', $this->proceso->id_alumno)
            ->where('cp.codigo_concepto', self::CONCEPTO)
            ->distinct()
            ->count('p.pago_id');

        if ($pagadas < self::CUOTAS_REQUERIDAS) {
            $faltantes = self::CUOTAS_REQUERIDAS - $pagadas;
            $fail("Faltan {$faltantes} mensualidad(es) de pre-especialización por cancelar ({$pagadas}/12).");
        }
    }
}