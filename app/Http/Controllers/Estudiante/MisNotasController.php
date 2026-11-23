<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Academico\CategoriaEvaluacion;
use App\Models\Academico\CicloLectivo;
use App\Models\Academico\PeriodoEvaluacion;
use App\Models\Estudiante\DetalleInscripcion;
use App\Models\Estudiante\Inscripcion;
use Illuminate\Support\Facades\Auth;

class MisNotasController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $alumno = $user->alumno;

        if (!$alumno) {
            abort(403, 'No tienes un perfil de estudiante asociado.');
        }

        // 1. Ciclo a mostrar: ACTIVO o el último con inscripción
        $ciclo = CicloLectivo::where('estado', 'ACTIVO')
            ->orderByDesc('fecha_inicio')
            ->first();

        if (!$ciclo) {
            $cicloId = Inscripcion::where('id_alumno', $alumno->alumno_id)
                ->orderByDesc('id_ciclo_lectivo')
                ->value('id_ciclo_lectivo');
            $ciclo = $cicloId ? CicloLectivo::find($cicloId) : null;
        }

        if (!$ciclo) {
            return view('student.notas_student', [
                'ciclo'     => null,
                'secciones' => collect(),
                'periodos'  => collect(),
                'mensaje'   => 'No tienes un ciclo lectivo asociado.',
            ]);
        }

        // 2. Inscripción del alumno en ese ciclo
        $inscripcion = Inscripcion::where('id_alumno', $alumno->alumno_id)
            ->where('id_ciclo_lectivo', $ciclo->ciclo_lectivo_id)
            ->first();

        if (!$inscripcion) {
            return view('student.notas_student', [
                'ciclo'     => $ciclo,
                'secciones' => collect(),
                'periodos'  => collect(),
                'mensaje'   => 'No tienes inscripciones registradas en el ciclo actual.',
            ]);
        }

        // 3. Detalles de inscripción
        $detalles = DetalleInscripcion::with([
            'seccion.materia',
            'seccion.docente.usuario',
            'seccion.horarios.horario',
            'seccion.horarios.aula',
            'calificaciones.evaluacion.categoria.periodo',
        ])
            ->where('id_inscripcion', $inscripcion->inscripcion_id)
            ->whereIn('estado_materia', ['CURSANDO', 'APROBADA', 'REPROBADA'])
            ->get();

        // 4. Períodos del ciclo
        $periodos = PeriodoEvaluacion::where('id_ciclo_lectivo', $ciclo->ciclo_lectivo_id)
            ->where('estado', 'ACTIVO')
            ->orderBy('numero_periodo')
            ->get();

        // 5. Categorías de las secciones del alumno
        $seccionIds = $detalles->pluck('id_seccion')->unique()->values();

        $categorias = CategoriaEvaluacion::with([
                'evaluaciones' => fn ($q) => $q->orderBy('evaluacion_id'),
                'periodo',
            ])
            ->whereIn('id_seccion', $seccionIds)
            ->whereIn('id_periodo_evaluacion', $periodos->pluck('periodo_evaluacion_id'))
            ->get()
            ->groupBy('id_seccion');

        // 6. Armar estructura por sección
        $secciones = $detalles->map(function ($detalle) use ($categorias, $periodos) {
            $seccion = $detalle->seccion;

            // Notas indexadas por id_evaluacion
            $notas = $detalle->calificaciones
                ->keyBy('id_evaluacion')
                ->map(fn ($c) => [
                    'nota'           => (float) $c->nota,
                    'es_diferida'    => (bool) $c->es_nota_diferida,
                    'fecha_registro' => $c->fecha_registro,
                ]);

            $catsSeccion = $categorias->get($detalle->id_seccion, collect());

            // Evaluaciones planas de la sección
            $evaluaciones = [];
            foreach ($periodos as $periodo) {
                $catsPeriodo = $catsSeccion->where('id_periodo_evaluacion', $periodo->periodo_evaluacion_id);
                foreach ($catsPeriodo as $cat) {
                    foreach ($cat->evaluaciones as $eval) {
                        $evaluaciones[] = [
                            'evaluacion_id'    => $eval->evaluacion_id,
                            'nombre'           => $eval->nombre_evaluacion,
                            'tipo_instrumento' => $eval->tipo_instrumento,
                            'porcentaje'       => (float) $eval->porcentaje_actividad,
                            'categoria'        => $cat->tipo_categoria,
                            'periodo_numero'   => $periodo->numero_periodo,
                            'periodo_nombre'   => $periodo->nombre,
                            'fecha_aplicacion' => $eval->fecha_aplicacion,
                        ];
                    }
                }
            }

            // Nota final calculada (suma ponderada)
            $notaFinal          = 0.0;
            $porcentajeCubierto = 0.0;

            foreach ($evaluaciones as $ev) {
                $n = $notas->get($ev['evaluacion_id']);
                if ($n) {
                    $notaFinal          += $n['nota'] * $ev['porcentaje'] / 100;
                    $porcentajeCubierto += $ev['porcentaje'];
                }
            }

            // Estado
            $estadoBadge = match (true) {
                $porcentajeCubierto <= 0  => ['badge-sin-calificar', 'Sin calificar'],
                $notaFinal >= 8.0          => ['badge-aprobado', 'Excelente'],
                $notaFinal >= 6.0          => ['badge-aprobado', 'Aprobada'],
                $notaFinal >= 4.0          => ['badge-reprobado', 'En riesgo'],
                default                    => ['badge-reprobado', 'Reprobada'],
            };

            return [
                'detalle_inscripcion_id' => $detalle->detalle_inscripcion_id,
                'seccion'                => $seccion,
                'materia'                => $seccion?->materia,
                'docente'                => $seccion?->docente?->usuario?->name ?? 'Sin docente asignado',
                'horarios'               => $seccion?->horarios ?? collect(),
                'estado_materia'         => $detalle->estado_materia,
                'promedio_final'         => $detalle->promedio_final,
                'evaluaciones'           => $evaluaciones,
                'notas'                  => $notas,
                'nota_final_calculada'   => round($notaFinal, 3),
                'nota_final_redondeada'  => floor($notaFinal * 10) / 10,
                'porcentaje_cubierto'    => round($porcentajeCubierto, 2),
                'estado_badge'           => $estadoBadge,
            ];
        });

        return view('student.notas_student', [
            'ciclo'     => $ciclo,
            'secciones' => $secciones,
            'periodos'  => $periodos,
            'mensaje'   => null,
        ]);
    }
}