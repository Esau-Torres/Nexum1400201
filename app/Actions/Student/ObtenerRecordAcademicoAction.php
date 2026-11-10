<?php

namespace App\Actions\Student;

use App\Models\Estudiante\Alumnos;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ObtenerRecordAcademicoAction
{
    public function execute(Alumnos $alumno): Collection
    {
        $detalles = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->join('materias as m', 's.id_materia', '=', 'm.materia_id')
            ->join('ciclo_lectivo as cl', 'i.id_ciclo_lectivo', '=', 'cl.ciclo_lectivo_id')
            ->leftJoin('docentes as d', 's.id_docente', '=', 'd.docente_id')
            ->leftJoin('users as u', 'd.id_usuario', '=', 'u.id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->select(
                'di.detalle_inscripcion_id',
                'di.promedio_final',
                'di.estado_materia',
                'di.numero_matricula',
                'di.es_sobrecupo',
                'di.fecha_retiro',
                'm.materia_id',
                'm.codigo_materia',
                'm.nombre as materia_nombre',
                'm.unidades_valorativas',
                's.seccion_id',
                's.nombre_grupo',
                'cl.ciclo_lectivo_id',
                'cl.nombre_ciclo',
                'cl.fecha_inicio',
                'cl.fecha_fin',
                'u.name as docente_nombre'
            )
            ->orderByRaw("
                CASE 
                    WHEN di.estado_materia = 'CURSANDO' THEN 1
                    WHEN di.estado_materia = 'APROBADA' THEN 2
                    WHEN di.estado_materia = 'REPROBADA' THEN 3
                    WHEN di.estado_materia = 'RETIRADA' THEN 4
                    ELSE 5
                END ASC
            ")
            ->orderByDesc('cl.fecha_inicio')
            ->orderBy('m.nombre')
            ->get();

        // Cargar horarios y aulas en batch (una sola consulta por sección)
        $seccionIds = $detalles->pluck('seccion_id')->unique()->values()->all();

        $horariosPorSeccion = DB::table('seccion_horario as sh')
            ->join('horarios as h', 'sh.id_horario', '=', 'h.horario_id')
            ->leftJoin('aula as a', 'sh.id_aula', '=', 'a.aula_id')
            ->whereIn('sh.id_seccion', $seccionIds)
            ->select(
                'sh.id_seccion',
                'h.dia',
                'h.hora_inicio',
                'h.hora_fin',
                'h.jornada',
                'a.nombre_aula as aula_nombre'
            )
            ->get()
            ->groupBy('id_seccion');

        return $detalles->map(function ($d) use ($horariosPorSeccion) {
            $horarios = collect($horariosPorSeccion->get($d->seccion_id, []))->map(fn ($h) => [
                'dia'   => is_object($h->dia) ? ($h->dia->value ?? (string) $h->dia) : (string) $h->dia,
                'ini'   => substr((string) $h->hora_inicio, 0, 5),
                'fin'   => substr((string) $h->hora_fin, 0, 5),
                'aula'  => $h->aula_nombre ?? 'Sin aula',
                'jornada' => is_object($h->jornada) ? ($h->jornada->value ?? (string) $h->jornada) : (string) $h->jornada,
            ]);

            $d->horarios = $horarios;
            return $d;
        });
    }
}