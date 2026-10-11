<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Http\Controllers\Controller;
use App\Models\Estudiante\Facultades;
use App\Models\Estudiante\Carreras;
use App\Models\Academico\NivelAcademico;
use App\Actions\AdminAcademic\CreateFacultadAction;
use App\Actions\AdminAcademic\UpdateFacultadAction;
use App\Actions\AdminAcademic\DeleteFacultadAction;
use App\Actions\AdminAcademic\CreateCarreraAction;
use App\Actions\AdminAcademic\UpdateCarreraAction;
use App\Actions\AdminAcademic\DeleteCarreraAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class FacultadCarreraController extends Controller
{
    public function index(Request $request)
    {
        // Facultades
        $qFac = Facultades::query()->withCount('carreras');

        $facultades = $qFac->orderBy('nombre')->get();

        // Carreras
        $qCar = Carreras::query()
            ->with(['facultad', 'nivel'])
            ->withCount('pensums');

        if ($request->filled('id_facultad')) {
            $qCar->where('id_facultad', $request->input('id_facultad'));
        }

        if ($request->filled('id_nivel_academico')) {
            $qCar->where('id_nivel_academico', $request->input('id_nivel_academico'));
        }

        $carreras = $qCar->orderBy('nombre')->get();

        $niveles = NivelAcademico::where('activo', true)->orderBy('orden')->get();

        return view('adminacademico.facultad', compact(
            'facultades', 'carreras', 'niveles'
        ));
    }

    // ============================================
    //  FACULTADES
    // ============================================
    public function storeFacultad(Request $request, CreateFacultadAction $action)
    {
        try {
            $facultad = $action->execute($request->all());
            return back()->with('success', "Facultad '{$facultad->nombre}' creada correctamente.");
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first())->withInput();
        } catch (Throwable $e) {
            return back()->with('error', 'Error al crear la facultad: ' . $e->getMessage())->withInput();
        }
    }

    public function updateFacultad(Request $request, Facultades $facultad, UpdateFacultadAction $action)
    {
        try {
            $action->execute($facultad, $request->all());
            return back()->with('success', "Facultad actualizada correctamente.");
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first())->withInput();
        } catch (Throwable $e) {
            return back()->with('error', 'Error al actualizar la facultad: ' . $e->getMessage())->withInput();
        }
    }

    public function deleteFacultad(Facultades $facultad, DeleteFacultadAction $action)
    {
        try {
            $action->execute($facultad);
            return back()->with('success', "Facultad eliminada correctamente.");
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        } catch (Throwable $e) {
            return back()->with('error', 'Error al eliminar la facultad: ' . $e->getMessage());
        }
    }

    // ============================================
    //  CARRERAS
    // ============================================
    public function storeCarrera(Request $request, CreateCarreraAction $action)
    {
        try {
            $carrera = $action->execute($request->all());
            return back()->with('success', "Carrera '{$carrera->nombre}' creada correctamente.");
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first())->withInput();
        } catch (Throwable $e) {
            return back()->with('error', 'Error al crear la carrera: ' . $e->getMessage())->withInput();
        }
    }

    public function updateCarrera(Request $request, Carreras $carrera, UpdateCarreraAction $action)
    {
        try {
            $action->execute($carrera, $request->all());
            return back()->with('success', "Carrera actualizada correctamente.");
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first())->withInput();
        } catch (Throwable $e) {
            return back()->with('error', 'Error al actualizar la carrera: ' . $e->getMessage())->withInput();
        }
    }

    public function deleteCarrera(Carreras $carrera, DeleteCarreraAction $action)
    {
        try {
            $action->execute($carrera);
            return back()->with('success', "Carrera eliminada correctamente.");
        } catch (ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        } catch (Throwable $e) {
            return back()->with('error', 'Error al eliminar la carrera: ' . $e->getMessage());
        }
    }
}