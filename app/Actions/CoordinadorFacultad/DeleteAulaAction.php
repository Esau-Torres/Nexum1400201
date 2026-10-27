<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Aula;
use RuntimeException;

class DeleteAulaAction
{
    public function execute(Aula $aula): void
    {
        if ($aula->seccionesHorario()->exists()) {
            throw new RuntimeException(
                'No se puede eliminar: el aula tiene horarios de secciones asignados.'
            );
        }

        $aula->delete();
    }
}