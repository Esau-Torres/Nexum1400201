<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        dd('LoginResponse personalizado se está ejecutando');

        $user = auth()->user();

        // Log para depuración
        Log::info('LoginResponse ejecutado', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name
        ]);

        // Si es el docente (ID = 4), redirigir a home_docente
        if ($user->id === 4) {
            Log::info('Redirigiendo a home.docente');
            return redirect()->route('home.docente');
        }

        // Para otros usuarios, redirigir al home normal
        Log::info('Redirigiendo a home normal');
        return redirect()->route('home');
    }
}
