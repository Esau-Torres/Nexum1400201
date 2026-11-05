<?php

namespace App\Rules\Student;

use App\Enums\TipoSituacion;
use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSituacionRule extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ESTUDIANTE) ?? false;
    }

    public function rules(): array
    {
        return [
            'nombre_usuario' => [
                'required', 'string', 'min:2', 'max:150',
                function ($attribute, $value, $fail) {
                    if (trim($value) !== $value) {
                        $fail('El nombre no puede tener espacios al inicio o final.');
                    }
                },
            ],
            'carnet_institucional' => [
                'required', 'string', 'min:4', 'max:25',
                'regex:/^[A-Za-z0-9-]+$/',
            ],
            'correo' => [
                'required', 'email:rfc', 'max:255',
            ],
            'tipo_situacion' => [
                'required', Rule::enum(TipoSituacion::class),
            ],
            'detalle' => [
                'required', 'string', 'min:10', 'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_usuario.required'    => 'El nombre es obligatorio.',
            'nombre_usuario.min'         => 'El nombre debe tener al menos 2 caracteres.',
            'carnet_institucional.regex' => 'El carnet solo puede contener letras, números y guiones.',
            'carnet_institucional.min'   => 'El carnet debe tener al menos 4 caracteres.',
            'correo.email'               => 'El correo no tiene un formato válido.',
            'tipo_situacion.required'    => 'Debes seleccionar un tipo de situación.',
            'detalle.required'           => 'Debes describir tu situación.',
            'detalle.min'                => 'El detalle debe tener al menos 10 caracteres.',
            'detalle.max'                => 'El detalle no puede exceder 2000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_usuario'       => 'nombre',
            'carnet_institucional' => 'carnet institucional',
            'correo'               => 'correo electrónico',
            'tipo_situacion'       => 'tipo de situación',
            'detalle'              => 'detalle de la situación',
        ];
    }
}