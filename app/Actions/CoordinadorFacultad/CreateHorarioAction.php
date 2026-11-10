<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Horarios;
use App\Models\Users\User;

class CreateHorarioAction
{
    public function execute(array $data, User $user): Horarios
    {
        return Horarios::create([
            'id_ciclo_lectivo' => $data['id_ciclo_lectivo'],
            'dia'              => $data['dia'],
            'hora_inicio'      => $data['hora_inicio'],
            'hora_fin'         => $data['hora_fin'],
            'jornada'          => $data['jornada'],
            'creado_por'       => $user->id,
        ]);
    }
}