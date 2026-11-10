<?php

namespace App\Rules;

use App\Enums\EstadoCiclo;
use App\Enums\TipoCiclo;
use App\Models\Academico\CicloLectivo;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ActualizarCicloLectivoRules
{
    public static function validate(CicloLectivo $ciclo, array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'nombre_ciclo' => ['required', 'string', 'max:50'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin'    => ['required', 'date', 'after:fecha_inicio'],
            'estado'       => ['required', Rule::enum(EstadoCiclo::class)],
            'numero_ciclo' => ['required', Rule::enum(TipoCiclo::class)],
        ], [
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ]);

        $validator->after(function ($v) use ($ciclo, $data) {
            $nuevoEstado = EstadoCiclo::tryFrom($data['estado'] ?? '');

            // -- Si el ciclo actual está CERRADO, no permitir reabrir si es terminal --
            if ($ciclo->estado === EstadoCiclo::CERRADO && $nuevoEstado !== EstadoCiclo::CERRADO) {
                if ($ciclo->fecha_fin->isPast() || $ciclo->fecha_fin->isToday()) {
                    $v->errors()->add(
                        'ciclo_cerrado_terminal',
                        'El ciclo está cerrado de forma definitiva y no puede reabrirse.'
                    );
                }
            }

            // -- Unicidad anio + numero_ciclo --
            $existe = CicloLectivo::where('anio', $ciclo->anio)
                ->where('numero_ciclo', $data['numero_ciclo'] ?? null)
                ->where('ciclo_lectivo_id', '!=', $ciclo->ciclo_lectivo_id)
                ->exists();

            if ($existe) {
                $v->errors()->add('duplicado', 'Ya existe otro ciclo con ese mismo año y número.');
            }

            // -- Validación "dos ciclos en curso" --
            if ($nuevoEstado && in_array($nuevoEstado, [EstadoCiclo::ACTIVO, EstadoCiclo::MATRICULA_ABIERTA], true)) {
                $existeOtro = CicloLectivo::where('ciclo_lectivo_id', '!=', $ciclo->ciclo_lectivo_id)
                    ->whereIn('estado', [EstadoCiclo::ACTIVO->value, EstadoCiclo::MATRICULA_ABIERTA->value])
                    ->exists();

                if ($existeOtro) {
                    $v->errors()->add('ciclo_en_curso', 'Ya existe otro ciclo en curso.');
                }
            }

            // -- Cierre anticipado --
            if ($nuevoEstado === EstadoCiclo::CERRADO && $ciclo->estado !== EstadoCiclo::CERRADO) {
                $fechaFin = \Carbon\Carbon::parse($data['fecha_fin']);
                if (!$fechaFin->isPast() && !$fechaFin->isToday()) {
                    $v->errors()->add('cierre_anticipado', 'La fecha de finalización aún no ha pasado.');
                }
            }
        });

        return $validator;
    }
}