<?php

namespace App\Http\Controllers\Estudiante;

use App\Actions\Student\CrearSituacionAction;
use App\Actions\Student\EliminarSituacionAction;
use App\Enums\TipoSituacion;
use App\Http\Controllers\Controller;
use App\Rules\Student\StoreSituacionRule;
use App\Models\Estudiante\SituacionUsuario;
use Illuminate\Http\Request;

class SituacionController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $alumno = $user->alumno;

        $situaciones = SituacionUsuario::deUsuario($user->id)
            ->visiblesParaAlumno() 
            ->recientes()
            ->paginate(10);

        $tipos = TipoSituacion::options();

        $prefill = [
            'nombre_usuario'       => $user->name ?? '',
            'carnet_institucional' => $alumno?->codigo_estudiante ?? '',
            'correo'               => $alumno?->correo_institucional ?? ($user->email ?? ''),
        ];

        return view('student.situacion_estudiante', compact('situaciones', 'tipos', 'prefill'));
    }

    public function store(StoreSituacionRule $request, CrearSituacionAction $action)
    {
        $action->execute($request->user(), $request->validated());

        return redirect()
            ->route('student.situaciones.index')
            ->with('success', 'Tu situación fue registrada correctamente. El área correspondiente la revisará a la brevedad.');
    }
    
    public function destroy(Request $request, int $id, EliminarSituacionAction $action)
    {
        $user = $request->user();

        $situacion = SituacionUsuario::deUsuario($user->id)->findOrFail($id);

        try {
            $action->execute($situacion, $user->id);

            return redirect()
                ->route('student.situaciones.index')
                ->with('success', 'La situación fue eliminada correctamente.');
        } catch (ValidationException $e) {
            return redirect()
                ->route('student.situaciones.index')
                ->with('error', collect($e->errors())->flatten()->first());
        }
    }
}