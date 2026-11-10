<?php

namespace App\Actions\Student;

use App\Enums\TipoSituacion;
use App\Models\Estudiante\SituacionUsuario;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;

class CrearSituacionAction
{
    public function execute(User $user, array $datos): SituacionUsuario
    {
        return DB::transaction(function () use ($user, $datos) {
            return SituacionUsuario::create([
                'id_usuario'           => $user->id,
                'nombre_usuario'       => trim($datos['nombre_usuario']),
                'carnet_institucional' => strtoupper(trim($datos['carnet_institucional'])),
                'correo'               => strtolower(trim($datos['correo'])),
                'tipo_situacion'       => $datos['tipo_situacion'],
                'detalle'              => trim($datos['detalle']),
                'creado_en'            => now(),
            ]);
        });
    }
}