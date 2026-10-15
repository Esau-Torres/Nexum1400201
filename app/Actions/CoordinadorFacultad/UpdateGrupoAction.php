<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Grupo;
use App\Models\CoordinadorFacultad\GrupoHorario;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateGrupoAction
{
    public function execute(Grupo $grupo, array $data): Grupo
    {
        return DB::transaction(function () use ($grupo, $data) {
            // Si tiene secciones asignadas, no permitir cambiar ciclo
            if ($grupo->secciones()->exists() && (int) $data['id_ciclo_lectivo'] !== $grupo->id_ciclo_lectivo) {
                throw new RuntimeException(
                    'No se puede cambiar el ciclo de un grupo que ya tiene secciones asignadas.'
                );
            }

            $grupo->update([
                'id_ciclo_lectivo' => $data['id_ciclo_lectivo'],
                'nombre'           => strtoupper($data['nombre']),
                'activo'           => (bool) ($data['activo'] ?? false),
            ]);

            // Sincronizar horarios: quitar los que ya no están, agregar los nuevos
            $nuevos = collect($data['horarios'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();

            // Si tiene secciones, no permitir quitar bloques que estén usados
            if ($grupo->secciones()->exists()) {
                $bloquesEnUso = $grupo->secciones()
                    ->with('horarios')
                    ->get()
                    ->flatMap->horarios
                    ->pluck('id_horario')
                    ->unique();

                $faltantes = $bloquesEnUso->diff($nuevos);

                if ($faltantes->isNotEmpty()) {
                    throw new RuntimeException(
                        'No se pueden quitar bloques que ya están siendo usados por secciones del grupo.'
                    );
                }
            }

            // Eliminar los que ya no están
            GrupoHorario::where('id_grupo', $grupo->grupo_id)
                ->whereNotIn('id_horario', $nuevos)
                ->delete();

            // Agregar los nuevos
            foreach ($nuevos as $horarioId) {
                GrupoHorario::firstOrCreate([
                    'id_grupo'   => $grupo->grupo_id,
                    'id_horario' => $horarioId,
                ]);
            }

            return $grupo->fresh('horarios');
        });
    }
}