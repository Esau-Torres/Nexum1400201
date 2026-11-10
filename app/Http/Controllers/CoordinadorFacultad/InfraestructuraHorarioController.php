<?php

namespace App\Http\Controllers\CoordinadorFacultad;

use App\Http\Controllers\Controller;
use App\Models\CoordinadorFacultad\Aula;
use App\Models\CoordinadorFacultad\Edificio;
use App\Models\Academico\CicloLectivo;
use App\Actions\CoordinadorFacultad\CreateEdificioAction;
use App\Actions\CoordinadorFacultad\UpdateEdificioAction;
use App\Actions\CoordinadorFacultad\DeleteEdificioAction;
use App\Actions\CoordinadorFacultad\CreateAulaAction;
use App\Actions\CoordinadorFacultad\UpdateAulaAction;
use App\Actions\CoordinadorFacultad\DeleteAulaAction;
use App\Rules\CoordinadorFacultad\StoreEdificioRules;
use App\Rules\CoordinadorFacultad\StoreAulaRules;
use App\Enums\TipoAula;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class InfraestructuraHorarioController extends Controller
{
    public function index(): View
    {
        $edificios = Edificio::query()
            ->with(['aulas' => fn ($q) => $q->orderBy('nivel')->orderBy('nombre_aula')])
            ->orderBy('nombre')
            ->get();

        $aulas = Aula::query()
            ->with('edificio')
            ->orderBy('id_edificio')
            ->orderBy('nivel')
            ->orderBy('nombre_aula')
            ->get();

        $ciclosLectivos = CicloLectivo::orderByDesc('fecha_inicio')->get();

        return view('coordinador-facultad.infraestructura-horarios', [
            'edificios'      => $edificios,
            'aulas'          => $aulas,
            'ciclosLectivos' => $ciclosLectivos,
            'tiposAula'      => TipoAula::cases(),
        ]);
    }

    // ============================================================
    // EDIFICIOS
    // ============================================================

    public function storeEdificio(StoreEdificioRules $request, CreateEdificioAction $action): RedirectResponse
    {
        try {
            $edificio = $action->execute($request->validated());

            return redirect()
                ->route('coordinador-facultad.infraestructura-horarios.index')
                ->with('success', "Edificio '{$edificio->nombre}' creado correctamente.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo crear: ' . $e->getMessage());
        }
    }

    public function updateEdificio(
        StoreEdificioRules $request,
        Edificio $edificio,
        UpdateEdificioAction $action
    ): RedirectResponse {
        try {
            $action->execute($edificio, $request->validated());

            return redirect()
                ->route('coordinador-facultad.infraestructura-horarios.index')
                ->with('success', 'Edificio actualizado correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function deleteEdificio(Edificio $edificio, DeleteEdificioAction $action): RedirectResponse
    {
        try {
            $action->execute($edificio);

            return redirect()
                ->route('coordinador-facultad.infraestructura-horarios.index')
                ->with('success', 'Edificio eliminado correctamente.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }

    // ============================================================
    // AULAS
    // ============================================================

    public function storeAula(StoreAulaRules $request, CreateAulaAction $action): RedirectResponse
    {
        try {
            $aula = $action->execute($request->validated());

            return redirect()
                ->route('coordinador-facultad.infraestructura-horarios.index')
                ->with('success', "Aula '{$aula->nombre_aula}' creada correctamente.");
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo crear: ' . $e->getMessage());
        }
    }

    public function updateAula(
        StoreAulaRules $request,
        Aula $aula,
        UpdateAulaAction $action
    ): RedirectResponse {
        try {
            $action->execute($aula, $request->validated());

            return redirect()
                ->route('coordinador-facultad.infraestructura-horarios.index')
                ->with('success', 'Aula actualizada correctamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo actualizar: ' . $e->getMessage());
        }
    }

    public function deleteAula(Aula $aula, DeleteAulaAction $action): RedirectResponse
    {
        try {
            $action->execute($aula);

            return redirect()
                ->route('coordinador-facultad.infraestructura-horarios.index')
                ->with('success', 'Aula eliminada correctamente.');
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', $e->getMessage());
        }
    }
}