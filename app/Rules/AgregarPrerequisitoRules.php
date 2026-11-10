<?php

namespace App\Rules;

use App\Models\Academico\PensumMateria;
use App\Models\Academico\Prerequisito;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class AgregarPrerequisitoRules
{
    public static function validate(PensumMateria $pensumMateria, array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'id_materia_requisito'  => ['required', 'integer', 'exists:materias,materia_id'],
            'uv_minimas_requeridas' => ['nullable', 'integer', 'min:0', 'max:500'],
        ]);

        $validator->after(function ($v) use ($pensumMateria, $data) {

            $idReq = $data['id_materia_requisito'] ?? null;

            // Regla 1: Una materia no puede ser prerequisito de sí misma
            if ($idReq === $pensumMateria->id_materia) {
                $v->errors()->add('id_materia_requisito', 'Una materia no puede ser prerequisito de sí misma.');
                return;
            }

            // Regla 2: El prerequisito debe existir dentro del mismo pensum
            $existeEnPensum = PensumMateria::where('id_pensum', $pensumMateria->id_pensum)
                ->where('id_materia', $idReq)
                ->exists();

            if (!$existeEnPensum) {
                $v->errors()->add('id_materia_requisito', 'La materia debe estar asignada en el mismo pensum para poder ser prerequisito.');
                return;
            }

            // Regla 3: No duplicar prerequisito
            $duplicado = Prerequisito::where('id_pensum_materia', $pensumMateria->pensum_materia_id)
                ->where('id_materia_requisito', $idReq)
                ->exists();

            if ($duplicado) {
                $v->errors()->add('id_materia_requisito', 'Esa materia ya está registrada como prerequisito.');
            }
        });

        return $validator;
    }
}