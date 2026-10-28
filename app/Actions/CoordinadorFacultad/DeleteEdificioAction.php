<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Edificio;
use RuntimeException;

class DeleteEdificioAction
{
    public function execute(Edificio $edificio): void
    {
        // Si tiene aulas con horarios asignados, no permitir
        $tieneHorarios = $edificio->aulas()
            ->whereHas('seccionesHorario')
            ->exists();

        if ($tieneHorarios) {
            throw new RuntimeException(
                'No se puede eliminar: hay aulas de este edificio con horarios asignados.'
            );
        }

        $edificio->delete();
    }
}