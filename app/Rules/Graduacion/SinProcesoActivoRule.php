<?php

namespace App\Rules\Graduacion;

use App\Models\Academico\ProcesoGraduacion;
use App\Models\Estudiante\Alumnos;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SinProcesoActivoRule implements ValidationRule
{
    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (ProcesoGraduacion::where('id_alumno', $this->alumno->alumno_id)
            ->where('es_activo', true)
            ->exists()) {
            $fail('El alumno ya tiene un proceso de graduación activo.');
        }
    }
}