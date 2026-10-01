<?php


namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $usuario = auth()->user();

        // Redirección según el rol del usuario
        if ($usuario->hasRole('DOCENTE')) {
            return redirect()->route('home.docente');
        }
        $relations = ['roles', 'regionalactivo'];

        if ($usuario->hasRole('ESTUDIANTE')) {
            $relations[] = 'alumno.carrera.facultad';
            $relations[] = 'alumno.documentos';
            $relations[] = 'alumno.beneficioCicloActual.cicloLectivo'; 
        }

        if ($usuario->hasRole('DOCENTE')) {
            $relations[] = 'docente';
            return view('home', compact('usuario'));
        }

        $usuario->load($relations);
        return view('home', compact('usuario'));
    }
}

