<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Horarios;
use RuntimeException;

class UpdateHorarioAction
{
    public function execute(Horarios $horario, array $data): Horarios
    {
        // Si tiene secciones asignadas, no dejar cambiar día/hora sin advertencia
        if ($horario->seccionesHorario()->exists()) {
            $cambiaHorario =
                $data['dia']         !== $horario->dia->value ||
                $data['hora_inicio'] !== $horario->hora_inicio ||
                $data['hora_fin']    !== $horario->hora_fin;

            if ($cambiaHorario) {
                throw new RuntimeException(
                    'No se puede cambiar día u hora: este bloque ya está asignado a una o más secciones.'
                );
            }
        }

        $horario->update([
            'id_ciclo_lectivo' => $data['id_ciclo_lectivo'],
            'dia'              => $data['dia'],
            'hora_inicio'      => $data['hora_inicio'],
            'hora_fin'         => $data['hora_fin'],
            'jornada'          => $data['jornada'],
        ]);

        return $horario->fresh();
    }
}