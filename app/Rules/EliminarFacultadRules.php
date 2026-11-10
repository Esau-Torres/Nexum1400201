<?php

namespace App\Rules;

use App\Models\Estudiante\Facultades;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class EliminarFacultadRules
{
    public static function validate(Facultades $facultad): ValidatorContract
    {
        $validator = Validator::make([], []);

        $validator->after(function ($v) use ($facultad) {
            if ($facultad->tieneCarreras()) {
                $v->errors()->add(
                    'eliminar',
                    'No se puede eliminar la facultad porque tiene carreras vinculadas.'
                );
            }
        });

        return $validator;
    }
}