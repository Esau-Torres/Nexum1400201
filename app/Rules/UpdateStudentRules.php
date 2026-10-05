<?php

namespace App\Rules;

use App\Models\Users\Role;
use App\Rules\ValidarDocumentoIdentidad;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRules extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::ADMIN_ACADEMICO) ?? false;
    }

    public function rules(): array
    {
       $userId = $this->route('alumno')->id_usuario;

        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'max:255',
                                      Rule::unique('users', 'email')->ignore($userId)],
            'id_tipo_documento'   => ['required', 'exists:tipo_documento_identidad,tipo_documento_id'],
            'documento_identidad' => [
                'required', 'string', 'max:30',
                new ValidarDocumentoIdentidad($this->input('id_tipo_documento')),
                Rule::unique('users', 'documento_identidad')
                    ->where(fn ($q) => $q->where('id_tipo_documento', $this->input('id_tipo_documento')))
                    ->ignore($userId),
            ],
            'fecha_nacimiento'    => ['required', 'date', 'before:-17 years'],
            'genero'              => ['required', 'in:M,F'],
            'estado_civil'        => ['nullable', 'string', 'max:20'],
            'celular'             => ['nullable', 'string', 'max:20'],
            'direccion'           => ['nullable', 'string', 'max:255'],
            'id_regional_activo'  => ['required', 'exists:regional_activo,regional_activo_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'documento_identidad.unique' => 'Ya existe un usuario con ese número de documento.',
            'email.unique'               => 'Ya existe un usuario con ese correo.',
            'email.email'                  => 'El correo institucional no posee un formato válido.',
            'fecha_nacimiento.before'      => 'La fecha de nacimiento no puede ser una fecha futura.',
        ];
    }
}