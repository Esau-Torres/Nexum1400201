<?php

namespace App\Actions\Student;

use App\Models\Academico\ConfiguracionAcademica;
use App\Models\Academico\Pensum;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Estudiante\Alumnos;
use App\Models\Estudiante\Inscripcion;
use App\Models\Academico\CicloLectivo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ValidarPuedeInscribirseAction
{
    public function execute(Alumnos $alumno, Seccion $seccion, int $cicloLectivoId): array
    {
        $bloqueadores = [];

        // 1. Sección activa
        if ($seccion->estado->value !== 'ACTIVA') {
            $bloqueadores[] = 'La sección no está activa.';
        }

        if ((int) $seccion->id_ciclo_lectivo !== (int) $cicloLectivoId) {
            $bloqueadores[] = 'La sección no pertenece al ciclo lectivo actual.';
        }

        // 2. Ciclo lectivo con MATRICULA_ABIERTA
        $ciclo = CicloLectivo::find($cicloLectivoId);
        if (!$ciclo || $ciclo->estado->value !== 'MATRICULA_ABIERTA') {
            $bloqueadores[] = 'El ciclo lectivo no tiene la matrícula abierta.';
        }

        // 3. Cupo disponible
        if ($seccion->cupo_disponible <= 0) {
            $bloqueadores[] = 'La sección no tiene cupo disponible.';
        }

        // 4. Pensum activo + la materia pertenece a él
        $pensum = Pensum::where('id_carrera', $alumno->id_carrera)
            ->where('estado', 'ACTIVO')
            ->first();

        $pm = null;
        if (!$pensum) {
            $bloqueadores[] = 'No tienes pensum activo.';
        } else {
            $pm = $pensum->pensumMaterias()
                ->with('prerequisitos.materiaRequisito')
                ->where('id_materia', $seccion->id_materia)
                ->first();

            if (!$pm) {
                $bloqueadores[] = 'La materia no pertenece a tu pensum.';
            }
        }

        // 5. Ya inscrito en la misma materia este ciclo
        //    → Solo bloquea si intenta inscribirse en OTRA sección de la misma materia.
        if ($this->yaInscritoEnMismaMateria($alumno, $seccion, $cicloLectivoId)) {
            $bloqueadores[] = 'Ya estás inscrito en esta materia en el ciclo actual.';
        }

        // 6. Inscripción ACTIVA en otro pensum para este ciclo
        //    → Reescribimos la validación con comparación directa de pensum activo del alumno.
        if ($pensum && $this->tieneInscripcionActivaEnOtroPensum($alumno, $pensum, $cicloLectivoId)) {
            $bloqueadores[] = 'Ya tienes una inscripción ACTIVA en otro pensum para este ciclo. Contacta a Administración Académica.';
        }

        // 7. Límite de materias por ciclo (4 o 5)
        $materiasInscritas = $this->contarMateriasCicloActual($alumno, $cicloLectivoId);
        $limite = $this->calcularLimiteMaterias($alumno, $pensum, $materiasInscritas);
        if ($materiasInscritas >= $limite) {
            $bloqueadores[] = "Has alcanzado el límite de {$limite} materias para este ciclo.";
        }

        // 8. Prerrequisitos (materia + UV mínimas)
        if ($pm && $pm->prerequisitos->isNotEmpty()) {
            $prereq = $pm->prerequisitos->first();

            if ($prereq->id_materia_requisito) {
                $aprobada = $this->alumnoAproboMateria($alumno, $prereq->id_materia_requisito);
                if (!$aprobada) {
                    $bloqueadores[] = "Debes aprobar primero: {$prereq->materiaRequisito->nombre}.";
                }
            }

            if ($prereq->uv_minimas_requeridas > 0) {
                $uvAprobadas = $this->uvAprobadas($alumno);
                if ($uvAprobadas < $prereq->uv_minimas_requeridas) {
                    $bloqueadores[] = "Necesitas al menos {$prereq->uv_minimas_requeridas} UV aprobadas (tienes {$uvAprobadas}).";
                }
            }
        }

        // 9. Máximo de reprobaciones
        $config = ConfiguracionAcademica::where('id_carrera', $alumno->id_carrera)->first();
        $maxReprob = $config->max_reprobaciones_materia ?? 3;
        $reprobaciones = $this->contarReprobaciones($alumno, $seccion->id_materia);
        if ($reprobaciones >= $maxReprob) {
            $bloqueadores[] = "Has reprobado esta materia {$reprobaciones} veces. Contacta a Administración Académica para gestionar un cambio de carrera.";
        }

        // 10. Horarios solapados (excluyendo la sección actual)
        $solapamiento = $this->detectarSolapamientoHorario($alumno, $seccion, $cicloLectivoId);
        if ($solapamiento) {
            $bloqueadores[] = "El horario de esta sección ({$solapamiento}) se cruza con otra materia ya inscrita.";
        }

        // 11. Solvencia financiera
        $solvencia = app(ValidarSolvenciaAction::class)->execute($alumno, $cicloLectivoId);
        if (!$solvencia['solvente']) {
            $bloqueadores = array_merge($bloqueadores, $solvencia['motivos']);
        }

        return [
            'puede'              => empty($bloqueadores),
            'bloqueadores'       => $bloqueadores,
            'limite_materias'    => $limite,
            'materias_inscritas' => $materiasInscritas,
        ];
    }
    
    /* ==========================================================
     * HELPERS
     * ========================================================== */

    /**
     * ¿Ya está inscrito en la MISMA materia este ciclo?
     * Excluye la sección exacta en la que está intentando inscribirse
     * (porque esa es la que ya tiene, no es un bloqueo real).
     */
    protected function yaInscritoEnMismaMateria(Alumnos $alumno, Seccion $seccion, int $cicloLectivoId): bool
    {
        return DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->where('i.id_ciclo_lectivo', $cicloLectivoId)
            ->where('i.estado_inscripcion', 'ACTIVA')
            ->where('s.id_materia', $seccion->id_materia)
            ->where('di.id_seccion', '!=', $seccion->seccion_id)  // excluir la sección actual
            ->whereIn('di.estado_materia', ['CURSANDO', 'APROBADA'])
            ->exists();
    }

    /**
     * ¿Tiene una inscripción ACTIVA en el ciclo actual, cuya materia NO pertenece
     * al pensum activo del alumno?
     */
    protected function tieneInscripcionActivaEnOtroPensum(Alumnos $alumno, Pensum $pensum, int $cicloLectivoId): bool
    {
        // IDs de materias del pensum activo del alumno
        $materiasPensum = $pensum->pensumMaterias()->pluck('id_materia')->all();

        // Materias inscritas activas en este ciclo
        $materiasInscritas = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->where('i.id_ciclo_lectivo', $cicloLectivoId)
            ->where('i.estado_inscripcion', 'ACTIVA')
            ->whereIn('di.estado_materia', ['CURSANDO', 'APROBADA'])
            ->distinct()
            ->pluck('s.id_materia')
            ->all();

        if (empty($materiasInscritas)) {
            return false;
        }

        // Hay al menos una materia inscrita que NO está en el pensum activo → otro pensum
        $fueraDelPensum = array_diff($materiasInscritas, $materiasPensum);

        return !empty($fueraDelPensum);
    }

    protected function contarMateriasCicloActual(Alumnos $alumno, int $cicloLectivoId): int
    {
        return DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->where('i.id_ciclo_lectivo', $cicloLectivoId)
            ->where('i.estado_inscripcion', 'ACTIVA')
            ->where('di.estado_materia', 'CURSANDO')
            ->count();
    }

    protected function calcularLimiteMaterias(Alumnos $alumno, ?Pensum $pensum, int $yaInscritas): int
    {
        if (!$pensum) return 4;

        $cicloActual = $this->detectarCicloActualAlumno($alumno, $pensum);
        if (!$cicloActual) return 4;

        $materiasCiclo = $pensum->pensumMaterias()
            ->where('ciclo_sugerido', $cicloActual)
            ->count();

        if ($materiasCiclo >= 5) return 5;

        $config = ConfiguracionAcademica::where('id_carrera', $alumno->id_carrera)->first();
        $cumMin = $config->cum_minimo_materia_extra ?? 8.5;
        $cum = $this->calcularCum($alumno);

        return $cum >= $cumMin ? 5 : 4;
    }

    public function obtenerLimiteMaterias(Alumnos $alumno, int $cicloLectivoId): array
    {
        $pensum = Pensum::where('id_carrera', $alumno->id_carrera)->where('estado', 'ACTIVO')->first();
        $inscritas = $this->contarMateriasCicloActual($alumno, $cicloLectivoId);
        return [
            'limite'    => $this->calcularLimiteMaterias($alumno, $pensum, $inscritas),
            'inscritas' => $inscritas,
        ];
    }

    protected function detectarCicloActualAlumno(Alumnos $alumno, Pensum $pensum): ?int
    {
        $pensumMaterias = $pensum->pensumMaterias()->get();
        $historial = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->select('s.id_materia', 'di.estado_materia')
            ->get()
            ->groupBy('id_materia')
            ->map(fn ($r) => $r->first());

        foreach ($pensumMaterias->groupBy('ciclo_sugerido')->sortKeys() as $ciclo => $materias) {
            $todasAprobadas = $materias->every(function ($m) use ($historial) {
                $r = $historial->get($m->id_materia);
                return $r && strtoupper($r->estado_materia) === 'APROBADA';
            });
            if (!$todasAprobadas) return (int) $ciclo;
        }

        return null;
    }

    protected function alumnoAproboMateria(Alumnos $alumno, int $materiaId): bool
    {
        return DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->where('s.id_materia', $materiaId)
            ->where('di.estado_materia', 'APROBADA')
            ->exists();
    }

    protected function uvAprobadas(Alumnos $alumno): int
    {
        return (int) DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->join('materias as m', 's.id_materia', '=', 'm.materia_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->where('di.estado_materia', 'APROBADA')
            ->sum('m.unidades_valorativas');
    }

    protected function contarReprobaciones(Alumnos $alumno, int $materiaId): int
    {
        return DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->where('s.id_materia', $materiaId)
            ->where('di.estado_materia', 'REPROBADA')
            ->count();
    }

    protected function calcularCum(Alumnos $alumno): float
    {
        $avg = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->whereIn('di.estado_materia', ['APROBADA', 'REPROBADA'])
            ->whereNotNull('di.promedio_final')
            ->avg('di.promedio_final');

        return round((float) $avg, 2);
    }

    /**
     * Detecta solapamiento de horarios EXCLUYENDO la sección actual del alumno,
     * para no auto-bloquearse con la materia que acaba de inscribir.
     */
    protected function detectarSolapamientoHorario(Alumnos $alumno, Seccion $seccion, int $cicloLectivoId): ?string
    {
        $seccion->loadMissing('horarios.horario');

        $horariosNuevos = $seccion->horarios
            ->map(fn ($sh) => $sh->horario)
            ->filter();

        if ($horariosNuevos->isEmpty()) {
            return null;
        }

        // Excluye la sección actual para evitar solaparse consigo misma
        $seccionesInscritas = DB::table('detalle_inscripcion as di')
            ->join('inscripciones as i', 'di.id_inscripcion', '=', 'i.inscripcion_id')
            ->join('seccion as s', 'di.id_seccion', '=', 's.seccion_id')
            ->join('seccion_horario as sh', 's.seccion_id', '=', 'sh.id_seccion')
            ->join('horarios as h', 'sh.id_horario', '=', 'h.horario_id')
            ->join('materias as m', 's.id_materia', '=', 'm.materia_id')
            ->where('i.id_alumno', $alumno->alumno_id)
            ->where('i.id_ciclo_lectivo', $cicloLectivoId)
            ->where('i.estado_inscripcion', 'ACTIVA')
            ->where('di.id_seccion', '!=', $seccion->seccion_id) // ← excluir actual
            ->whereIn('di.estado_materia', ['CURSANDO', 'APROBADA'])
            ->select('m.nombre as materia', 'h.dia', 'h.hora_inicio', 'h.hora_fin')
            ->get();

        foreach ($horariosNuevos as $nuevo) {
            foreach ($seccionesInscritas as $actual) {
                $mismoDia = $nuevo->dia->value === $actual->dia;
                if (!$mismoDia) continue;

                $nuevoIni = $this->normalizarHora($nuevo->hora_inicio);
                $nuevoFin = $this->normalizarHora($nuevo->hora_fin);
                $actIni   = $this->normalizarHora($actual->hora_inicio);
                $actFin   = $this->normalizarHora($actual->hora_fin);

                if ($nuevoIni < $actFin && $nuevoFin > $actIni) {
                    return sprintf(
                        '%s %s-%s con %s (%s-%s)',
                        $nuevo->dia->value,
                        $nuevoIni,
                        $nuevoFin,
                        $actual->materia,
                        $actIni,
                        $actFin
                    );
                }
            }
        }

        return null;
    }

    private function normalizarHora(string $hora): string
    {
        return substr($hora, 0, 5);
    }
}