<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Estudiante\Alumnos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoPerfilController extends Controller
{
    public function show(Request $request)
    {
        $alumno = Alumnos::activoDeUsuario($request->user()->id);

        abort_unless($alumno, 404);

        // Prioridad 1: foto del carnet (propia del expediente)
        if ($alumno->foto_carnet && Storage::disk('public')->exists($alumno->foto_carnet)) {
            return Storage::disk('public')->response($alumno->foto_carnet);
        }

        // Prioridad 2: foto del expediente (heredada si es un expediente derivado)
        $ruta = $alumno->documentos_heredados?->fotografia_personal;

        abort_unless(
            $ruta && Storage::disk('public')->exists($ruta),
            404,
            'No hay foto disponible.'
        );

        return Storage::disk('public')->response($ruta);
    }
}