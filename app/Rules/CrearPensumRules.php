<?php

namespace App\Rules;

use App\Enums\EstadoPensum;
use App\Models\Academico\Pensum;
use App\Models\Estudiante\Carreras;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CrearPensumRules
{
    public static function validate(array $data): ValidatorContract
    {
        $validator = Validator::make($data, [
            'id_carrera'    => ['required', 'integer', 'exists:carreras,carrera_id'],
            'nombre'        => ['required', 'string', 'min:3', 'max:100'],
            'anio_vigencia' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $validator->after(function ($v) use ($data) {
            // Unicidad: no dos pensums del mismo año para la misma carrera
            $existe = Pensum::where('id_carrera', $data['id_carrera'] ?? null)
                ->where('anio_vigencia', $data['anio_vigencia'] ?? null)
                ->exists();

            if ($existe) {
                $v->errors()->add('anio_vigencia', 'Ya existe un pensum para esa carrera y año.');
            }
        });

        return $validator;
    }
}