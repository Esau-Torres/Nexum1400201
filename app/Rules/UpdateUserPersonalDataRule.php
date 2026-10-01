<?php

namespace App\Rules;
 
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserPersonalDataRule extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('SUPER_ADMIN');
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name'                => ['required', 'string', 'max:255'],
            // en produccion esta linea
            //'email'               => ['required', 'string', 'email:rfc,dns', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            // en desarrollo esta otra 
            'email'               => ['required', 'string', 'email:rfc,strict', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'documento_identidad'  => ['required', 'string', 'max:30', Rule::unique('users', 'documento_identidad')->ignore($userId)],
            'celular'             => ['nullable', 'string', 'regex:/^[0-9]{4}-[0-9]{4}$/'],
            'fecha_nacimiento'    => ['required', 'date', 'before:today'],
            'id_regional_activo'  => ['required', 'integer', 'exists:regional_activo,regional_activo_id'],
            'estado'              => ['nullable',
                                        Rule::in([0, 1]),
                                        function ($attribute, $value, $fail) use ($userId) {
                                            if (auth()->id() === $userId && (int)$value === 0) {
                                                $fail('No puede deshabilitar su propia cuenta de usuario en sesión activa.');
                                            }
                                        }
                                    ],
            'direccion'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                => 'El nombre completo es obligatorio.',
            'email.required'               => 'El correo institucional es obligatorio.',
            'email.email'                  => 'El correo institucional no posee un formato válido.',
            'email.unique'                 => 'El correo institucional ya se encuentra registrado por otro usuario.',
            'celular.regex'                => 'El formato del número celular debe ser 0000-0000.',
            'documento_identidad.required' => 'El número de documento de identidad es obligatorio.',
            'documento_identidad.unique'   => 'El documento de identidad ya pertenece a otra cuenta institucional.',
            'fecha_nacimiento.required'    => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before'      => 'La fecha de nacimiento no puede ser una fecha futura.',
            'id_regional_activo.required'  => 'Debe seleccionar una sede regional UMA.',
            'id_regional_activo.exists'    => 'La sede regional seleccionada no existe en el catálogo institucional.',
        ];
    }
}