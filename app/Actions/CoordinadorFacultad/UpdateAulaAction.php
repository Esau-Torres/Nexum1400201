<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Aula;
use RuntimeException;

class UpdateAulaAction
{
    public function execute(Aula $aula, array $data): Aula
    {
        // No reducir capacidad por debajo de la sección más grande que la usa
        $nuevaCapacidad = (int) $data['capacidad'];

        $maxCupo = $aula->seccionesHorario()
            ->join('seccion', 'seccion.seccion_id', '=', 'seccion_horario.id_seccion')
            ->max('seccion.cupo_maximo') ?? 0;

        if ($nuevaCapacidad < $maxCupo) {
            throw new RuntimeException(
                "No podés reducir la capacidad a {$nuevaCapacidad}: hay secciones que necesitan {$maxCupo} cupos."
            );
        }

        $aula->update([
            'id_edificio' => $data['id_edificio'],
            'nombre_aula' => $data['nombre_aula'],
            'nivel'       => $data['nivel'],
            'capacidad'   => $data['capacidad'],
            'tipo'        => $data['tipo'],
            'activo'      => (bool) ($data['activo'] ?? false),
        ]);

        return $aula->fresh();
    }
}