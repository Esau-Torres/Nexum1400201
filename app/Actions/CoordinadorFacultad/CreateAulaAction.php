<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Aula;

class CreateAulaAction
{
    public function execute(array $data): Aula
    {
        return Aula::create([
            'id_edificio' => $data['id_edificio'],
            'nombre_aula' => $data['nombre_aula'],
            'nivel'       => $data['nivel'],
            'capacidad'   => $data['capacidad'],
            'tipo'        => $data['tipo'],
            'activo'      => (bool) ($data['activo'] ?? false),
        ]);
    }
}