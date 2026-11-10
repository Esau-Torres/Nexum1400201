<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CarnetController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'foto_carnet' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
                'dimensions:min_width=300,min_height=300',
            ],
            // Flag que envía el navegador si detectó un rostro
            'rostro_detectado' => ['required', 'boolean', 'accepted'],
        ], [
            'foto_carnet.required'      => 'Adjuntá una fotografía.',
            'foto_carnet.mimes'         => 'Formato inválido. Usá JPG o PNG.',
            'foto_carnet.max'           => 'La foto no debe superar 2 MB.',
            'foto_carnet.dimensions'    => 'La foto debe tener al menos 300×300 píxeles.',
            'rostro_detectado.accepted' => 'La foto no parece contener un rostro humano. Probá con otra imagen.',
        ]);

        $user   = $request->user();
        $alumno = $user->alumno;

        if (!$alumno) {
            return back()->with('error', 'No tenés perfil de alumno.');
        }

        try {
            $archivo = $request->file('foto_carnet');
            $ruta    = "estudiantes/carnet/{$alumno->alumno_id}";

            // Borrar la anterior si existe
            if ($alumno->foto_carnet && Storage::disk('public')->exists($alumno->foto_carnet)) {
                Storage::disk('public')->delete($alumno->foto_carnet);
            }

            // Guardar la nueva
            $rutaGuardada = $archivo->store($ruta, 'public');

            //   El sistema marca la foto como validada automáticamente
            //   porque el frontend ya confirmó que tiene un rostro.
            $alumno->update([
                'foto_carnet'             => $rutaGuardada,
                'foto_carnet_validada_at' => now(),
            ]);

            return back()->with('success', 'Foto del carnet actualizada y validada automáticamente.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo subir la foto: ' . $e->getMessage());
        }
    }
}