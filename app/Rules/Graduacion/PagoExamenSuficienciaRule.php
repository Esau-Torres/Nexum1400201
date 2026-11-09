<?php

namespace App\Rules\Graduacion;

use App\Models\Academico\ProcesoGraduacion;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class PagoExamenSuficienciaRule implements ValidationRule
{
    private const CONCEPTO = 'TRA-017';

    public function __construct(private ProcesoGraduacion $proceso) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pagado = ArancelesGraduacion::queryPagosDesde($this->proceso)
            ->where('p.id_alumno', $this->proceso->id_alumno)
            ->where('cp.codigo_concepto', self::CONCEPTO)
            ->exists();

        if (!$pagado) {
            $fail('Para reprogramar el examen de suficiencia el alumno debe de cancelar el arancel TRA-017 ($100).');
        }
    }
}