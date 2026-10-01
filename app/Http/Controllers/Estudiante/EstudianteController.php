<?php 

namespace App\Http\Controllers\Estudiante;

use App\Actions\Student\CreateStudentProfileAction;
use App\Http\Controllers\Controller;
use App\Models\Estudiante\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class EstudianteController extends Controller
{
    public function store(Request $request, User $user, CreateStudentProfileAction $action): RedirectResponse
    {
        $request->validate([
            'carrera_id' => ['required', 'integer', 'exists:carreras,carrera_id'],
        ]);

        try {
            $alumno = $action->execute($user, (int)$request->carrera_id);

            return redirect()->route('alumnos.index')
                ->with('success', "Perfil estudiantil creado. Carnet asignado: {$alumno->codigo_estudiante}");

        } catch (Throwable $e) {
            return back()
                ->with('error', 'Error al procesar el expediente: ' . $e->getMessage());
        }
    }
}