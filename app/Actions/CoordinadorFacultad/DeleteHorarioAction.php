<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Horarios;
use RuntimeException;

class DeleteHorarioAction
{
    public function execute(Horarios $horario): void
    {
        if ($horario->seccionesHorario()->exists()) {
            throw new RuntimeException(
                'No se puede eliminar: el bloque tiene horarios de secciones asignados.'
            );
        }

        if ($horario->gruposHorario()->exists()) {
            throw new RuntimeException(
                'No se puede eliminar: el bloque está asignado a uno o más grupos.'
            );
        }

        $horario->delete();
    }
}