<?php

namespace App\Rules\Student;

use App\Models\Estudiante\Alumnos;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CumplePrerequisitosRule implements Rule
{
    public function __construct(private Alumnos $alumno, private int $materiaId) {}

    public function passes($attribute, $value): bool
    {
        // $value = seccion_id
        $prereq = DB::table('prerequisitos as pr')
            ->join('pensum_materia as pm', 'pr.id_pensum_materia', '=', 'pm.pensum_materia_id')
            ->where('pm.id_materia', $this->materiaId)
            ->first();

        if (!$prereq) return true;

        // UV mínimas
        if ($prereq->uv_minimas_requeridas > 0) {
            $uv = (int) DB::table('detalle_inscripcion as di')
                ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
                ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
                ->join('materias as m', 's.id_materia', '=', 'm.materia_id')
                ->where('i.id_alumno', $this->alumno->alumno_id)
                ->where('di.estado_materia', 'APROBADA')
                ->sum('m.unidades_valorativas');

            if ($uv < $prereq->uv_minimas_requeridas) return false;
        }

        // Materia requisito aprobada
        if ($prereq->id_materia_requisito) {
            $ok = DB::table('detalle_inscripcion as di')
                ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
                ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
                ->where('i.id_alumno', $this->alumno->alumno_id)
                ->where('s.id_materia', $prereq->id_materia_requisito)
                ->where('di.estado_materia', 'APROBADA')
                ->exists();

            if (!$ok) return false;
        }

        return true;
    }

    public function message(): string
    {
        return 'No cumples los prerrequisitos (materia o UV mínimas) para inscribirte.';
    }
}