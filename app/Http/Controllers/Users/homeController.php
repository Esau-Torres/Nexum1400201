<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
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

        if ($usuario->hasRole('ESTUDIANTE')) {
            $usuario->load([
                'alumno.carrera',
            ]);
            return view('home', compact('usuario'));
        }

        if ($usuario->hasRole('SUPER_ADMIN')) {
            return redirect()->route('superadmin.panel-administrativo');
        }

        // Fallback: si no tiene un rol específico, mostrar home por defecto
        return view('home', compact('usuario'));
    }
}
