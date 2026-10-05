<?php

namespace App\Rules;

use App\Enums\EstadoCiclo;
use App\Models\Academico\CicloLectivo;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class CrearCicloLectivoRules
{
    /**
     * Valida que se pueda crear un nuevo ciclo lectivo.
     * Regla: Solo se puede crear un ciclo si TODOS los anteriores están CERRADOS.
     */
    public static function validate(array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'anio'         => ['required', 'integer', 'min:2020', 'max:2100'],
            'numero_ciclo' => ['required', 'string', 'in:01,02'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after:fecha_inicio'],
            'nombre_ciclo' => ['nullable', 'string', 'max:50'],
        ], [
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ]);

        $validator->after(function ($v) use ($data) {
            // Regla 1: No puede existir un ciclo en CONFIGURACION / MATRICULA_ABIERTA / ACTIVO
            $cicloEnCurso = CicloLectivo::whereIn('estado', [
                EstadoCiclo::CONFIGURACION->value,
                EstadoCiclo::MATRICULA_ABIERTA->value,
                EstadoCiclo::ACTIVO->value,
            ])->first();

            if ($cicloEnCurso) {
                $v->errors()->add(
                    'ciclo_en_curso',
                    "No se puede crear un nuevo ciclo porque '{$cicloEnCurso->nombre_ciclo}' sigue en estado '{$cicloEnCurso->estado->label()}'. Debe cerrarlo primero."
                );
            }

            // Regla 2: Unicidad anio + numero_ciclo
            $existe = CicloLectivo::where('anio', $data['anio'] ?? null)
                ->where('numero_ciclo', $data['numero_ciclo'] ?? null)
                ->exists();

            if ($existe) {
                $v->errors()->add(
                    'duplicado',
                    'Ya existe un ciclo registrado para ese año y número.'
                );
            }
        });

        return $validator;
    }
}