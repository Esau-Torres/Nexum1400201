<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoPerfilController extends Controller
{
    public function show(Request $request)
    {
        $alumno = $request->user()->alumno;

        abort_unless($alumno, 404);

        // Prioridad 1: foto del carnet
        if ($alumno->foto_carnet && Storage::disk('public')->exists($alumno->foto_carnet)) {
            return Storage::disk('public')->response($alumno->foto_carnet);
        }

        // Prioridad 2: foto del expediente
        $ruta = $alumno->documentos?->fotografia_personal;

        abort_unless(
            $ruta && Storage::disk('public')->exists($ruta),
            404,
            'No hay foto disponible.'
        );

        return Storage::disk('public')->response($ruta);
    }
}