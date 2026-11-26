<?php

namespace App\Actions\Fortify;

use App\Enums\EstadoEstudiante;
use App\Models\Estudiante\Alumnos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;

class LoginUser implements LoginResponse
{
    // 0 inactivo, 1 activo, 2 bloqueado
    public function toResponse($request)
    {
        $user = Auth::user();

       if ($user->estado === 0) {
            Auth::logout();
            return redirect()->route('login')->with('warning', 'Tu cuenta aún no ha sido activada. Por favor, espera a que un administrador la valide.');
        }
        if ($user->estado === 2) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Tu cuenta ha sido desactivada. Por favor, contacta al soporte técnico para más información.');
        }

         // ─────────────────────────────────────────────
        // Resolver expediente activo para ESTUDIANTE
        // ─────────────────────────────────────────────
        if ($user->hasRole('ESTUDIANTE')) {
            $expediente = $this->resolverExpedienteActivo($user);

            if (!$expediente) {
                Auth::logout();
                return redirect()->route('login')->with('error',
                    'No tienes un expediente activo. Contacta a Administración Académica.'
                );
            }

            // Guardar en sesión para que el resto del sistema sepa con qué expediente trabajar
            session(['alumno_id_activo' => $expediente->alumno_id]);
        }

        return redirect()->intended(config('fortify.home', '/home'))->with('success', 'Inicio de sesión exitoso.');
    }

      /**
     * Resuelve el expediente con el que debe entrar el estudiante:
     * 1. Debe haber exactamente uno ACTIVO → se usa ese.
     * 2. Si no hay ACTIVO:
     *    - Si hay exactamente un GRADUADO → se usa ese.
     *    - Si hay múltiples GRADUADO → error (ambigüedad).
     *    - Si no hay GRADUADO → error.
     */
    private function resolverExpedienteActivo($user): ?Alumnos
    {
        $expedientes = Alumnos::where('id_usuario', $user->id)->get();

        $activos = $expedientes->where('estado_carrera', EstadoEstudiante::ACTIVO->value);

        if ($activos->count() === 1) {
            return $activos->first();
        }

        if ($activos->count() > 1) {
            return null;
        }

        $graduados = $expedientes->where('estado_carrera', EstadoEstudiante::GRADUADO->value);

        if ($graduados->count() === 1) {
            return $graduados->first();
        }

        return null;
    }
}
