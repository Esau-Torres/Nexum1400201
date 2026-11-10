<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Grupo;
use App\Models\CoordinadorFacultad\GrupoHorario;
use Illuminate\Support\Facades\DB;

class CreateGrupoAction
{
    public function execute(array $data): Grupo
    {
        return DB::transaction(function () use ($data) {
            $grupo = Grupo::create([
                'id_ciclo_lectivo' => $data['id_ciclo_lectivo'],
                'nombre'           => strtoupper($data['nombre']),
                'activo'           => (bool) ($data['activo'] ?? true),
            ]);

            $this->syncHorarios($grupo, $data['horarios'] ?? []);

            return $grupo->fresh('horarios');
        });
    }

    private function syncHorarios(Grupo $grupo, array $horarios): void
    {
        foreach ($horarios as $horarioId) {
            GrupoHorario::firstOrCreate([
                'id_grupo'   => $grupo->grupo_id,
                'id_horario' => (int) $horarioId,
            ]);
        }
    }
}