<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Edificio;
use RuntimeException;

class UpdateEdificioAction
{
    public function execute(Edificio $edificio, array $data): Edificio
    {
        // No se puede reducir niveles por debajo del nivel más alto usado por sus aulas
        $nivelMaxAulas = $edificio->aulas()->max('nivel') ?? 0;

        if ((int) $data['niveles'] < $nivelMaxAulas) {
            throw new RuntimeException(
                "No podés reducir a {$data['niveles']} niveles: hay aulas en el nivel {$nivelMaxAulas}."
            );
        }

        $edificio->update([
            'nombre'      => $data['nombre'],
            'niveles'     => $data['niveles'],
            'descripcion' => $data['descripcion'] ?? null,
            'activo'      => (bool) ($data['activo'] ?? false),
        ]);

        return $edificio->fresh();
    }
}