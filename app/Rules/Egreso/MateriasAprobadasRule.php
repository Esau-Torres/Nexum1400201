<?php

namespace App\Rules\Egreso;

use App\Models\Academico\Pensum;
use App\Models\Estudiante\Alumnos;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class MateriasAprobadasRule implements ValidationRule
{
    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $pensum = Pensum::where('id_carrera', $this->alumno->id_carrera)
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$pensum) {
            $fail('El alumno no tiene un pensum activo.');
            return;
        }

        $total = $pensum->pensumMaterias()->count();

        $aprobadas = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->join('pensum_materia as pm', 's.id_materia', '=', 'pm.id_materia')
            ->where('i.id_alumno', $this->alumno->alumno_id)
            ->where('pm.id_pensum', $pensum->pensum_id)
            ->where('di.estado_materia', 'APROBADA')
            ->distinct()
            ->count('pm.id_materia');

        if ($aprobadas < $total) {
            $fail("No has aprobado todas las materias del pensum ({$aprobadas}/{$total}).");
        }
    }
}