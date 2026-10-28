<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Http\Controllers\Controller;
use App\Models\Academico\CicloLectivo;
use App\Enums\EstadoCiclo;
use App\Enums\TipoCiclo;
use App\Actions\AdminAcademic\CreateCicloLectivoAction;
use App\Actions\AdminAcademic\UpdateCicloLectivoAction;
use App\Actions\AdminAcademic\ChangeCicloStatusAction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CicloLectivoController extends Controller
{
    public function index(Request $request)
    {
        $query = CicloLectivo::query();

        // --- Búsqueda ---
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre_ciclo', 'ILIKE', "%{$search}%")
                  ->orWhere('anio', 'ILIKE', "%{$search}%");
            });
        }

        // --- Filtros ---
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }
        if ($request->filled('numero_ciclo')) {
            $query->where('numero_ciclo', $request->input('numero_ciclo'));
        }
        if ($request->filled('anio')) {
            $query->where('anio', $request->input('anio'));
        }

        $ciclos = $query
            ->orderBy('anio', 'desc')
            ->orderBy('numero_ciclo', 'desc')
            ->paginate(9)
            ->withQueryString();

        $estados = EstadoCiclo::cases();
        $tiposCiclo = TipoCiclo::cases();
        $anios = CicloLectivo::distinct()->orderBy('anio', 'desc')->pluck('anio');

        return view('adminacademico.ciclolectivo', compact('ciclos', 'estados', 'tiposCiclo', 'anios'));
    }

    public function store(Request $request, CreateCicloLectivoAction $action)
    {
        try {
            $ciclo = $action->execute($request->all());

            return redirect()
                ->route('admin-academico.ciclolectivo.ciclo')
                ->with('success', "Ciclo '{$ciclo->nombre_ciclo}' creado exitosamente.");
        } catch (ValidationException $e) {
            return back()
                ->with('error', $e->validator->errors()->first())
                ->withInput();
        } catch (Throwable $e) {
            return back()
                ->with('error', 'Error al crear el ciclo: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, CicloLectivo $ciclo, UpdateCicloLectivoAction $action)
    {
        try {
            $action->execute($ciclo, $request->all());

            return redirect()
                ->route('admin-academico.ciclolectivo.ciclo')
                ->with('success', "Ciclo '{$ciclo->nombre_ciclo}' actualizado correctamente.");
        } catch (ValidationException $e) {
            return back()
                ->with('error', $e->validator->errors()->first())
                ->withInput();
        } catch (Throwable $e) {
            return back()
                ->with('error', 'Error al actualizar el ciclo: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function changeStatus(Request $request, CicloLectivo $ciclo, ChangeCicloStatusAction $action)
    {
        $request->validate([
            'nuevo_estado' => ['required', Rule::enum(EstadoCiclo::class)],
        ]);

        try {
            $nuevoEstado = EstadoCiclo::from($request->input('nuevo_estado'));
            $action->execute($ciclo, $nuevoEstado);

            return back()->with('success', "Estado cambiado a '{$nuevoEstado->label()}' correctamente.");
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        } catch (Throwable $e) {
            return back()->with('error', 'Error al cambiar el estado: ' . $e->getMessage());
        }
    }
}