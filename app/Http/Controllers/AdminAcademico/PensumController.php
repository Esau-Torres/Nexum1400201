<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Http\Controllers\Controller;
use App\Models\Academico\Pensum;
use App\Models\Academico\PensumMateria;
use App\Models\Academico\Prerequisito;
use App\Models\Academico\Materias;
use App\Models\Estudiante\Carreras;
use App\Models\Estudiante\Facultades;
use App\Enums\EstadoPensum;
use App\Actions\AdminAcademic\CreatePensumAction;
use App\Actions\AdminAcademic\ChangeEstadoPensumAction;
use App\Actions\AdminAcademic\AgregarMateriaAPensumAction;
use App\Actions\AdminAcademic\EliminarMateriaDePensumAction;
use App\Actions\AdminAcademic\AgregarPrerequisitoAction;
use App\Actions\AdminAcademic\EliminarPrerequisitoAction;
use App\Rules\CambiarEstadoPensumRules;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PensumController extends Controller
{
    // ===================================================
    //  LISTADO
    // ===================================================
    public function index(Request $request)
    {
        $query = Pensum::query()->with(['carrera.facultad', 'pensumMaterias.materia']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('id_carrera')) {
            $query->where('id_carrera', $request->input('id_carrera'));
        }

        if ($request->filled('id_facultad')) {
            $query->whereHas('carrera', function ($q) use ($request) {
                $q->where('id_facultad', $request->input('id_facultad'));
            });
        }

        $pensums = $query
            ->orderBy('anio_vigencia', 'desc')
            ->orderBy('pensum_id', 'desc')
            ->get();

        $estados = EstadoPensum::cases();
        $carreras = Carreras::with('facultad')->orderBy('nombre')->get();
        $facultades = Facultades::orderBy('nombre')->get();

        return view('adminacademico.pensum.index', compact('pensums', 'estados', 'carreras', 'facultades'));
    }

    // ===================================================
    //  DETALLE (malla curricular)
    // ===================================================
    public function show(Pensum $pensum)
    {
        $pensum->load([
            'carrera.facultad',
            'pensumMaterias.materia',
            'pensumMaterias.prerequisitos.materiaRequisito',
        ]);

        return view('adminacademico.pensum.show', compact('pensum'));
    }

    // ===================================================
    //  EDICIÓN (asignar materias + prerequisitos)
    // ===================================================
    public function edit(Pensum $pensum)
    {
        $pensum->load([
            'carrera.facultad',
            'pensumMaterias.materia',
            'pensumMaterias.prerequisitos.materiaRequisito',
        ]);

        $catalogoMaterias = Materias::where('estado', 'ACTIVO')
            ->orderBy('codigo_materia')
            ->get();

        $duracionCiclos = $pensum->carrera?->duracion_ciclos ?? 10;

        return view('adminacademico.pensum.edit', compact('pensum', 'catalogoMaterias', 'duracionCiclos'));
    }

    // ===================================================
    //  CREAR
    // ===================================================
    public function store(Request $request, CreatePensumAction $action)
    {
        try {
            $pensum = $action->execute($request->all());

            return redirect()
                ->route('admin-academico.pensum.edit', $pensum)
                ->with('success', "Pensum '{$pensum->nombre}' creado. Ahora asigne las materias por ciclo.");
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first())->withInput();
        } catch (Throwable $e) {
            return back()->with('error', 'Error al crear el pensum: ' . $e->getMessage())->withInput();
        }
    }

    // ===================================================
    //  CAMBIAR ESTADO
    // ===================================================
    public function changeEstado(Request $request, Pensum $pensum, ChangeEstadoPensumAction $action)
    {
        $request->validate([
            'nuevo_estado' => ['required', Rule::enum(EstadoPensum::class)],
        ]);

        try {
            $nuevo = EstadoPensum::from($request->input('nuevo_estado'));
            $action->execute($pensum, $nuevo);

            return back()->with('success', "Pensum cambiado a '{$nuevo->label()}' correctamente.");
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        } catch (Throwable $e) {
            return back()->with('error', 'Error al cambiar estado: ' . $e->getMessage());
        }
    }

    // ===================================================
    //  AGREGAR / QUITAR MATERIA
    // ===================================================
    public function addMateria(Request $request, Pensum $pensum, AgregarMateriaAPensumAction $action)
    {
        try {
            $action->execute($pensum, $request->all());

            return back()->with('success', 'Materia agregada al pensum.');
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first())->withInput();
        } catch (Throwable $e) {
            return back()->with('error', 'Error al agregar materia: ' . $e->getMessage())->withInput();
        }
    }

    public function removeMateria(PensumMateria $pensumMateria, EliminarMateriaDePensumAction $action)
    {
        try {
            $action->execute($pensumMateria);

            return back()->with('success', 'Materia eliminada del pensum.');
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        } catch (Throwable $e) {
            return back()->with('error', 'Error al eliminar materia: ' . $e->getMessage());
        }
    }

    // ===================================================
    //  AGREGAR / QUITAR PREREQUISITO
    // ===================================================
    public function addPrerequisito(Request $request, PensumMateria $pensumMateria, AgregarPrerequisitoAction $action)
    {
        try {
            $action->execute($pensumMateria, $request->all());

            return back()->with('success', 'Prerequisito agregado.');
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first())->withInput();
        } catch (Throwable $e) {
            return back()->with('error', 'Error al agregar prerequisito: ' . $e->getMessage())->withInput();
        }
    }

    public function removePrerequisito(Prerequisito $prerequisito, EliminarPrerequisitoAction $action)
    {
        try {
            $action->execute($prerequisito);

            return back()->with('success', 'Prerequisito eliminado.');
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        } catch (Throwable $e) {
            return back()->with('error', 'Error al eliminar prerequisito: ' . $e->getMessage());
        }
    }
}