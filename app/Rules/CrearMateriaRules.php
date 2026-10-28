<?php

namespace App\Rules;

use App\Enums\EstadoMateria;
use App\Models\Academico\Materias;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CrearMateriaRules
{
    public static function validate(array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'codigo_materia'       => [
                'required', 'string', 'max:20',
                'regex:/^[A-Z0-9\-]{3,20}$/',
            ],
            'nombre'               => ['required', 'string', 'min:3', 'max:150'],
            'unidades_valorativas' => ['required', 'integer', 'min:1', 'max:20'],
            'estado'               => ['required', Rule::enum(EstadoMateria::class)],
        ], [
            'codigo_materia.regex' => 'El código solo admite mayúsculas, números y guiones (3-20 caracteres).',
        ]);

        $validator->after(function ($v) use ($data) {
            // -- Unicidad de código --
            $existe = Materias::whereRaw('UPPER(codigo_materia) = ?', [strtoupper($data['codigo_materia'] ?? '')])
                ->exists();

            if ($existe) {
                $v->errors()->add('codigo_materia', 'Ya existe una materia con ese código.');
            }

            // -- Unicidad de nombre (case-insensitive) --
            $existeNombre = Materias::whereRaw('UPPER(nombre) = ?', [strtoupper($data['nombre'] ?? '')])
                ->exists();

            if ($existeNombre) {
                $v->errors()->add('nombre', 'Ya existe una materia con ese nombre.');
            }
        });

        return $validator;
    }
}