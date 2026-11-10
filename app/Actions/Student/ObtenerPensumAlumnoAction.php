<?php

namespace App\Actions\Student;

use App\Models\Estudiante\Alumnos;
use App\Models\Academico\PensumMateria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ObtenerPensumAlumnoAction
{
    public function execute(Alumnos $alumno): Collection
    {
        // 1. Obtener el pensum ACTIVO de la carrera del alumno
        $pensum = DB::table('pensum')
            ->where('id_carrera', $alumno->id_carrera)
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$pensum) {
            return collect([]);
        }

        // 2. Obtener todas las materias del pensum con sus datos
        $materiasPensum = DB::table('pensum_materia as pm')
            ->join('materias as m', 'pm.id_materia', '=', 'm.materia_id')
            ->leftJoin('prerequisitos as pr', 'pm.pensum_materia_id', '=', 'pr.id_pensum_materia')
            ->leftJoin('materias as mr', 'pr.id_materia_requisito', '=', 'mr.materia_id')
            ->where('pm.id_pensum', $pensum->pensum_id)
            ->select(
                'pm.pensum_materia_id',
                'pm.ciclo_sugerido',
                'pm.es_obligatoria',
                'm.materia_id',
                'm.codigo_materia',
                'm.nombre as nombre_materia',
                'm.unidades_valorativas',
                'pr.uv_minimas_requeridas',
                'mr.nombre as nombre_prerequisito'
            )
            ->get();

        // 3. Obtener el historial académico del alumno (solo las materias de este pensum)
        $historial = DB::table('detalle_inscripcion as di')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->where('i.id_alumno', $alumno->alumno_id) // O usa $alumno->alumno_id según tu FK
            ->select('s.id_materia', 'di.estado_materia', 'di.promedio_final')
            ->get()
            ->keyBy('id_materia');

        // 4. Mapear los estados
        return $materiasPensum->map(function ($materia) use ($historial) {
            $record = $historial->get($materia->materia_id);

            if ($record) {
                // Si ya la cursó o la está cursando
                $estado = match($record->estado_materia) {
                    'APROBADA' => 'APROBADA',
                    'REPROBADA' => 'REPROBADA',
                    'RETIRADA' => 'RETIRADA',
                    'CURSANDO' => 'ACTUAL',
                    default => 'POR_CURSAR'
                };
            } else {
                $estado = 'POR_CURSAR';
            }

            $materia->estado_visual = $estado;
            return $materia;
        })
        ->sortBy('ciclo_sugerido')
        ->groupBy('ciclo_sugerido')
        ->sortKeys();
    }
}