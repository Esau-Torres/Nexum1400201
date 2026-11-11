<?php

namespace App\Rules\Egreso;

use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasantiaAprobadaRule implements ValidationRule
{
    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $req = RequisitosEgreso::paraAlumno($this->alumno->alumno_id);

        if ($req->requiere_pasantia && !$req->pasantia_aprobada) {
            $fail('No has aprobado la pasantía requerida.');
        }
    }
}