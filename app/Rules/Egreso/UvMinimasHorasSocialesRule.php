<?php

namespace App\Rules\Egreso;

use App\Models\Estudiante\Alumnos;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UvMinimasHorasSocialesRule implements ValidationRule
{
    /**
     * Mínimos de UV ganadas por nivel académico (según código en `niveles_academicos`).
     */
    private const UV_MINIMAS = [
        'LICENCIATURA' => 64,
        'PROFESORADO'  => 32,
        'TECNICO'      => 32,
        'MAESTRIA'     => 16,
    ];

    public function __construct(private Alumnos $alumno) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $nivel = $this->nivelAcademico();

        if (!$nivel || !isset(self::UV_MINIMAS[$nivel])) {
            $fail('No se pudo determinar el nivel académico para validar las horas sociales.');
            return;
        }

        $uvGanadas = $this->uvAprobadas();
        $uvRequeridas = self::UV_MINIMAS[$nivel];

        if ($uvGanadas < $uvRequeridas) {
            $fail("Necesitas mínimo {$uvRequeridas} UV ganadas para realizar horas sociales ({$nivel}). Tienes {$uvGanadas}.");
        }
    }

    private function nivelAcademico(): ?string
    {
        return DB::table('carreras as c')
            ->join('niveles_academicos as na', 'c.id_nivel_academico', '=', 'na.nivel_academico_id')
            ->where('c.carrera_id', $this->alumno->id_carrera)
            ->value('na.codigo');
    }

    private function uvAprobadas(): int
    {
        return (int) DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->join('materias as m', 's.id_materia', '=', 'm.materia_id')
            ->where('i.id_alumno', $this->alumno->alumno_id)
            ->where('di.estado_materia', 'APROBADA')
            ->sum('m.unidades_valorativas');
    }
}