<?php

namespace App\Actions\Student;

use App\Models\Academico\Pensum;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Estudiante\Alumnos;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ObtenerSeccionesDisponiblesAction
{
    public function __construct(
        private ValidarPuedeInscribirseAction $validarAction,
    ) {}

    public function execute(Alumnos $alumno, int $cicloLectivoId): Collection
    {
        // 1. Pensum activo del alumno
        $pensum = Pensum::where('id_carrera', $alumno->id_carrera)
            ->where('estado', 'ACTIVO')
            ->first();

        if (!$pensum) {
            return collect();
        }

        // 2. Materias del pensum con su materia y prerequisitos
        $materiasPensum = $pensum->pensumMaterias()
            ->with(['materia', 'prerequisitos.materiaRequisito'])
            ->get();

        // 3. Historial académico del alumno
        $historial = $this->historialAlumno($alumno);

        // 4. Ciclo actual del alumno (primer ciclo con materias pendientes)
        $cicloActual = $this->detectarCicloActual($materiasPensum, $historial);

        // 5. Secciones ACTIVAS del ciclo lectivo actual, solo de materias del pensum
        //    Cargamos horarios.horario para tener los datos reales del horario.
        $seccionesPorMateria = Seccion::with([
                'materia',
                'docente.user',
                'horarios.horario',
            ])
            ->where('id_ciclo_lectivo', $cicloLectivoId)
            ->where('estado', 'ACTIVA')
            ->whereIn('id_materia', $materiasPensum->pluck('id_materia'))
            ->get()
            ->groupBy('id_materia');

        // 6. Construir la estructura por ciclo sugerido
        return $materiasPensum
            ->groupBy('ciclo_sugerido')
            ->sortKeys()
            ->map(function ($materias, $ciclo) use (
                $historial,
                $seccionesPorMateria,
                $cicloActual,
                $alumno,
                $cicloLectivoId
            ) {
                // Filtrar solo materias que tengan al menos una sección activa
                $materiasConSecciones = $materias->filter(function ($pm) use ($seccionesPorMateria) {
                    return $seccionesPorMateria->has($pm->id_materia)
                        && $seccionesPorMateria->get($pm->id_materia)->isNotEmpty();
                });

                // Si el ciclo no tiene materias con secciones, devolvemos null y luego lo limpiamos
                if ($materiasConSecciones->isEmpty()) {
                    return null;
                }
                return [
                    'ciclo'           => (int) $ciclo,
                    'es_ciclo_actual' => $ciclo == $cicloActual,
                    'materias'        => $materiasConSecciones->map(function ($pm) use (
                        $historial,
                        $seccionesPorMateria,
                        $alumno,
                        $cicloLectivoId
                    ) {
                        $record = $historial->get($pm->id_materia);
                        $estado = $this->resolverEstado($record);
                        $seccionesInscritas = DB::table('detalle_inscripcion as di')
                            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
                            ->where('i.id_alumno', $alumno->alumno_id)
                            ->where('i.id_ciclo_lectivo', $cicloLectivoId)
                            ->where('i.estado_inscripcion', 'ACTIVA')
                            ->whereIn('di.estado_materia', ['CURSANDO', 'APROBADA'])
                            ->pluck('di.id_seccion')
                            ->flip();
                            
                        // Secciones + validación por cada una
                        $secciones = $seccionesPorMateria->get($pm->id_materia, collect())
                            ->map(function ($s) use ($alumno, $cicloLectivoId, $seccionesInscritas) {
                                $validacion = $this->validarAction->execute(
                                    $alumno,
                                    $s,
                                    $cicloLectivoId
                                );

                                return [
                                    'seccion_id'      => $s->seccion_id,
                                    'nombre_grupo'    => $s->nombre_grupo,
                                    'docente'         => $s->docente?->usuario?->name ?? 'Sin asignar',
                                    'cupo_total'      => $s->cupo_total,
                                    'inscritos'       => $s->inscritos_count,
                                    'cupo_disponible' => $s->cupo_disponible,
                                    'horarios'        => $this->formatearHorarios($s),
                                    'ya_inscrito'     => $seccionesInscritas->has($s->seccion_id),
                                    'puede_inscribir' => $validacion['puede'],
                                    'bloqueadores'    => $validacion['bloqueadores'],
                                ];
                            })
                            ->values();

                        return [
                            'pensum_materia_id' => $pm->pensum_materia_id,
                            'materia_id'        => $pm->id_materia,
                            'codigo'            => $pm->materia->codigo_materia,
                            'nombre'            => $pm->materia->nombre,
                            'uv'                => $pm->materia->unidades_valorativas,
                            'ciclo_sugerido'    => $pm->ciclo_sugerido,
                            'estado_visual'     => $estado,
                            'prerequisito'      => $pm->prerequisitos->first()?->materiaRequisito?->nombre,
                            'uv_minimas'        => $pm->prerequisitos->first()?->uv_minimas_requeridas ?? 0,
                            'secciones'         => $secciones,
                            'tiene_secciones'   => $secciones->isNotEmpty(),
                        ];
                    }),
                ];
            })    
            ->filter()
            ->values();
    }

    /* ==========================================================
     * HELPERS
     * ========================================================== */

    /**
     * Formatea los horarios de una sección extrayendo los datos reales
     * desde la tabla `horarios` (a través de `seccion_horario`).
     */
    private function formatearHorarios(Seccion $seccion): array
    {
        return $seccion->horarios
            ->map(fn ($sh) => $sh->horario)
            ->filter()
            ->map(fn ($h) => [
                'dia' => $this->valorDia($h->dia),
                'ini' => substr($h->hora_inicio, 0, 5),
                'fin' => substr($h->hora_fin, 0, 5),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Normaliza el día (enum Postgres `dia_semana` o string).
     */
    private function valorDia($dia): string
    {
        if ($dia instanceof \BackedEnum) {
            return $dia->value;
        }
        return (string) $dia;
    }

    /**
     * Historial académico del alumno agrupado por materia (último registro).
     */
    protected function historialAlumno(Alumnos $alumno): Collection
    {
        return DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->select('s.id_materia', 'di.estado_materia', 'di.promedio_final', 'di.numero_matricula')
            ->get()
            ->groupBy('id_materia')
            ->map(fn ($rows) => $rows->sortByDesc('numero_matricula')->first());
    }

    /**
     * Convierte el estado de la materia a string legible para la vista.
     */
    protected function resolverEstado($record): string
    {
        if (!$record) return 'POR_CURSAR';

        return match (strtoupper($record->estado_materia)) {
            'APROBADA'  => 'APROBADA',
            'REPROBADA' => 'REPROBADA',
            'RETIRADA'  => 'RETIRADA',
            'CURSANDO'  => 'ACTUAL',
            default     => 'POR_CURSAR',
        };
    }

    /**
     * Detecta el ciclo actual del alumno: primer ciclo con materias no aprobadas.
     */
    protected function detectarCicloActual($materiasPensum, $historial): ?int
    {
        foreach ($materiasPensum->groupBy('ciclo_sugerido')->sortKeys() as $ciclo => $materias) {
            $todasAprobadas = $materias->every(function ($m) use ($historial) {
                $r = $historial->get($m->id_materia);
                return $r && strtoupper($r->estado_materia) === 'APROBADA';
            });

            if (!$todasAprobadas) return (int) $ciclo;
        }

        return null;
    }
}