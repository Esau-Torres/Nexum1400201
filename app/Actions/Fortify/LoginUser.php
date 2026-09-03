<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Fortify;

class LoginUser implements LoginResponse
{
    public function toResponse($request)
    {
        $user = Auth::user();
        $redirectTo = config('fortify.home', '/home');
        return redirect()->intended($redirectTo)->with('success', 'Inicio de sesión exitoso.');
    }
}
