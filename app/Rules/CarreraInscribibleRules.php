<?php

namespace App\Rules;

use App\Models\Estudiante\Carreras;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class CarreraInscribibleRules
{
    public static function validate(Carreras $carrera): ValidatorContract
    {
        $validator = Validator::make([], []);

        $validator->after(function ($v) use ($carrera) {
            if (!$carrera->tienePensumActivo()) {
                $v->errors()->add(
                    'pensum',
                    'Esta carrera no puede ser seleccionada porque no tiene un pensum activo.'
                );
            }
        });

        return $validator;
    }
}