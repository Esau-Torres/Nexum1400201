<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Edificio;

class CreateEdificioAction
{
    public function execute(array $data): Edificio
    {
        return Edificio::create([
            'nombre'      => $data['nombre'],
            'niveles'     => $data['niveles'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo'      => (bool) ($data['activo'] ?? false),
        ]);
    }
}