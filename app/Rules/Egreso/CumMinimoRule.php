<?php

namespace App\Rules\Egreso;

use App\Models\Estudiante\Alumnos;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class CumMinimoRule implements ValidationRule
{
    private const CUM_MINIMO = 7.0;

    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cum = (float) DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->where('i.id_alumno', $this->alumno->alumno_id)
            ->whereIn('di.estado_materia', ['APROBADA', 'REPROBADA'])
            ->whereNotNull('di.promedio_final')
            ->avg('di.promedio_final');

        $cum = round($cum, 2);

        if ($cum < self::CUM_MINIMO) {
            $fail("Tu CUM es {$cum}, el mínimo requerido es " . self::CUM_MINIMO . ".");
        }
    }
}