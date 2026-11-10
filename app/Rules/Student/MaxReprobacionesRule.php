<?php

namespace App\Rules\Student;

use App\Models\Academico\ConfiguracionAcademica;
use App\Models\Estudiante\Alumnos;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class MaxReprobacionesRule implements Rule
{
    private int $max;
    private int $actual;

    public function __construct(private Alumnos $alumno, private int $materiaId)
    {
        $config = ConfiguracionAcademica::where('id_carrera', $alumno->id_carrera)->first();
        $this->max = $config->max_reprobaciones_materia ?? 3;
    }

    public function passes($attribute, $value): bool
    {
        $this->actual = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->where('i.id_alumno', $this->alumno->alumno_id)
            ->where('s.id_materia', $this->materiaId)
            ->where('di.estado_materia', 'REPROBADA')
            ->count();

        return $this->actual < $this->max;
    }

    public function message(): string
    {
        return "Has reprobado esta materia {$this->actual} de {$this->max} veces permitidas. Contacta a Administración Académica para gestionar un cambio de carrera.";
    }
}