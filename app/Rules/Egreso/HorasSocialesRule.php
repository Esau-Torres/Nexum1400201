<?php

namespace App\Rules\Egreso;

use App\Models\Academico\ConfiguracionAcademica;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\RequisitosEgreso;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HorasSocialesRule implements ValidationRule
{
    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $req = RequisitosEgreso::paraAlumno($this->alumno->alumno_id);

        if (!$req->horas_sociales_aprobadas) {
            $fail('No tienes aprobadas las horas sociales.');
            return;
        }

        $config = ConfiguracionAcademica::paraCarrera($this->alumno->id_carrera);
        $requeridas = (int) $config->uv_requeridas_horas_sociales;

        if ($req->horas_sociales_completadas < $requeridas) {
            $fail("Has completado {$req->horas_sociales_completadas} de {$requeridas} horas sociales.");
        }
    }
}