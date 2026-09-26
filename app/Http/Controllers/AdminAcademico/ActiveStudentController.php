<?php

namespace App\Http\Controllers\AdminAcademico;

use App\Actions\AdminAcademic\CreateActiveStudentAction;
use App\Rules\StoreActiveStudentRules;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ActiveStudentController extends Controller
{
    public function index(): View
    {
        $tipo_documento_identidad = DB::table('tipo_documento_identidad')->get();
        $carreras = DB::table('carreras')->get();
        $regionales     = DB::table('regional_activo')->get();
        $ciclosLectivos = DB::table('ciclo_lectivo')->orderByDesc('fecha_inicio')->get();
        return view('adminacademico.create-student', compact('tipo_documento_identidad', 'carreras', 'regionales', 'ciclosLectivos'));
    }

    public function __construct(
        protected CreateActiveStudentAction $createActiveStudent
    ) {}

    public function store(StoreActiveStudentRules $request): RedirectResponse
    {
        try {
            $user = $this->createActiveStudent->execute(
                input: $request->validated(),
                admin: $request->user(),
                request: $request,
            );

            return redirect()
                ->route('admin-academico.create-student')
                ->with('success', "Estudiante {$user->name} creado y activado correctamente. Código: {$user->alumno->codigo_estudiante}");
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el estudiante. ' . $e->getMessage());
        }
    }
}