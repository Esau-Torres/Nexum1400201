<?php

namespace App\Rules\Graduacion;

use App\Enums\EstadoEgreso;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EgresadoRule implements ValidationRule
{
    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $req = RequisitosEgreso::paraAlumno($this->alumno->alumno_id);

        if ($req->estado_egreso !== EstadoEgreso::EGRESADO) {
            $fail('El alumno debe estar registrado como EGRESADO antes de iniciar el proceso de graduación.');
        }
    }
}