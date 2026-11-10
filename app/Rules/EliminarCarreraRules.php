<?php

namespace App\Rules;

use App\Models\Estudiante\Carreras;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class EliminarCarreraRules
{
    public static function validate(Carreras $carrera): ValidatorContract
    {
        $validator = Validator::make([], []);

        $validator->after(function ($v) use ($carrera) {
            if ($carrera->tienePensum()) {
                $v->errors()->add(
                    'eliminar',
                    'No se puede eliminar la carrera porque tiene pensums vinculados.'
                );
            }

            if ($carrera->alumno()->exists()) {
                $v->errors()->add(
                    'eliminar',
                    'No se puede eliminar la carrera porque tiene estudiantes asignados.'
                );
            }
        });

        return $validator;
    }
}