<?php

namespace App\Http\Controllers\CoordinadorFacultad;

use App\Actions\CoordinadorFacultad\CorregirCalificacionAction;
use App\Enums\EstadoCiclo;
use App\Http\Controllers\Controller;
use App\Models\Academico\Calificacion;
use App\Models\Academico\CategoriaEvaluacion;
use App\Models\Academico\CicloLectivo;
use App\Models\Academico\PeriodoEvaluacion;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Estudiante\DetalleInscripcion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CorreccionNotasController extends Controller
{
    /**
     * Lista secciones cuyo ciclo sea ACTIVO.
     */
    public function index(): View
    {
        $cicloActivo = CicloLectivo::where('estado', EstadoCiclo::ACTIVO)
            ->orderByDesc('fecha_inicio')
            ->first();

        $secciones = collect();

        if ($cicloActivo) {
            $secciones = Seccion::with(['materia', 'docente.usuario', 'cicloLectivo'])
                ->where('id_ciclo_lectivo', $cicloActivo->ciclo_lectivo_id)
                ->orderBy('seccion_id')
                ->get();
        }

        return view('coordinador-facultad.correccion-notas', [
            'secciones'              => $secciones,
            'cicloActivo'            => $cicloActivo,
            'seccion'                => null,
            'evaluacionesPorPeriodo' => [],
            'estudiantes'            => collect(),
            'periodos'               => collect(),
        ]);
    }

    public function show(int $seccionId): View
    {
        $seccion = Seccion::with(['materia', 'docente.usuario', 'cicloLectivo'])
            ->findOrFail($seccionId);

        $this->validarCicloActivo($seccion);

        $periodos = PeriodoEvaluacion::where('id_ciclo_lectivo', $seccion->id_ciclo_lectivo)
            ->where('estado', 'ACTIVO')
            ->orderBy('numero_periodo')
            ->get();

        $categorias = CategoriaEvaluacion::with([
                'evaluaciones' => fn ($q) => $q->orderBy('evaluacion_id'),
                'periodo',
            ])
            ->where('id_seccion', $seccionId)
            ->whereIn('id_periodo_evaluacion', $periodos->pluck('periodo_evaluacion_id'))
            ->get()
            ->groupBy('id_periodo_evaluacion');

        $detalles = DetalleInscripcion::with([
                'inscripcion.alumno.usuario',
                'calificaciones.evaluacion.categoria.periodo',
            ])
            ->where('id_seccion', $seccionId)
            ->whereIn('estado_materia', ['CURSANDO', 'APROBADA', 'REPROBADA'])
            ->get()
            ->sortBy(fn ($d) => $d->inscripcion?->alumno?->usuario?->name ?? '');

        $evaluacionesPorPeriodo = [];
        foreach ($periodos as $periodo) {
            $evaluacionesPorPeriodo[$periodo->periodo_evaluacion_id] = [
                'periodo'    => $periodo,
                'categorias' => $categorias->get($periodo->periodo_evaluacion_id, collect()),
            ];
        }

        $estudiantes = $detalles->map(function ($detalle) {
            $alumno  = $detalle->inscripcion?->alumno;
            $usuario = $alumno?->usuario;

            $notas = [];
            foreach ($detalle->calificaciones as $calif) {
                $notas[$calif->id_evaluacion] = [
                    'calificacion_id' => $calif->calificacion_id,
                    'nota'            => (float) $calif->nota,
                    'es_diferida'     => (bool) $calif->es_nota_diferida,
                    'fecha_registro'  => $calif->fecha_registro,
                ];
            }

            return (object) [
                'detalle_inscripcion_id' => $detalle->detalle_inscripcion_id,
                'nombre'                 => $usuario?->name ?? 'Sin nombre',
                'codigo'                 => $alumno?->codigo_estudiante ?? 'S/C',
                'notas'                  => $notas,
            ];
        })->values();

        return view('coordinador-facultad.correccion-notas', [
            'seccion'                => $seccion,
            'evaluacionesPorPeriodo' => $evaluacionesPorPeriodo,
            'estudiantes'            => $estudiantes,
            'periodos'               => $periodos,
            'secciones'              => collect(),     // 👈 explícito
            'cicloActivo'            => $seccion->cicloLectivo,
        ]);
    }

    /**
     * Actualiza una calificación individual.
     */
    public function update(
        Request $request,
        int $seccionId,
        int $calificacionId,
        CorregirCalificacionAction $action
    ): RedirectResponse {
        $user = Auth::user();

        $validated = $request->validate([
            'nota'   => ['required', 'numeric', 'min:0', 'max:10'],
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'nota.required'   => 'La nota es obligatoria.',
            'nota.numeric'    => 'La nota debe ser un número.',
            'nota.min'        => 'La nota mínima es 0.',
            'nota.max'        => 'La nota máxima es 10.',
            'motivo.required' => 'Debe indicar el motivo de la corrección.',
            'motivo.min'      => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max'      => 'El motivo no puede superar los 500 caracteres.',
        ]);

        $seccion = Seccion::findOrFail($seccionId);
        $this->validarCicloActivo($seccion);

        $calificacion = Calificacion::where('calificacion_id', $calificacionId)
            ->whereHas('evaluacion.categoria', fn ($q) => $q->where('id_seccion', $seccionId))
            ->firstOrFail();

        try {
            $action->execute(
                calificacion: $calificacion,
                seccionId:    $seccionId,
                nuevaNota:    (float) $validated['nota'],
                usuario:      $user,
                motivo:       $validated['motivo'],
            );

            return redirect()
                ->route('coordinador-facultad.correccion-notas.show', $seccionId)
                ->with('success', 'Calificación corregida correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'No se pudo corregir: ' . $e->getMessage());
        }
    }

    /**
     * Valida que la sección pertenezca a un ciclo ACTIVO.
     */
    private function validarCicloActivo(Seccion $seccion): void
    {
        $ciclo = $seccion->cicloLectivo;

        if (!$ciclo || $ciclo->estado !== EstadoCiclo::ACTIVO) {
            abort(403, 'Solo se pueden corregir notas de secciones con ciclo ACTIVO.');
        }
    }
}