<?php

namespace App\Rules;

use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;

class AssignRolesToUserRule extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(Role::SUPER_ADMIN);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,rol_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists'   => 'El usuario seleccionado no existe.',
            'roles.array'      => 'Los roles deben enviarse como arreglo.',
            'roles.*.exists'   => 'Uno de los roles seleccionados no existe en el catálogo.',
        ];
    }

    /**
     * Validación adicional: no asignar roles inactivos.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $roleIds = $this->input('roles', []);
            if (empty($roleIds)) return;

            $inactive = Role::whereIn('rol_id', $roleIds)
                ->where('estado', false)
                ->pluck('nombre')
                ->toArray();

            if (!empty($inactive)) {
                $validator->errors()->add(
                    'roles',
                    'No se pueden asignar roles inactivos: ' . implode(', ', $inactive)
                );
            }

            $studentRole = Role::where('nombre', Role::ESTUDIANTE)->first();
            if ($studentRole && in_array($studentRole->rol_id, $roleIds)) {
                $validator->errors()->add(
                    'roles',
                    'Error de permisos: el rol ESTUDIANTE no puede ser asignado manualmente.'
                );
            }
        });

    }
}