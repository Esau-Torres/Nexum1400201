<?php

namespace App\Rules\Egreso;

use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SolvenciaFinancieraRule implements ValidationRule
{
    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $req = RequisitosEgreso::paraAlumno($this->alumno->alumno_id);

        if (!$req->solvencia_financiera) {
            $fail('No cuentas con la constancia de solvencia financiera.');
        }
    }
}