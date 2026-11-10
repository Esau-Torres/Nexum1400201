<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class StoreCalificacionesRules implements ValidationRule
{
    protected int $seccionId;
    protected int $userId;

    public function __construct(int $seccionId, int $userId)
    {
        $this->seccionId = $seccionId;
        $this->userId = $userId;
    }

    /**
     * Ejecuta la validación de la regla.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1. Validar que el request tenga la estructura correcta
        if (!is_array($value) || !isset($value['evaluacion_id']) || !isset($value['calificaciones'])) {
            $fail('La estructura del request es inválida. Se requiere evaluacion_id y calificaciones.');
            return;
        }

        // 2. Validar que la evaluación exista
        $evaluacionId = (int) $value['evaluacion_id'];
        $evaluacion = DB::table('evaluaciones')
            ->where('evaluacion_id', $evaluacionId)
            ->first();

        if (!$evaluacion) {
            $fail("La evaluación con ID {$evaluacionId} no existe.");
            return;
        }

        // 3. Validar que la evaluación pertenezca a la sección (vía categoría)
        $categoria = DB::table('categoria_evaluacion')
            ->where('categoria_id', $evaluacion->id_categoria)
            ->where('id_seccion', $this->seccionId)
            ->first();

        if (!$categoria) {
            $fail("La evaluación no pertenece a esta sección.");
            return;
        }

        // 4. Validar que la sección pertenezca al docente autenticado
        $seccion = DB::table('seccion')
            ->join('docentes', 'seccion.id_docente', '=', 'docentes.docente_id')
            ->where('seccion.seccion_id', $this->seccionId)
            ->where('docentes.id_usuario', $this->userId)
            ->first();

        if (!$seccion) {
            $fail('No tienes permiso para modificar las notas de esta sección.');
            return;
        }

        // 5. Validar cada calificación individual
        if (!is_array($value['calificaciones']) || empty($value['calificaciones'])) {
            $fail('El array de calificaciones no puede estar vacío.');
            return;
        }

        foreach ($value['calificaciones'] as $index => $calif) {
            if (!isset($calif['detalle_inscripcion_id']) || !isset($calif['nota'])) {
                $fail("La calificación en posición {$index} debe tener detalle_inscripcion_id y nota.");
                return;
            }

            $detalleId = (int) $calif['detalle_inscripcion_id'];
            $nota = $calif['nota'];

            // Validar que el detalle_inscripcion pertenezca a la sección
            $detalle = DB::table('detalle_inscripcion')
                ->where('detalle_inscripcion_id', $detalleId)
                ->where('id_seccion', $this->seccionId)
                ->first();

            if (!$detalle) {
                $fail("El detalle de inscripción {$detalleId} no pertenece a esta sección.");
                return;
            }

            // Validar que la nota sea numérica entre 0 y 10
            if (!is_numeric($nota)) {
                $fail("La nota del estudiante {$detalleId} debe ser un número.");
                return;
            }

            $notaFloat = (float) $nota;
            if ($notaFloat < 0 || $notaFloat > 10) {
                $fail("La nota del estudiante {$detalleId} debe estar entre 0 y 10.");
                return;
            }

            // Validar máximo 1 decimal
            if (preg_match('/^\d+(\.\d{2,})$/', (string) $nota)) {
                $fail("La nota del estudiante {$detalleId} solo puede tener hasta 1 decimal.");
                return;
            }
        }
    }
}
