<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Http\Controllers\Controller;
use App\Models\Academico\CicloLectivo;
use App\Models\Academico\EvaluacionDocente;
use App\Models\CoordinadorFacultad\Seccion;
use App\Rules\Student\StoreEvaluacionDocenteRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class EvaluacionDocenteController extends Controller
{
    public function index(Request $request): View
    {
        $ciclosLectivos = CicloLectivo::orderByDesc('fecha_inicio')->get();

        // Default inteligente: ciclo en matrícula abierta o activo
        $cicloActual = CicloLectivo::query()
            ->orderByRaw("
                CASE estado
                    WHEN 'MATRICULA_ABIERTA' THEN 1
                    WHEN 'ACTIVO' THEN 2
                    WHEN 'CONFIGURACION' THEN 3
                    ELSE 4
                END
            ")
            ->orderByDesc('fecha_inicio')
            ->first();

        $idCicloFiltro = $request->filled('id_ciclo_lectivo')
            ? (int) $request->input('id_ciclo_lectivo')
            : $cicloActual?->ciclo_lectivo_id;

        $query = EvaluacionDocente::query()
            ->with(['seccion.materia', 'seccion.docente.user', 'seccion.cicloLectivo', 'registradoPor'])
            ->orderByDesc('fecha_registro');

        if ($idCicloFiltro) {
            $query->where('id_ciclo_lectivo', $idCicloFiltro);
        }

        $evaluaciones = $query->get();

        // Secciones disponibles (del ciclo filtrado si hay, sino todas)
        $seccionesQuery = Seccion::query()
            ->with(['materia', 'docente.user', 'cicloLectivo', 'evaluacionesDocente'])
            ->orderBy('id_ciclo_lectivo', 'desc')
            ->orderBy('nombre_grupo');

        if ($idCicloFiltro) {
            $seccionesQuery->where('id_ciclo_lectivo', $idCicloFiltro);
        }

        $secciones = $seccionesQuery->get();

        $seccionesPorCiclo = $secciones->groupBy('id_ciclo_lectivo')->map(function ($items) {
            return $items
                ->filter(fn ($s) => $s->evaluacionesDocente->isEmpty())
                ->map(fn ($s) => [
                    'seccion_id' => $s->seccion_id,
                    'materia'    => $s->materia?->nombre ?? '—',
                    'codigo'     => $s->materia?->codigo_materia ?? '',
                    'grupo'      => $s->nombre_grupo,
                    'docente'    => $s->docente?->user?->name ?? '—',
                    'label'      => ($s->materia?->nombre ?? '—') . ' — Grupo ' . $s->nombre_grupo . ' (' . ($s->docente?->user?->name ?? 'Sin docente') . ')',
                ])->values();
        });

        return view('adminacademico.registro_evaluacion_docente', [
            'evaluaciones'      => $evaluaciones,
            'ciclosLectivos'    => $ciclosLectivos,
            'seccionesPorCiclo' => $seccionesPorCiclo,
            'idCicloFiltro'     => $idCicloFiltro,
            'cicloDefault'      => $cicloActual
        ]);
    }

    public function store(StoreEvaluacionDocenteRules $request): RedirectResponse
    {
        try {
            EvaluacionDocente::create([
                'id_seccion'       => $request->input('id_seccion'),
                'id_ciclo_lectivo' => $request->input('id_ciclo_lectivo'),
                'url'              => $request->input('url'),
                'fecha_registro'   => now(),
                'registrado_por'   => $request->user()->id,
            ]);

            return redirect()
                ->route('admin-academico.evaluaciones.index', [
                    'id_ciclo_lectivo' => $request->input('id_ciclo_lectivo'),
                ])
                ->with('success', 'Encuesta registrada correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo registrar: ' . $e->getMessage());
        }
    }

    public function update(
        StoreEvaluacionDocenteRules $request,
        EvaluacionDocente $evaluacion
    ): RedirectResponse {
        try {
            $evaluacion->update([
                'url' => $request->input('url'),
            ]);

            return redirect()
                ->route('admin-academico.evaluaciones.index', [
                    'id_ciclo_lectivo' => $evaluacion->id_ciclo_lectivo,
                ])
                ->with('success', 'Encuesta actualizada correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, EvaluacionDocente $evaluacion): RedirectResponse
    {
        try {
            $idCiclo = $evaluacion->id_ciclo_lectivo;
            $evaluacion->delete();

            return redirect()
                ->route('admin-academico.evaluaciones.index', [
                    'id_ciclo_lectivo' => $request->filled('filtro_ciclo')
                        ? $request->input('filtro_ciclo')
                        : $idCiclo,
                ])
                ->with('success', 'Encuesta eliminada correctamente.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }
}