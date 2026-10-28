<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Academico\Calificacion;
use App\Models\Academico\CategoriaEvaluacion;
use App\Models\Academico\Evaluaciones;
use App\Models\Academico\PeriodoEvaluacion;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Estudiante\DetalleInscripcion;
use App\Actions\Teacher\StoreCalificacionesAction;
use App\Rules\StoreCalificacionesRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotasMateriaController extends Controller
{
    /**
     * Muestra la vista de calificaciones para una sección específica.
     */
    public function index(int $seccionId)
    {
        $user = Auth::user();
        $docente = $user->docente;

        if (!$docente) {
            abort(403, 'No tienes un perfil de docente asociado.');
        }

        // 1. Obtener la sección con sus relaciones
        $seccion = Seccion::with(['materia', 'cicloLectivo'])
            ->where('seccion_id', $seccionId)
            ->where('id_docente', $docente->docente_id)
            ->first();

        if (!$seccion) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        // 2. Obtener períodos del ciclo de esta sección (ordenados)
        $periodos = PeriodoEvaluacion::where('id_ciclo_lectivo', $seccion->id_ciclo_lectivo)
            ->where('estado', 'ACTIVO')
            ->orderBy('numero_periodo')
            ->get();

        // 3. Obtener categorías de la sección, con sus evaluaciones
        $categorias = CategoriaEvaluacion::with(['evaluaciones' => function ($query) {
                $query->orderBy('evaluacion_id');
            }])
            ->where('id_seccion', $seccionId)
            ->whereIn('id_periodo_evaluacion', $periodos->pluck('periodo_evaluacion_id'))
            ->get()
            ->groupBy('id_periodo_evaluacion');

        // 4. Obtener estudiantes inscritos con sus calificaciones
        $detalleInscripciones = DetalleInscripcion::with([
                'inscripcion.alumno.usuario',
                'calificaciones' => function ($query) use ($seccionId) {
                    // Solo traer calificaciones de evaluaciones de esta sección
                    $query->whereHas('evaluacion.categoria', function ($q) use ($seccionId) {
                        $q->where('id_seccion', $seccionId);
                    });
                },
            ])
            ->where('id_seccion', $seccionId)
            ->where('estado_materia', 'CURSANDO')
            ->get()
            ->sortBy(fn($d) => optional(optional(optional($d->inscripcion)->alumno)->usuario)->name ?? '');

        // 5. Armar array de evaluaciones agrupadas por período para la vista
        $evaluacionesPorPeriodo = [];
        foreach ($periodos as $periodo) {
            $cats = $categorias->get($periodo->periodo_evaluacion_id, collect());
            $evaluacionesPorPeriodo[$periodo->periodo_evaluacion_id] = [
                'periodo' => $periodo,
                'categorias' => $cats,
            ];
        }

        // 6. Preparar datos de estudiantes con notas mapeadas por evaluación
        $estudiantes = $detalleInscripciones->map(function ($detalle) {
            $alumno = $detalle->inscripcion?->alumno;
            $usuario = $alumno?->usuario;

            $notas = [];
            foreach ($detalle->calificaciones as $calif) {
                $notas[$calif->id_evaluacion] = $calif->nota;
            }

            return (object) [
                'detalle_inscripcion_id' => $detalle->detalle_inscripcion_id,
                'nombre' => $usuario?->name ?? 'Sin nombre',
                'codigo' => $alumno?->codigo_estudiante ?? 'S/C',
                'notas' => $notas,
            ];
        })->values();

        // 7. Preparar datos de la materia para la vista
        $materia = [
            'nombre' => $seccion->materia->nombre ?? 'Sin nombre',
            'codigo' => $seccion->materia->codigo_materia ?? 'S/C',
            'grupo' => $seccion->nombre_grupo,
            'ciclo' => $seccion->cicloLectivo?->nombre_ciclo ?? '',
            'total_estudiantes' => $estudiantes->count(),
        ];

        return view('docente.notas_materia', [
            'materia' => $materia,
            'seccionId' => $seccionId,
            'evaluacionesPorPeriodo' => $evaluacionesPorPeriodo,
            'estudiantes' => $estudiantes,
            'periodos' => $periodos,
        ]);
    }

    /**
     * Guarda las calificaciones de múltiples evaluaciones.
     */
    public function store(Request $request, int $seccionId)
    {
        $user = Auth::user();

        // Validar la estructura del request
        $validated = $request->validate([
            'evaluaciones' => ['required', 'array', 'min:1'],
            'evaluaciones.*.evaluacion_id' => ['required', 'integer', 'exists:evaluaciones,evaluacion_id'],
            'evaluaciones.*.calificaciones' => ['required', 'array', 'min:1'],
            'evaluaciones.*.calificaciones.*.detalle_inscripcion_id' => ['required', 'integer', 'exists:detalle_inscripcion,detalle_inscripcion_id'],
            'evaluaciones.*.calificaciones.*.nota' => ['required', 'numeric', 'min:0', 'max:10'],
        ], [
            'evaluaciones.required' => 'Debe enviar las evaluaciones a guardar.',
            'evaluaciones.array' => 'Las evaluaciones deben enviarse como array.',
            'evaluaciones.min' => 'Debe enviar al menos una evaluación.',
            'evaluaciones.*.evaluacion_id.required' => 'Cada evaluación debe tener un ID.',
            'evaluaciones.*.evaluacion_id.exists' => 'La evaluación seleccionada no existe.',
            'evaluaciones.*.calificaciones.required' => 'Cada evaluación debe tener calificaciones.',
            'evaluaciones.*.calificaciones.array' => 'Las calificaciones deben enviarse como array.',
            'evaluaciones.*.calificaciones.*.detalle_inscripcion_id.required' => 'Cada calificación debe tener un detalle de inscripción.',
            'evaluaciones.*.calificaciones.*.nota.required' => 'La nota es obligatoria.',
            'evaluaciones.*.calificaciones.*.nota.numeric' => 'La nota debe ser un número.',
            'evaluaciones.*.calificaciones.*.nota.min' => 'La nota mínima es 0.',
            'evaluaciones.*.calificaciones.*.nota.max' => 'La nota máxima es 10.',
        ]);

        $action = new StoreCalificacionesAction();
        $totalCreated = 0;
        $totalUpdated = 0;
        $errores = [];

        // Procesar cada evaluación
        foreach ($validated['evaluaciones'] as $evaluacionData) {
            $resultado = $action->handle(
                $seccionId,
                (int) $evaluacionData['evaluacion_id'],
                $evaluacionData['calificaciones'],
                $user->id
            );

            if (!$resultado['success']) {
                $errores[] = $resultado['message'];
                continue;
            }

            $totalCreated += $resultado['created'];
            $totalUpdated += $resultado['updated'];
        }

        // Retornar respuesta JSON para el fetch del frontend
        if (empty($errores)) {
            return response()->json([
                'success' => true,
                'message' => "Calificaciones guardadas correctamente. {$totalCreated} nuevas, {$totalUpdated} actualizadas.",
                'created' => $totalCreated,
                'updated' => $totalUpdated,
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Errores al guardar: ' . implode(', ', $errores),
            'created' => $totalCreated,
            'updated' => $totalUpdated,
        ], 422);
    }
}
