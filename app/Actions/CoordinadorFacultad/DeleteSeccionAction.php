<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\CoordinadorFacultad\Seccion;
use App\Models\CoordinadorFacultad\ClaseCompartida;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeleteSeccionAction
{
    public function execute(Seccion $seccion): void
    {
        if ($seccion->detallesInscripcion()->exists()) {
            throw new RuntimeException('No se puede eliminar: la sección tiene alumnos inscritos.');
        }

        if ($seccion->sesiones()->exists()) {
            throw new RuntimeException('No se puede eliminar: la sección tiene clases programadas.');
        }

        DB::transaction(function () use ($seccion) {
            // Recopilar clases compartidas que podrían quedar huérfanas
            $idsClase = $seccion->horarios()
                ->whereNotNull('id_clase_compartida')
                ->pluck('id_clase_compartida')
                ->unique();

            // Eliminar seccion_horario de esta sección
            $seccion->horarios()->delete();

            // Eliminar clases compartidas que quedaron sin secciones
            foreach ($idsClase as $idClase) {
                $sigueUsada = \DB::table('seccion_horario')
                    ->where('id_clase_compartida', $idClase)
                    ->exists();

                if (!$sigueUsada) {
                    ClaseCompartida::where('clase_compartida_id', $idClase)->delete();
                }
            }
            $seccion->delete();
        });
    }
}