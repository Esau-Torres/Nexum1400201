<?php

namespace App\Actions\Teacher;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreCalificacionesAction
{
    public function handle(int $seccionId, int $evaluacionId, array $calificaciones, int $userId): array
    {
        try {
            $seccion = DB::table('seccion')
                ->join('docentes', 'seccion.id_docente', '=', 'docentes.docente_id')
                ->where('seccion.seccion_id', $seccionId)
                ->where('docentes.id_usuario', $userId)
                ->first();

            if (!$seccion) {
                return [
                    'success' => false,
                    'message' => 'No tienes permiso para modificar las notas de esta sección.',
                    'updated' => 0,
                    'created' => 0,
                ];
            }

            $evaluacion = DB::table('evaluaciones')
                ->join('categoria_evaluacion', 'evaluaciones.id_categoria', '=', 'categoria_evaluacion.categoria_id')
                ->where('evaluaciones.evaluacion_id', $evaluacionId)
                ->where('categoria_evaluacion.id_seccion', $seccionId)
                ->first();

            if (!$evaluacion) {
                return [
                    'success' => false,
                    'message' => 'La evaluación no pertenece a esta sección.',
                    'updated' => 0,
                    'created' => 0,
                ];
            }

            $updated = 0;
            $created = 0;

            DB::transaction(function () use ($evaluacionId, $calificaciones, $userId, $seccionId, &$updated, &$created) {
                foreach ($calificaciones as $calif) {
                    $detalleId = (int) $calif['detalle_inscripcion_id'];
                    $nota = (float) $calif['nota'];

                    $detalle = DB::table('detalle_inscripcion')
                        ->where('detalle_inscripcion_id', $detalleId)
                        ->where('id_seccion', $seccionId)
                        ->first();

                    if (!$detalle) {
                        continue;
                    }

                    $existente = DB::table('calificacion')
                        ->where('id_evaluacion', $evaluacionId)
                        ->where('id_detalle_inscripcion', $detalleId)
                        ->first();

                    if ($existente) {
                        DB::table('calificacion')
                            ->where('calificacion_id', $existente->calificacion_id)
                            ->update([
                                'nota' => $nota,
                                'modificado_por' => $userId,
                                'fecha_registro' => now(),
                            ]);
                        $updated++;
                    } else {
                        DB::table('calificacion')->insert([
                            'id_evaluacion' => $evaluacionId,
                            'id_detalle_inscripcion' => $detalleId,
                            'nota' => $nota,
                            'es_nota_diferida' => false,
                            'modificado_por' => $userId,
                            'fecha_registro' => now(),
                        ]);
                        $created++;
                    }
                }
            });

            return [
                'success' => true,
                'message' => "Calificaciones guardadas correctamente. {$created} nuevas, {$updated} actualizadas.",
                'updated' => $updated,
                'created' => $created,
            ];
        } catch (Throwable $e) {
            Log::error('Error en StoreCalificacionesAction: ' . $e->getMessage(), [
                'seccion_id' => $seccionId,
                'evaluacion_id' => $evaluacionId,
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'message' => 'Error al guardar las calificaciones: ' . $e->getMessage(),
                'updated' => 0,
                'created' => 0,
            ];
        }
    }
}
