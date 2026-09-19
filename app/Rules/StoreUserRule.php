<?php
// validaciones y reglas de almacenamiento de usuario.
namespace App\Rules;

use App\Models\Users\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserRule extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole(Role::SUPER_ADMIN);
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'celular'             => ['required', 'string', 'regex:/^[0-9]{4}-[0-9]{4}$/'],
            'fecha_nacimiento'    => ['required', 'date', 'before:today'],
            'genero'              => ['required', 'string', Rule::in(['M', 'F'])],
            'estado_civil'        => ['nullable', 'string', Rule::in(['Soltero(a)', 'Casado(a)', 'Divorciado(a)', 'Viudo(a)'])],
            'direccion'           => ['required', 'string', 'max:500'],
            'id_regional_activo'  => ['required', 'integer', 'exists:regional_activo,regional_activo_id'],
            'id_tipo_documento'   => ['required', 'integer', 'exists:tipo_documento_identidad,tipo_documento_id'],
            'documento_identidad' => ['required', 'string', 'max:30', 'unique:users,documento_identidad'],
            'estado'              => ['required', 'in:0,1'],
            'roles'               => ['required', 'array', 'min:1'],
            'roles.*'             => ['required', 'integer', 'exists:roles,rol_id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $studentRole = Role::where('nombre', Role::ESTUDIANTE)->first();
            if ($studentRole && in_array($studentRole->rol_id, $this->input('roles', []))) {
                $validator->errors()->add(
                    'roles',
                    'Inconsistencia de permisos: El rol ESTUDIANTE no puede ser asignado manualmente por el Administrador.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email'               => strtolower(trim($this->email)),
            'documento_identidad' => trim($this->documento_identidad),
            'estado'              => (int) $this->estado,
        ]);
    }
}