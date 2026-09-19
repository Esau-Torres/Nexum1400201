<?php
namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        if ($usuario->hasRole('ESTUDIANTE')) {
            $usuario->load([
                'alumno.carrera',
            ]);
        }

        return view('home', compact('usuario'));
    }
}