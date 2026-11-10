<?php

namespace App\Rules;

use App\Enums\EstadoCiclo;
use App\Models\Academico\CicloLectivo;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class CambiarEstadoCicloRules
{
    /**
     * Valida el cambio de estado de un ciclo.
     * REGLAS CLAVE:
     *  - Un ciclo CERRADO solo puede volver a un estado anterior si fecha_actual < fecha_fin
     *    (i.e. fue cerrado por error).
     *  - Un ciclo CERRADO con fecha_fin <= hoy es TERMINAL.
     *  - No puede haber dos ciclos en MATRICULA_ABIERTA o ACTIVO a la vez.
     */
    public static function validate(CicloLectivo $ciclo, EstadoCiclo $nuevoEstado): ValidatorContract
    {
        $validator = Validator::make([], []); // Sin reglas base

        $validator->after(function ($v) use ($ciclo, $nuevoEstado) {

            // -- CASO A: El ciclo actual está CERRADO --
            if ($ciclo->estado === EstadoCiclo::CERRADO) {

                // Si la fecha fin ya pasó no se puede reabrir
                if ($ciclo->fecha_fin->isPast() || $ciclo->fecha_fin->isToday()) {
                    $v->errors()->add(
                        'ciclo_cerrado_terminal',
                        "El ciclo '{$ciclo->nombre_ciclo}' está cerrado de forma definitiva (el ciclo finalizo). No puede reabrirse."
                    );
                    return;
                }

                // Si la fecha fin NO ha pasado → fue cerrado por error, permitir reapertura
                // Solo validamos que el nuevo estado no sea CERRADO (ya lo está)
                if ($nuevoEstado === EstadoCiclo::CERRADO) {
                    $v->errors()->add(
                        'mismo_estado',
                        'El ciclo ya se encuentra en estado CERRADO.'
                    );
                    return;
                }

                // Permitimos reapertura. Caemos a las validaciones de "dos ciclos en curso".
            }

            // -- CASO B: Validación de "dos ciclos en curso" --
            if (in_array($nuevoEstado, [EstadoCiclo::ACTIVO, EstadoCiclo::MATRICULA_ABIERTA], true)) {
                $existeOtroEnCurso = CicloLectivo::where('ciclo_lectivo_id', '!=', $ciclo->ciclo_lectivo_id)
                    ->whereIn('estado', [
                        EstadoCiclo::ACTIVO->value,
                        EstadoCiclo::MATRICULA_ABIERTA->value,
                    ])
                    ->exists();

                if ($existeOtroEnCurso) {
                    $v->errors()->add(
                        'ciclo_en_curso',
                        'Ya existe otro ciclo en MATRICULA_ABIERTA o ACTIVO. Debe cerrarlo antes.'
                    );
                }
            }

            // -- CASO C: Cierre anticipado --
            if ($nuevoEstado === EstadoCiclo::CERRADO && $ciclo->estado !== EstadoCiclo::CERRADO) {
                if (!$ciclo->fecha_fin->isPast() && !$ciclo->fecha_fin->isToday()) {
                    $v->errors()->add(
                        'cierre_anticipado',
                        'No se puede cerrar el ciclo: la fecha de finalización aún no ha pasado.'
                    );
                }
            }
        });

        return $validator;
    }
}