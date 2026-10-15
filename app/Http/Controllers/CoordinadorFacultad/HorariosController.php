<?php

namespace App\Http\Controllers\CoordinadorFacultad;

use App\Http\Controllers\Controller;
use App\Models\CoordinadorFacultad\Horarios;
use App\Models\Academico\CicloLectivo;
use App\Actions\CoordinadorFacultad\CreateHorarioAction;
use App\Actions\CoordinadorFacultad\UpdateHorarioAction;
use App\Actions\CoordinadorFacultad\DeleteHorarioAction;
use App\Rules\CoordinadorFacultad\StoreHorarioRules;
use App\Models\CoordinadorFacultad\Grupo;
use App\Actions\CoordinadorFacultad\CreateGrupoAction;
use App\Actions\CoordinadorFacultad\UpdateGrupoAction;
use App\Actions\CoordinadorFacultad\DeleteGrupoAction;
use App\Rules\CoordinadorFacultad\StoreGrupoRules;
use App\Enums\DiaSemana;
use App\Enums\TipoJornada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class HorariosController extends Controller
{
    public function index(): View
    {
        $ciclosLectivos = CicloLectivo::orderByDesc('fecha_inicio')->get();

        $horarios = Horarios::query()
            ->with(['cicloLectivo'])
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

        $grupos = Grupo::query()
            ->with(['cicloLectivo', 'horarios', 'secciones'])
            ->orderByDesc('id_ciclo_lectivo')
            ->orderBy('nombre')
            ->get();

        $horariosPorCiclo = $horarios->groupBy('id_ciclo_lectivo');

        return view('coordinador-facultad.horarios-grupohorario', [
            'ciclosLectivos' => $ciclosLectivos,
            'horarios'       => $horarios,
            'grupos'           => $grupos,
            'horariosPorCiclo' => $horariosPorCiclo,
            'dias'           => DiaSemana::ordenados(),
            'jornadas'       => TipoJornada::cases(),
        ]);
    }

    // ============================================================
    // HORARIOS
    // ============================================================

    public function storeHorario(
        StoreHorarioRules $request,
        CreateHorarioAction $action
    ): RedirectResponse {
        try {
            $horario = $action->execute($request->validated(), $request->user());

            return redirect()
                ->route('coordinador-facultad.horarios-grupohorario.index')
                ->with('success', "Bloque horario '{$horario->etiqueta}' creado correctamente.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo crear: ' . $e->getMessage());
        }
    }

    public function updateHorario(
        StoreHorarioRules $request,
        Horarios $horario,
        UpdateHorarioAction $action
    ): RedirectResponse {
        try {
            $action->execute($horario, $request->validated());

            return redirect()
                ->route('coordinador-facultad.horarios-grupohorario.index')
                ->withFragment('tab-horarios')
                ->with('success', 'Bloque horario actualizado correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function deleteHorario(Horarios $horario, DeleteHorarioAction $action): RedirectResponse
    {
        try {
            $action->execute($horario);

            return redirect()
                ->route('coordinador-facultad.horarios-grupohorario.index')
                ->withFragment('tab-horarios')
                ->with('success', 'Bloque horario eliminado correctamente.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }

    // ============================================================
    // Grupos de Horarios (son las 2:00 de la madrugada :/ )
    // ============================================================

    public function storeGrupo(StoreGrupoRules $request, CreateGrupoAction $action): RedirectResponse
    {
        try {
            $grupo = $action->execute($request->validated());

            return redirect()
                ->route('coordinador-facultad.horarios-grupohorario.index')
                ->withFragment('tab-grupos')
                ->with('success', "Grupo '{$grupo->nombre}' creado con {$grupo->horarios->count()} bloque(s) horario(s).");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo crear: ' . $e->getMessage());
        }
    }

    public function updateGrupo(StoreGrupoRules $request, Grupo $grupo, UpdateGrupoAction $action): RedirectResponse
    {
        try {
            $action->execute($grupo, $request->validated());

            return redirect()
                ->route('coordinador-facultad.horarios-grupohorario.index')
                ->withFragment('tab-grupos')
                ->with('success', 'Grupo actualizado correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function deleteGrupo(Grupo $grupo, DeleteGrupoAction $action): RedirectResponse
    {
        try {
            $action->execute($grupo);

            return redirect()
                ->route('coordinador-facultad.horarios-grupohorario.index')
                ->withFragment('tab-grupos')
                ->with('success', 'Grupo eliminado correctamente.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }
}