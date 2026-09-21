<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PruebaNotasController extends Controller
{
    public function index()
    {
        return view('docente.prueba-notas');
    }
}
