<?php

namespace App\Http\Controllers\CoordinadorFacultad;

use App\Http\Controllers\Controller;
use App\Models\Academico\CicloLectivo;
use App\Models\Academico\Materias;
use App\Models\CoordinadorFacultad\Aula;
use App\Models\CoordinadorFacultad\Grupo;
use App\Models\CoordinadorFacultad\Horarios;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Docente\Docentes;
use App\Actions\CoordinadorFacultad\CreateSeccionAction;
use App\Actions\CoordinadorFacultad\UpdateSeccionAction;
use App\Actions\CoordinadorFacultad\DeleteSeccionAction;
use App\Rules\CoordinadorFacultad\StoreSeccionRules;
use App\Enums\EstadoSeccion;
use App\Models\Academico\PensumMateria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SeccionesController extends Controller
{
    public function index(Request $request): View
    {
        $cicloActual = CicloLectivo::query()
        ->orderByRaw("
            CASE estado
                WHEN 'MATRICULA_ABIERTA' THEN 1
                WHEN 'ACTIVO' THEN 2
                WHEN 'CONFIGURACION' THEN 3
                WHEN 'CERRADO' THEN 4
                ELSE 5
            END
        ")
        ->orderByDesc('fecha_inicio')
        ->first();

        $idCicloFiltro = $request->filled('id_ciclo_lectivo') ? (int) $request->input('id_ciclo_lectivo') : $cicloActual?->ciclo_lectivo_id;

         $seccionesQuery = Seccion::query()
            ->with([
                'materia',
                'cicloLectivo',
                'docente.user',
                'grupo',
                'horarios.horario',
                'horarios.aula.edificio',
            ])
            ->orderByDesc('id_ciclo_lectivo')
            ->orderBy('nombre_grupo')
            ->orderBy('id_materia');

        if ($idCicloFiltro) {
            $seccionesQuery->where('id_ciclo_lectivo', $idCicloFiltro);
        }

        $secciones = $seccionesQuery->get();

        $ciclosLectivos     = CicloLectivo::orderByDesc('fecha_inicio')->get();
        $materias           = Materias::query()->where('estado', 'ACTIVO')->with(['pensumMaterias.pensum.carrera.facultad'])->orderBy('nombre')->get();
        $pensumMaterias     = PensumMateria::query()->with([ 'materia', 'pensum.carrera.facultad', ])->whereHas('materia', function ($query) { $query->where('estado', 'ACTIVO'); })->orderBy('id_pensum')->orderBy('ciclo_sugerido')->get();
        $materiasPorCarrera = $pensumMaterias->groupBy(fn ($pm) => $pm->pensum?->carrera?->nombre ?? 'Sin carrera');
        $docentes           = Docentes::with('user')->where('estado', 'ACTIVO')->get();
        $grupos             = Grupo::with('cicloLectivo')->orderByDesc('id_ciclo_lectivo')->orderBy('nombre')->get();
        $aulas              = Aula::with('edificio')->where('activo', true)->orderBy('id_edificio')->orderBy('nombre_aula')->get();
        $horarios           = Horarios::with('cicloLectivo')
            ->orderBy('id_ciclo_lectivo', 'desc')
            ->orderByRaw("CASE dia
                WHEN 'LUNES' THEN 1
                WHEN 'MARTES' THEN 2
                WHEN 'MIERCOLES' THEN 3
                WHEN 'JUEVES' THEN 4
                WHEN 'VIERNES' THEN 5
                WHEN 'SABADO' THEN 6
                ELSE 7 END")
            ->orderBy('hora_inicio')
            ->get();

        // Horarios agrupados por ciclo para el JS de los modales
        $horariosPorCiclo = $horarios->groupBy('id_ciclo_lectivo');

        return view('coordinador-facultad.secciones', [
            'secciones'        => $secciones,
            'ciclosLectivos'   => $ciclosLectivos,
            'materiasPorCarrera' => $materiasPorCarrera,
            'pensumMateria'     => $pensumMaterias,
            'materias'         => $materias,
            'docentes'         => $docentes,
            'grupos'           => $grupos,
            'aulas'            => $aulas,
            'horarios'         => $horarios,
            'horariosPorCiclo' => $horariosPorCiclo,
            'estados'          => EstadoSeccion::cases(),
            'idCicloFiltro'      => $idCicloFiltro,
        ]);
    }

    public function store(StoreSeccionRules $request, CreateSeccionAction $action): RedirectResponse
    {
        try {
            $seccion = $action->execute($request->validated());

            return redirect()
                ->route('coordinador-facultad.secciones.index')
                ->with('success', "Sección creada: {$seccion->materia->nombre} — Grupo {$seccion->nombre_grupo}.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo crear: ' . $e->getMessage());
        }
    }

    public function update(StoreSeccionRules $request, Seccion $seccion, UpdateSeccionAction $action): RedirectResponse
    {
        try {
            $action->execute($seccion, $request->validated());

            return redirect()
                ->route('coordinador-facultad.secciones.index')
                ->with('success', 'Sección actualizada correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function destroy(Seccion $seccion, DeleteSeccionAction $action): RedirectResponse
    {
        try {
            $action->execute($seccion);

            return redirect()
                ->route('coordinador-facultad.secciones.index')
                ->with('success', 'Sección eliminada correctamente.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }
}