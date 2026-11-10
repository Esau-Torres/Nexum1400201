<?php

namespace App\Rules\Graduacion;

use App\Models\Academico\ProcesoGraduacion;
use App\Enums\ArancelesGraduacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ArancelMatriculaPreEspecializacionPagadoRule implements ValidationRule
{
    private const CONCEPTO = 'PRE-001';

    public function __construct(private ProcesoGraduacion $proceso) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->proceso->modalidad->value !== 'PRE_ESPECIALIZACION') {
            return;
        }

        $pagado = ArancelesGraduacion::queryPagosDesde($this->proceso)
            ->where('p.id_alumno', $this->proceso->id_alumno)
            ->where('cp.codigo_concepto', self::CONCEPTO)
            ->exists();

        if (!$pagado) {
            $fail('No se ha cancelado la matrícula de pre-especialización (PRE-001) para este proceso.');
        }
    }
}