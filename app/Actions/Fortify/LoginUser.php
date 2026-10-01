<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $redirectTo = config('fortify.home', '/home');
        return redirect()->intended($redirectTo)->with('success', 'Inicio de sesión exitoso.');
    }
}
