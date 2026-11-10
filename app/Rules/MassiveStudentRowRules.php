<?php

namespace App\Rules;

use App\Models\Users\Role;
use App\Rules\ValidarDocumentoIdentidad;
use Illuminate\Validation\Rule;

class MassiveStudentRowRules
{
    /**
     * Devuelve las reglas para UNA fila del archivo masivo.
     * $rowNumber se usa solo para mensajes.
     */
    public static function rules(array $data, int $rowNumber): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'id_tipo_documento'   => ['required', 'exists:tipo_documento_identidad,tipo_documento_id'],
            'documento_identidad' => [
                'required', 'string', 'max:30',
                new ValidarDocumentoIdentidad((int) ($data['id_tipo_documento'] ?? 0)),
                Rule::unique('users', 'documento_identidad')
                    ->where(fn ($q) => $q->where('id_tipo_documento', $data['id_tipo_documento'] ?? 0)),
            ],
            'fecha_nacimiento'    => ['required', 'date_format:Y-m-d', 'before:-17 years'],
            'genero'              => ['required', 'in:M,F'],
            'estado_civil'        => ['nullable', 'string', 'max:20'],
            'celular'             => ['nullable', 'string', 'max:20'],
            'direccion'           => ['nullable', 'string'],
            'id_regional_activo'  => ['required', 'exists:regional_activo,regional_activo_id'],
            'id_carrera'          => ['required', 'exists:carreras,carrera_id'],
        ];
    }

    public static function messages(int $rowNumber): array
    {
        $prefix = "rows.{$rowNumber}.";
        $msgs   = [];

        foreach ([
            'name.required'                => 'El nombre es obligatorio.',
            'email.required'               => 'El correo es obligatorio.',
            'email.email'                  => 'El correo no es válido.',
            'email.unique'                 => 'Este correo ya está registrado.',
            'id_tipo_documento.required'   => 'El tipo de documento es obligatorio.',
            'id_tipo_documento.exists'     => 'El tipo de documento no existe.',
            'documento_identidad.required' => 'El número de documento es obligatorio.',
            'documento_identidad.unique'   => 'Este documento ya está registrado.',
            'fecha_nacimiento.required'    => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before'      => 'Debe ser mayor de 17 años.',
            'genero.required'              => 'El género es obligatorio.',
            'genero.in'                    => 'Género inválido (M/F).',
            'id_regional_activo.required'  => 'La regional es obligatoria.',
            'id_regional_activo.exists'    => 'La regional no existe.',
            'id_carrera.required'          => 'La carrera es obligatoria.',
            'id_carrera.exists'            => 'La carrera no existe.',
        ] as $key => $msg) {
            $msgs[$prefix . $key] = "Fila {$rowNumber}: {$msg}";
        }

        return $msgs;
    }
}