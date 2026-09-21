<?php

namespace App\Actions\User;

use App\Models\Users\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserAccountAction
{
    public function execute(array $input): User
    {
        return User::create([
            'name'                => $input['name'],
            'email'               => $input['email'],
            'password'            => Hash::make(Str::random(16)),
            'id_tipo_documento'   => $input['id_tipo_documento'],
            'id_regional_activo'  => $input['id_regional_activo'],
            'documento_identidad' => $input['documento_identidad'],
            'fecha_nacimiento'    => $input['fecha_nacimiento'],
            'genero'              => $input['genero'],
            'estado_civil'        => $input['estado_civil'] ?? null,
            'celular'             => $input['celular'] ?? null,
            'direccion'           => $input['direccion'] ?? null,
            'estado'              => $input['estado']?? 0, 
        ]);
    }
}