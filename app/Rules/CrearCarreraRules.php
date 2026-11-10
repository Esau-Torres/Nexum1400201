<?php

namespace App\Rules;

use App\Models\Estudiante\Carreras;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;

class CrearCarreraRules
{
    public static function validate(array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'id_facultad'         => ['required', 'integer', 'exists:facultades,facultad_id'],
            'id_nivel_academico'  => ['required', 'integer', 'exists:niveles_academicos,nivel_academico_id'],
            'nombre'              => ['required', 'string', 'min:3', 'max:150'],
            'duracion_ciclos'     => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $validator->after(function ($v) use ($data) {
            // Unicidad por facultad
            $existe = Carreras::where('id_facultad', $data['id_facultad'] ?? null)
                ->whereRaw('UPPER(nombre) = ?', [mb_strtoupper($data['nombre'] ?? '')])
                ->exists();

            if ($existe) {
                $v->errors()->add('nombre', 'Ya existe una carrera con ese nombre en esta facultad.');
            }
        });

        return $validator;
    }
}