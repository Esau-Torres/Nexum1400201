<?php
namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
  public function index(): View
    {
        $usuario = auth()->user();
        $relations = ['roles', 'regionalactivo'];

        if ($usuario->hasRole('ESTUDIANTE')) {
            $relations[] = 'alumno.carrera.facultad';
            $relations[] = 'alumno.documentos';
        }

        if ($usuario->hasRole('DOCENTE')) {
            $relations[] = 'docente';
        }

        $usuario->load($relations);

        return view('home', compact('usuario'));
    }
}