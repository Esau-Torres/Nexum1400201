<?php

namespace App\Rules\Student;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PuedeCursarMateriaRule implements Rule
{
    protected $alumnoId;
    protected $mensaje;

    public function __construct($alumnoId)
    {
        $this->alumnoId = $alumnoId;
    }

    public function passes($attribute, $value)
    {
        // $value es el ID de la materia
        $prerequisito = DB::table('prerequisitos as pr')
            ->join('pensum_materia as pm', 'pr.id_pensum_materia', '=', 'pm.pensum_materia_id')
            ->where('pm.id_materia', $value)
            ->first();

        if (!$prerequisito) {
            return true; // No tiene prerrequisitos
        }

        // Validar UV mínimas
        $uvAprobadas = DB::table('detalle_inscripcion as di')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('materias as m', 's.id_materia', '=', 'm.materia_id')
            ->where('i.id_alumno', $this->alumnoId)
            ->where('di.estado_materia', 'APROBADA')
            ->sum('m.unidades_valorativas');

        if ($uvAprobadas < $prerequisito->uv_minimas_requeridas) {
            $this->mensaje = "No cumple con las UV mínimas requeridas ({$prerequisito->uv_minimas_requeridas}).";
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->mensaje;
    }
}