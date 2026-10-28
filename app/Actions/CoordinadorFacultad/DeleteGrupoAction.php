<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Grupo;
use RuntimeException;

class DeleteGrupoAction
{
    public function execute(Grupo $grupo): void
    {
        if ($grupo->secciones()->exists()) {
            throw new RuntimeException(
                'No se puede eliminar: el grupo tiene ' . $grupo->secciones()->count() . ' sección(es) asignada(s).'
            );
        }

        $grupo->delete();
    }
}