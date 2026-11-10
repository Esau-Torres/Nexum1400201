<?php

namespace App\Rules;

use App\Enums\EstadoPensum;
use App\Models\Academico\Pensum;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class CambiarEstadoPensumRules
{
    public static function validate(Pensum $pensum, EstadoPensum $nuevoEstado): ValidatorContract
    {
        $validator = Validator::make([], []);

        $validator->after(function ($v) use ($pensum, $nuevoEstado) {

            // -- Regla 1: No cambiar a ACTIVO si el pensum no está completo --
            if ($nuevoEstado === EstadoPensum::ACTIVO && !self::estaCompleto($pensum)) {
                $v->errors()->add(
                    'incompleto',
                    'El pensum no puede activarse: revise que todos los ciclos estén completos (4-5 materias por ciclo), que la carrera esté vinculada y que los prerequisitos sean válidos.'
                );
                return;
            }

            // -- Regla 2: Si ya hay un pensum ACTIVO en esa carrera, no permitir otro --
            if ($nuevoEstado === EstadoPensum::ACTIVO) {
                $existeActivo = Pensum::where('id_carrera', $pensum->id_carrera)
                    ->where('estado', EstadoPensum::ACTIVO->value)
                    ->where('pensum_id', '!=', $pensum->pensum_id)
                    ->exists();

                if ($existeActivo) {
                    $v->errors()->add(
                        'activo_existente',
                        'Ya existe un pensum ACTIVO para esta carrera. Debe desactivarlo antes.'
                    );
                    return;
                }
            }

            // -- Regla 3: No se puede pasar de ACTIVO a MODIFICADO directamente --
            if ($pensum->estado === EstadoPensum::ACTIVO && $nuevoEstado === EstadoPensum::MODIFICADO) {
                $v->errors()->add(
                    'transicion_invalida',
                    'Un pensum ACTIVO no puede pasar a MODIFICADO. Primero cámbielo a INACTIVO.'
                );
                return;
            }

            // -- Regla 4: Si el pensum no tiene carrera, forzar MODIFICADO --
            if (empty($pensum->id_carrera) && $nuevoEstado !== EstadoPensum::MODIFICADO) {
                $v->errors()->add(
                    'sin_carrera',
                    'Un pensum sin carrera vinculada solo puede estar en MODIFICADO.'
                );
            }
        });

        return $validator;
    }

    /**
     * Verifica que el pensum esté listo para ser ACTIVO.
     */
    public static function estaCompleto(Pensum $pensum): bool
    {
        $duracion = $pensum->carrera?->duracion_ciclos ?? 0;

        if ($duracion <= 0) {
            return false;
        }

        $materias = $pensum->pensumMaterias;

        // 1. Cada ciclo debe tener entre 4 y 5 materias
        $porCiclo = $materias->groupBy('ciclo_sugerido');

        for ($i = 1; $i <= $duracion; $i++) {
            $cantidad = $porCiclo->get($i)?->count() ?? 0;
            if ($cantidad < 4 || $cantidad > 5) {
                return false;
            }
        }

        // 2. No debe haber materias en ciclos fuera del rango
        if ($materias->where('ciclo_sugerido', '>', $duracion)->count() > 0) {
            return false;
        }

        // 3. Cada materia debe tener al menos 1 prerequisito o marcar explícitamente que no requiere
        //    (esto se puede omitir si tu institución permite materias sin prereq)
        //    -> lo dejamos opcional

        return true;
    }
}