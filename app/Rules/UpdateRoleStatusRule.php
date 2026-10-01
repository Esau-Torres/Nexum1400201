<?php

namespace App\Rules;

use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class UpdateRoleStatusRule extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(Role::SUPER_ADMIN);
    }

    public function rules(): array
    {
        return [
            'role_id' => [
                'required',
                'integer',
                ValidationRule::exists('roles', 'rol_id'),
            ],
            'estado' => [
                'required',
                ValidationRule::in([0, 1, true, false, '0', '1']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'El rol es obligatorio.',
            'role_id.exists'   => 'El rol seleccionado no existe en el catálogo.',
            'estado.required'  => 'Debe indicar el nuevo estado del rol.',
            'estado.in'        => 'El estado debe ser activo (1) o inactivo (0).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'estado' => filter_var($this->estado, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);
    }
}