<?php

namespace App\Rules;

use App\Models\Estudiante\Facultades;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class ActualizarFacultadRules
{
    public static function validate(Facultades $facultad, array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'nombre' => ['required', 'string', 'min:3', 'max:150'],
        ]);

        $validator->after(function ($v) use ($facultad, $data) {
            $existe = Facultades::where('facultad_id', '!=', $facultad->facultad_id)
                ->whereRaw('UPPER(nombre) = ?', [mb_strtoupper($data['nombre'] ?? '')])
                ->exists();

            if ($existe) {
                $v->errors()->add('nombre', 'Ya existe otra facultad con ese nombre.');
            }
        });

        return $validator;
    }
}