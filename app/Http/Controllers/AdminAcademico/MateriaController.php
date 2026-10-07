<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Http\Controllers\Controller;
use App\Models\Academico\Materias;
use App\Models\Academico\Pensum;
use App\Enums\EstadoMateria;
use App\Actions\AdminAcademic\CreateMateriaAction;
use App\Actions\AdminAcademic\UpdateMateriaAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class MateriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Materias::query()->with(['pensumMaterias.pensum.carrera']);

        // -- Filtros --
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('id_pensum')) {
            $query->whereHas('pensumMaterias', function ($q) use ($request) {
                $q->where('id_pensum', $request->input('id_pensum'));
            });
        }

        $materias = $query->orderBy('codigo_materia')->get();

        // Catálogos para los filtros y modales
        $estados = EstadoMateria::cases();
        $pensums = Pensum::with('carrera')->orderBy('anio_vigencia', 'desc')->get();

        return view('adminacademico.materias', compact(
            'materias', 'estados', 'pensums'
        ));
    }

    public function store(Request $request, CreateMateriaAction $action)
    {
        try {
            $materia = $action->execute($request->all());

            return redirect()->to(route('admin-academico.materias.materias'))
            ->with('success', "Materia '{$materia->codigo_materia} - {$materia->nombre}' creada exitosamente.");
        } catch (ValidationException $e) {
            return back()
                ->with('error', $e->validator->errors()->first())
                ->withInput();
        } catch (Throwable $e) {
            return back()
                ->with('error', 'Error al crear la materia: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, Materias $materia, UpdateMateriaAction $action)
    {
        try {
            $action->execute($materia, $request->all());

            return redirect()
                ->route('admin-academico.materias.materias')
                ->with('success', "Materia: '{$materia->codigo_materia}' actualizada correctamente.");
        } catch (ValidationException $e) {
            return back()
                ->with('error', $e->validator->errors()->first())
                ->withInput();
        } catch (Throwable $e) {
            return back()
                ->with('error', 'Error al actualizar la materia: ' . $e->getMessage())
                ->withInput();
        }
    }
}