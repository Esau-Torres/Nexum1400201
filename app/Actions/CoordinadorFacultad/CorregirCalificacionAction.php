<?php

namespace App\Actions\CoordinadorFacultad;

use App\Models\Academico\Calificacion;
use App\Models\Academico\CicloLectivo;
use App\Models\CoordinadorFacultad\Seccion;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Enums\EstadoCiclo;
use RuntimeException;

class CorregirCalificacionAction
{
    /**
     * Corrige una calificación ya registrada.
     * Solo permite modificar si el ciclo de la sección está ACTIVO.
     */
    public function execute(
        Calificacion $calificacion,
        int $seccionId,
        float $nuevaNota,
        User $usuario,
        ?string $motivo = null
    ): Calificacion {
        // 1. Validar rango
        if ($nuevaNota < 0 || $nuevaNota > 10) {
            throw new RuntimeException('La nota debe estar entre 0 y 10.');
        }

        // 2. Validar que la calificación pertenezca a la sección
        $pertenece = Calificacion::where('calificacion_id', $calificacion->calificacion_id)
            ->whereHas('evaluacion.categoria', fn ($q) => $q->where('id_seccion', $seccionId))
            ->exists();

        if (!$pertenece) {
            throw new RuntimeException('La calificación no pertenece a esta sección.');
        }

        // 3. Validar que el ciclo de la sección esté ACTIVO
        $seccion = Seccion::find($seccionId);

        if (!$seccion) {
            throw new RuntimeException('La sección no existe.');
        }

        $ciclo = CicloLectivo::find($seccion->id_ciclo_lectivo);

        if (!$ciclo || $ciclo->estado !== EstadoCiclo::ACTIVO) {
            throw new RuntimeException('Solo se pueden corregir notas de secciones con ciclo ACTIVO.');
        }

        // 4. Ejecutar en transacción
        return DB::transaction(function () use ($calificacion, $nuevaNota, $usuario, $motivo) {
            $notaAnterior = (float) $calificacion->nota;

            $calificacion->update([
                'nota'           => $nuevaNota,
                'modificado_por' => $usuario->id,
                'fecha_registro' => now(),
            ]);

            // 5. Log de auditoría (sin tabla, solo archivo)
            Log::info('Corrección de calificación', [
                'calificacion_id' => $calificacion->calificacion_id,
                'seccion_id'      => $calificacion->evaluacion?->categoria?->id_seccion,
                'evaluacion_id'   => $calificacion->id_evaluacion,
                'alumno_id'       => $calificacion->detalleInscripcion?->inscripcion?->id_alumno,
                'nota_anterior'   => $notaAnterior,
                'nota_nueva'      => $nuevaNota,
                'motivo'          => $motivo,
                'modificado_por'  => $usuario->id,
                'rol'             => 'COORDINADOR_FACULTAD',
                'fecha'           => now()->toDateTimeString(),
            ]);

            return $calificacion->fresh();
        });
    }
}