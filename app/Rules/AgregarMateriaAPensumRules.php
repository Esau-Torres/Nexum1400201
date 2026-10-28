<?php

namespace App\Rules;

use App\Enums\EstadoPensum;
use App\Models\Academico\Pensum;
use App\Models\Academico\PensumMateria;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class AgregarMateriaAPensumRules
{
    public static function validate(Pensum $pensum, array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'id_materia'     => ['required', 'integer', 'exists:materias,materia_id'],
            'ciclo_sugerido' => ['required', 'integer', 'min:1'],
            'es_obligatoria' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($v) use ($pensum, $data) {

            // Regla 1: El pensum debe estar en MODIFICADO
            if ($pensum->estado !== EstadoPensum::MODIFICADO) {
                $v->errors()->add('estado', 'Solo se pueden modificar materias en pensums en estado MODIFICADO.');
                return;
            }

            // Regla 2: El ciclo debe estar dentro de duracion_ciclos
            $duracion = $pensum->carrera?->duracion_ciclos ?? 0;
            if ($duracion > 0 && ($data['ciclo_sugerido'] ?? 0) > $duracion) {
                $v->errors()->add('ciclo_sugerido', "El ciclo no puede ser mayor a la duración de la carrera ({$duracion}).");
            }

            // Regla 3: Máximo 5 materias por ciclo
            $cantidad = $pensum->pensumMaterias()
                ->where('ciclo_sugerido', $data['ciclo_sugerido'] ?? 0)
                ->count();

            if ($cantidad >= 5) {
                $v->errors()->add('ciclo_sugerido', 'Ese ciclo ya tiene el máximo de 5 materias.');
            }

            // Regla 4: La materia no debe estar ya en este pensum
            $existe = PensumMateria::where('id_pensum', $pensum->pensum_id)
                ->where('id_materia', $data['id_materia'] ?? null)
                ->exists();

            if ($existe) {
                $v->errors()->add('id_materia', 'Esa materia ya está asignada a este pensum.');
            }
        });

        return $validator;
    }
}