<?php

namespace App\Rules;

use App\Models\Estudiante\Facultades;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class CrearFacultadRules
{
    public static function validate(array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'min:3', 'max:150'],
        ]);

        $validator->after(function ($v) use ($data) {
            $existe = Facultades::whereRaw('UPPER(nombre) = ?', [mb_strtoupper($data['nombre'] ?? '')])
                ->exists();

            if ($existe) {
                $v->errors()->add('nombre', 'Ya existe una facultad con ese nombre.');
            }
        });

        return $validator;
    }
}